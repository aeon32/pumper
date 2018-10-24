<?php

/**   Controller monitoring info
 *    uint32_t    pressure;    //current pressure
 *    bool is_working;         //is controller working
 *    uint8_t currentValve;    //current valve opened
 *    uint8_t currentStep;     //current step
 *
 */


class MonitoringInfo
{
    public $pressure;
    public $is_working;
    public $current_valve;
    public $current_step;

    private static $structFMT = "Npressure/Cis_working/Ccurrent_value/Ccurrent_step";
    private static $structFMTSize = 7;

    public function __construct($pressure, $is_working, $current_valve, $current_step)
    {
        $this->pressure = $pressure;
        $this->is_working = $is_working;
        $this->current_valve = $current_valve;
        $this->current_step = $current_step;
    }

    static public function getStructFMT()
    {
        return MonitoringInfo::$structFMT;
    }

    static public function getStructFMTSize()
    {
        return MonitoringInfo::$structFMTSize;
    }

    static public function deserialize($data)
    {
        if (is_string($data) && strlen($data) == MonitoringInfo::$structFMTSize) {
            $unpacked = unpack(MonitoringInfo::$structFMT, $data);

            return new MonitoringInfo($unpacked["pressure"], $unpacked["is_working"] != 0, $unpacked["current_value"], $unpacked["current_step"]);
        } else
            return null;

    }


}

?>