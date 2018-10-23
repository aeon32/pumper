# -*- coding:utf-8 -*-
import os
import sys
import signal
import getopt
import time
import struct
import logging
import copy
import socket
import struct
import base64
import http.client
import urllib.parse
import controller_emul.config
import controller_emul.enum


class PumpMessageBase:
    def __init__ (self, type):
        self.type = type

    def serialize(self):
        fmt = "B"
        return  struct.pack(fmt, self.type)



class PumpMessageCommandWithId (PumpMessageBase):
    def __init__ (self, type, id):
        super().__init__(type)
        self.id = id

    def serialize(self):
        fmt = "!BI"
        return struct.pack(fmt, self.type, self.id)



class ControllerInfoCommand(PumpMessageCommandWithId):
    def __init__ (self, type, id, token):
        super().__init__(type, id)
        self.token = token

    def serialize(self):
        fmt = "!BI"
        res = struct.pack(fmt, self.type, self.id) + self.token
        return res


class CheckCommandRequest(PumpMessageBase):
    def __init__(self, type, token):
        super().__init__(type)
        self.token = token

    def serialize(self):
        fmt = "B"
        res = struct.pack(fmt, self.type) + self.token
        return res
 #
 #    uint32_t    pressure;    //current pressure
 #    bool is_working;         //is controller working
 #    uint8_t currentValve;    //current valve opened
 #    uint8_t currentStep;     //current step

class CheckCommandWithInfoRequest(PumpMessageBase):
    def __init__(self, type, token, pressure, is_working, current_valve, current_step):
        super().__init__(type)
        self.token = token

        self.pressure = pressure
        self.is_working = is_working
        self.current_valve = current_valve
        self.current_step = current_step

    def serialize(self):
        fmt = "!BIBBB"
        return struct.pack(fmt, self.type, self.pressure, self.is_working, self.current_valve, self.current_step) + self.token

class PumpProtocol (object) :

    MESSAGE_TYPE = controller_emul.enum.enum(
        PUMP_COMMAND_CHECK = 0x30,
        NO_COMMAND_RESPONSE = 0x31,   #nothing to do
        GET_INFO_RESPONSE = 0x32,     #controller must return information
        SEND_INFO_REQUEST = 0x33,     #send info
        SWITCH_TO_MONITORING_MODE_RESPONSE = 0x34,
        COMMAND_CHECK_WITH_INFO_REQUEST=0x35

    )

    def __init__(self, url) :
        self.urlParsed = urllib.parse.urlsplit(url)

    def parse_response(self,response):
        length = len(response)
        offset = 0
        type = None
        if length >= offset + 1:
            type = response[offset]

        res = None
        offset += 1

        if type == self.MESSAGE_TYPE.NO_COMMAND_RESPONSE:
            res = PumpMessageBase(type)
        elif type == self.MESSAGE_TYPE.GET_INFO_RESPONSE:
            fmt = "!I"
            data = response[offset:]
            if struct.calcsize(fmt) == len(data):
                res = PumpMessageCommandWithId(type, struct.unpack(fmt, data)[0])

        elif type == self.MESSAGE_TYPE.SWITCH_TO_MONITORING_MODE_RESPONSE :
            res = PumpMessageBase(type)

        return res


    def send_command_request(self, message):


        path = self.urlParsed.path + "?" + base64.b64encode(message).decode('latin-1')

        res = None

        try:

            conn = http.client.HTTPConnection(self.urlParsed.netloc)
            conn.request("GET", path, None, {})

            response = conn.getresponse()

            if response.status != 204:
                data = response.read()
            controller_emul.LOGGER.debug("Send command %s,  return code %d  response %s", path, response.status, data)
            res = self.parse_response(data)
        finally:
            conn.close()

        return res

    def send_controller_info(self, commandId, token, info):
        command = ControllerInfoCommand(self.MESSAGE_TYPE.SEND_INFO_REQUEST, commandId, token )

        return self.send_command_request(command.serialize())



    def send_check_command_request(self, token):
        command = CheckCommandRequest(self.MESSAGE_TYPE.PUMP_COMMAND_CHECK, token)

        return self.send_command_request(command.serialize())





