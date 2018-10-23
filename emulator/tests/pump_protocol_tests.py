import unittest
import random
import os

import controller_emul.pump_protocol
import testssetup


URL = "http://localhost/pump/controlserver/"

def setUpModule():
    testssetup.globalsetup()


class PumpProtocolTests(unittest.TestCase):
    def setUp(self):
        pass

    def test_sendCommandTest1(self):
        global URL
        url = URL + "test/echo/";

        token = "abcd".encode("latin-1");

        controller = controller_emul.pump_protocol.PumpProtocol(url)
        res = controller.send_check_command_request(token)

        self.assertEqual(token,res)

    def test_sendCommandTest2(self):
        global URL

        controller = controller_emul.pump_protocol.PumpProtocol(URL)
        token = "abcd".encode("latin-1");
        controller.send_check_command_request(token)

        self.assertTrue(True)




