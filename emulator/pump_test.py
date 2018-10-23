#!/usr/bin/python
# -*- coding:utf-8 -*-


import os
import sys
import signal
import getopt
import time
import logging


import struct
import controller_emul.pump_protocol
import controller_emul.queuedispatcher
import controller_emul.enum
import controller_emul.config
import controller_emul.pump_jobs
import controller_emul.device_emulator

##s

def test1(queueDispatcher, pumpProtocol,  deviceEmulator):



    checkCommandJob = controller_emul.pump_jobs.CheckCommandJob(pumpProtocol, deviceEmulator)

    queueDispatcher.push_job(checkCommandJob)

    def terminateEmptyRunner():
        if queueDispatcher.queue_size() == 0:
            queueDispatcher.terminate()

    queueDispatcher.run( terminateEmptyRunner)


    pass

       
def main(argv):
    this_script_paht = os.path.dirname(os.path.realpath(__file__))

    try:
        opts, args = getopt.getopt(argv, 'u:')
        url = None
        for opt, arg in opts:
            if opt == '-u':
                url = arg

        if not url:
            raise getopt.GetoptError("url is empty")


        logger = logging.getLogger(controller_emul.config.LOGGER_NAME)
        logger.setLevel(logging.DEBUG)


        ch = logging.StreamHandler()
        ch.setLevel(logging.DEBUG)
        formatter = logging.Formatter('PUMP_TEST:%(message)s')
        ch.setFormatter(formatter)
        logger.addHandler(ch)

        url = url + "/controlserver/"

        logger.debug("URL : %s", url)

        deviceEmulator = controller_emul.device_emulator.DeviceEmulator('abcd'.encode("latin-1"), 2)
        queueDispatcher = controller_emul.queuedispatcher.QueueDispatcher()
        pumpProtocol = controller_emul.pump_protocol.PumpProtocol(url)
        test1(queueDispatcher, pumpProtocol, deviceEmulator )



        
    except getopt.GetoptError:
        print ("Usage: pump_test -u url")
        sys.exit(2)

    finally:
        pass

if __name__ == "__main__":
    main(sys.argv[1:])

