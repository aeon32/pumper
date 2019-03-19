<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.03.19
 * Time: 12:06
 */

class PumpingTableRow
{

    public $valve_number;
    public $time_to_run;

    private static $structFMT = "Cvalve_number/Ntime_to_run";
    private static $structFMTSize = 5;

    public function __construct($valve_number, $time_to_run)
    {
        $this->valve_number = $valve_number;
        $this->time_to_run = $time_to_run;
    }

    static public function getStructFMT()
    {
        return PumpingTableRow::$structFMT;

    }

    static public function getStructFMTSize()
    {
        return PumpingTableRow::$structFMTSize;

    }

}


class PumpingTable
{

    private static $structFMT = "Cstep_count";
    private static $structFMTSize = 1;

    public $pumping_table; //array of PumpingTableRow`s

    public function __construct($pumping_table)
    {
        $this->pumping_table = $pumping_table;
    }

    public function packedSize()
    {
      return PumpingTable::$structFMTSize + count($this->pumping_table) * PumpingTableRow::getStructFMTSize();
    }

    static public function getStructFMT()
    {
        return PumpingTable::$structFMT;
    }

    static public function getStructFMTSize()
    {
        return PumpingTable::$structFMTSize;
    }

    static public function deserialize($data)
    {

        if (is_string($data) && strlen($data) >= PumpingTable::$structFMTSize) {
            $len = strlen($data);
            $offset = 0;
            $unpacked = unpack(PumpingTable::$structFMT, $data);
            $step_count = $unpacked["step_count"];
            $offset += PumpingTable::$structFMTSize;

            $pumping_table_expected_size = $step_count * PumpingTableRow::getStructFMTSize();
            if ($len >= $offset + $pumping_table_expected_size)
            {
                $pumping_table = array();
                for($i = 0; $i < $step_count; $i++)
                {
                   $unpacked_row = unpack(PumpingTableRow::getStructFMT(), $data, $offset);
                   array_push($pumping_table,  new PumpingTableRow($unpacked_row["valve_number"], $unpacked_row["time_to_run"]));
                   $offset += PumpingTableRow::getStructFMTSize();
                };
                return new PumpingTable($pumping_table);

            } else
                return null;

            return new MonitoringInfo($unpacked["pressure"], $unpacked["is_working"] != 0, $unpacked["current_value"], $unpacked["current_step"]);
        } else
            return null;

    }


}

?>