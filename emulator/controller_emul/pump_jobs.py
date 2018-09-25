# -*- coding:utf-8 -*-
import controller_emul.pump_protocol
import controller_emul.queuedispatcher
import time
import logging
import enum

class RegisterContollerJob(controller_emul.queuedispatcher.Job):
    def __init__(self, pumpProtocol):
        queuedispatcher.Job.__init__(self)
        self.pumpProtocol = pumpProtocol
        self.setNextTryStamp(time.time())
        self.logger = logging.getLogger(controller_emul.config.LOGGER_NAME)

    def process(self):
        if self.job_state == self.JOB_STATE.JOB_ADDED:
            self.job_state = self.JOB_STATE.JOB_PROCESS
        elif self.job_state == self.JOB_STATE.JOB_PROCESS:
            self.job_state = self.JOB_STATE.JOB_TO_DELETE
        self.setNextTryStamp(time.time() + 1)
    