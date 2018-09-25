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
import http.client
import urllib.parse


class PumpProtocol (object) :

    def __init__(self, url) :
        self.logger = logging.getLogger(controller_emul.config.LOGGER_NAME)
        self.url = url
        urlParseResult = urllib.parse.urlsplit(self.url)


    def send_register_command(self):
        conn = http.client.HTTPConnection(self.url)
        headers = {"Content-type":"text/plain"}
        #conn.request("GET", )
        pass


