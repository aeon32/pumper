DELETE FROM pump_controller_monitoring_info WHERE id>0;
DELETE FROM pump_controller_session WHERE id>0;
DELETE FROM pump_controller_command WHERE id>0;
DELETE FROM pump_controller_command_send_info_result WHERE command_id > 0;
DELETE FROM pump_controller_pumping_table;
DELETE FROM pump_controller_pumping_table_rows;

ALTER TABLE pump_controller_monitoring_info AUTO_INCREMENT = 1;
ALTER TABLE pump_controller_session AUTO_INCREMENT = 1;
ALTER TABLE pump_controller_command AUTO_INCREMENT = 1;
ALTER TABLE pump_controller_pumping_table AUTO_INCREMENT = 1;
