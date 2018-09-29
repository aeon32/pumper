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
import http.client
import urllib.parse
import controller_emul.config
import controller_emul.enum

class PumpProtocol (object) :

    MESSAGE_TYPE = controller_emul.enum.enum(
        PUMP_COMMAND_CHECK = 0x30
    )

    def __init__(self, url) :
        self.urlParsed = urllib.parse.urlsplit(url)

    def _parse_response(self,response):

        pass


    def send_command_request(self, msgType, token, data):

        message = bytearray()
        message += struct.pack('B',msgType)

        if len(token):
            message += struct.pack("!B", len(token))
            message += token


        if len(data):
            message += struct.pack("!Hs", len(data))
            message += data

        path = self.urlParsed.path + "?" + urllib.parse.quote_from_bytes(message)

        res = None

        try:

            conn = http.client.HTTPConnection(self.urlParsed.netloc)
            conn.request("GET", path, None, {})

            response = conn.getresponse()

            if response.status != 204:
                res = response.read()
            controller_emul.LOGGER.debug("Send command %s, type %d, return code %d  response %s", path, msgType, response.status, res)
        finally:
            conn.close()

        return res




    def send_check_command_request(self, token):
        return self.send_command_request(self.MESSAGE_TYPE.PUMP_COMMAND_CHECK, token, bytes())





