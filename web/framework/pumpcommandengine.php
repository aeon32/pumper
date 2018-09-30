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
    private $controllerManager;

    public function __construct($dbdriver, $controllerManager)
    {
        $this->dbdriver = $dbdriver;
        $this->controllerManager = $controllerManager;

    }

    public function _checkCommandRequest($request)
    {
        $session = $this->controllerManager->getSessionByToken($request->getToken(), true);
        return $request->getToken();

    }

    public function processCommand($request)
    {
        switch($request->getType())
        {
            case PumpMessageConsts::$COMMAND_CHECK_REQUEST:
                return $this->_checkCommandRequest($request);
                break;
        };

        return null;

    }



};