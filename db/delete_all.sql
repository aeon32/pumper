DELETE FROM pump_controller_monitoring_info WHERE id>0;
DELETE FROM pump_controller_session WHERE id>0;
DELETE FROM pump_controller_command WHERE id>0;

ALTER TABLE pump_controller_monitoring_info AUTO_INCREMENT = 1;
ALTER TABLE pump_controller_session AUTO_INCREMENT = 1;
ALTER TABLE pump_controller_command AUTO_INCREMENT = 1;
