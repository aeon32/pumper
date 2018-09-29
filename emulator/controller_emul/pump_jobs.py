# -*- coding:utf-8 -*-
import controller_emul.pump_protocol
import controller_emul.queuedispatcher
import time
import logging


class RegisterControllerJob(controller_emul.queuedispatcher.Job):

    TRIES = 5

    def __init__(self, pumpProtocol):
        controller_emul.queuedispatcher.Job.__init__(self)
        self.pumpProtocol = pumpProtocol
        self.setNextTryStamp(time.time())
        self.logger = logging.getLogger(controller_emul.config.LOGGER_NAME)
        self.tryCount = 0

    def process(self):
        if self.job_state == self.JOB_STATE.JOB_ADDED:
            self.job_state = self.JOB_STATE.JOB_PROCESS

        if self.job_state == self.JOB_STATE.JOB_PROCESS:
            self.pumpProtocol.send_register_command()
            self.tryCount+=1
            if self.tryCount == self.TRIES:
                self.job_state = self.JOB_STATE.JOB_TO_DELETE
        self.setNextTryStamp(time.time() + 1)
    