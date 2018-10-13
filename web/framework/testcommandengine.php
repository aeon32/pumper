<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.09.18
 * Time: 11:40
 */

require_once("pumpprotocol.php");

class TestCommandEngine extends  PumpCommandEngineBase
{

    private $dbdriver;
    private $testmode;

    public function __construct($dbdriver, $testmode)
    {
        $this->dbdriver = $dbdriver;
        $this->testmode = $testmode;

    }

    public function processCommandEcho($request)
    {
        switch($request->getType())
        {
            case PumpMessageConsts::$COMMAND_CHECK_REQUEST:
                return $request->getToken();
                break;
        };

        return null;

    }

    public function processRequest($request)
    {

        switch ($this->testmode)
        {
            case "echo":
                return $this->processCommandEcho($request);

            break;

            default:
                return null;

        }

        return $res;
    }



};