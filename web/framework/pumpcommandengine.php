<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.09.18
 * Time: 17:14
 */

require_once("pumpprotocol.php");


abstract class PumpCommandEngineBase
{
    public function processCommand($request)
    {
        return null;
    }

}
class PumpCommandEngine extends  PumpCommandEngineBase
{

    private $dbdriver;

    public function __construct($dbdriver)
    {
        $this->dbdriver = $dbdriver;

    }

    public function processCommand($request)
    {
       return null;

    }



};