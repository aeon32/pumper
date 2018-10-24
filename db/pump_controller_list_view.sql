CREATE OR REPLACE VIEW pump_controller_list AS 

select pump_controller.id as controller_id, name, last_session_id, pump_controller.imei as imei, 
       pcs.token,((now(3) - pcs.lasttime) < (select pump_settings.session_expiration_time from pump_settings)) AS session_active, pcs.lasttime,
       pcmi.id as monitoring_info_id, pcmi.createtime as monitoring_time,
       ((now(3) - pcmi.lasttime) < (select pump_settings.session_expiration_time from pump_settings)) AS monitoring_info_actual,
       pcmi.pressure, pcmi.is_working, pcmi.current_valve, pcmi.current_step

from pump_controller
 left join pump_controller_session as pcs on pcs.id = last_session_id
 left join pump_controller_monitoring_info as pcmi on pcmi.id = last_monitoring_info.id and pcmi.session_id = pcs.id