import logging
import controller_emul.config

def globalsetup():
    logger = logging.getLogger(controller_emul.config.LOGGER_NAME)
    logger.setLevel(logging.DEBUG)

    ch = logging.StreamHandler()
    ch.setLevel(logging.DEBUG)
    formatter = logging.Formatter('PUMP_TEST:%(message)s')
    ch.setFormatter(formatter)
    logger.addHandler(ch)
