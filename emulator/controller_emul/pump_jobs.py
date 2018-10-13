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


    def process(self):
        if self.job_state == self.JOB_STATE.JOB_ADDED:
            self.job_state = self.JOB_STATE.JOB_PROCESS

        if self.job_state == self.JOB_STATE.JOB_PROCESS:
            command = self.pumpProtocol.send_check_command_request(self.token)


        self.setNextTryStamp(time.time() + self.period)


    def handle_command(self, command):

        pass
    