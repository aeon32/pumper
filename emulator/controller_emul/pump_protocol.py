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

class PumpProtocol (object) :

    MESSAGE_TYPE = controller_emul.enum.enum(
        PUMP_COMMAND_CHECK = 0x30
    )

    def __init__(self, url) :
        self.urlParsed = urllib.parse.urlsplit(url)

    def _parse_response(self,response):

        pass


    def send_command_request(self, message):


        path = self.urlParsed.path + "?" + base64.b64encode(message).decode('latin-1')

        res = None

        try:

            conn = http.client.HTTPConnection(self.urlParsed.netloc)
            conn.request("GET", path, None, {})

            response = conn.getresponse()

            if response.status != 204:
                res = response.read()
            controller_emul.LOGGER.debug("Send command %s,  return code %d  response %s", path, response.status, res)
        finally:
            conn.close()

        return res




    def send_check_command_request(self, token):
        message = bytearray()
        message += struct.pack('B', self.MESSAGE_TYPE.PUMP_COMMAND_CHECK)

        if len(token):
            message += token

        return self.send_command_request(message)





