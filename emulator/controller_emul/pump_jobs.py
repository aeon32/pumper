# -*- coding:utf-8 -*-
import controller_emul.pump_protocol
import controller_emul.queuedispatcher
import time
import logging


class CheckCommandJob(controller_emul.queuedispatcher.Job):



    def __init__(self, pumpProtocol, period, token):
        controller_emul.queuedispatcher.Job.__init__(self)
        self.pumpProtocol = pumpProtocol
        self.setNextTryStamp(time.time())
        self.logger = logging.getLogger(controller_emul.config.LOGGER_NAME)
        self.period = period
        self.token = token

        self.needSendInfo = False
        self.lastCommandId = None


    def process(self):
        timeInterval = self.period

        if self.job_state == self.JOB_STATE.JOB_ADDED:
            self.job_state = self.JOB_STATE.JOB_PROCESS

        if self.job_state == self.JOB_STATE.JOB_PROCESS:
            if self.needSendInfo:
                self.needSendInfo = False
                self.pumpProtocol.send_controller_info(self.lastCommandId, self.token, None)
            else:
                command = self.pumpProtocol.send_check_command_request(self.token)
                if command:
                    self.handle_command(command)
                    if self.needSendInfo:
                        timeInterval = self.period / 2  #Answer emulation is faster


        self.setNextTryStamp(time.time() + self.period)


    def handle_command(self, command):
        if command.type == controller_emul.pump_protocol.PumpProtocol.MESSAGE_TYPE.GET_INFO_RESPONSE:
            self.logger.debug("Get info command")
            self.lastCommandId = command.id
            self.needSendInfo = True


        pass
    