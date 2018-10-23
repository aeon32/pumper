# -*- coding:utf-8 -*-
import controller_emul.pump_protocol
import controller_emul.queuedispatcher
import time
import logging


class CheckCommandJob(controller_emul.queuedispatcher.Job):



    def __init__(self, pumpProtocol, deviceEmulator):
        controller_emul.queuedispatcher.Job.__init__(self)
        self.pumpProtocol = pumpProtocol
        self.setNextTryStamp(time.time())
        self.logger = logging.getLogger(controller_emul.config.LOGGER_NAME)
        self.token = deviceEmulator.token

        self.needSendInfo = False
        self.needSendMonitoring = False
        self.lastCommandId = None
        self.deviceEmulator = deviceEmulator


    def process(self):
        timeInterval = self.deviceEmulator.basePeriod
        currentTime = time.time()

        if self.job_state == self.JOB_STATE.JOB_ADDED:
            self.job_state = self.JOB_STATE.JOB_PROCESS

        if self.job_state == self.JOB_STATE.JOB_PROCESS:
            command = self.deviceEmulator.get_command(currentTime)
            response = None


            if command:
                response = self.pumpProtocol.send_command_request(command.serialize())

            if response:
                self.deviceEmulator.handle_command(response)

            timeInterval = self.deviceEmulator.get_interval()


        self.setNextTryStamp(time.time() + timeInterval)


