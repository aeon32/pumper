# -*- coding:utf-8 -*-
import time
import logging
import controller_emul.config
import controller_emul.enum
import controller_emul.pump_protocol


class DeviceEmulator:
    def __init__ (self, token, base_period):
        self.token = token
        self.fullMonitoring = False
        self.lastCommandId = None
        self.needSendInfo = False
        self.basePeriod = base_period
        self.logger = logging.getLogger(controller_emul.config.LOGGER_NAME)

        self.pressure = 10
        self.is_working = True
        self.currentValve = 20
        self.currentStep = 30

    # handle parsed command, switch device states
    def handle_command(self, command):

        currentTime = time.time()
        if command.type == controller_emul.pump_protocol.PumpProtocol.MESSAGE_TYPE.GET_INFO_RESPONSE:
            self.logger.debug("Get info command")
            self.lastCommandId = command.id
            self.needSendInfo = True
        if command.type == controller_emul.pump_protocol.PumpProtocol.MESSAGE_TYPE.SWITCH_TO_MONITORING_MODE_RESPONSE:
            if not self.fullMonitoring:
                self.logger.debug("Switch to send full monitoring ")
                self.fullMonitoring = True

    def get_interval(self):
        time_interval = self.basePeriod
        if self.needSendInfo:
            time_interval = self.basePeriod / 4.0
        elif self.fullMonitoring:
            time_interval = self.basePeriod / 2.0
        return time_interval


    def get_command(self, time):
        command = None

        if self.needSendInfo:
            command = controller_emul.pump_protocol.ControllerInfoCommand(
                controller_emul.pump_protocol.PumpProtocol.MESSAGE_TYPE.SEND_INFO_REQUEST, self.lastCommandId, self.token
            )
            self.needSendInfo = False
        elif self.fullMonitoring:
            command = controller_emul.pump_protocol.CheckCommandWithInfoRequest(
                controller_emul.pump_protocol.PumpProtocol.MESSAGE_TYPE.COMMAND_CHECK_WITH_INFO_REQUEST, self.token,
                self.pressure, self.is_working, self.currentValve, self.currentStep

            )
        else:
            command = controller_emul.pump_protocol.CheckCommandRequest(
                controller_emul.pump_protocol.PumpProtocol.MESSAGE_TYPE.PUMP_COMMAND_CHECK, self.token

            )
        return command







