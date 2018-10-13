<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.09.18
 * Time: 17:14
 */

require_once("pumpprotocol.php");



class CommandResult
{

}


abstract class PumpCommandEngineBase
{
    public function processCommand($request)
    {
        return null;
    }

}
class PumpCommandEngine extends  PumpCommandEngineBase
{
    /**
     *  Return status for push..command info status
     */

    public static  $CONTROLLER_NOT_FOUND  = 1; //not found controller
    public static  $CONTROLLER_IS_OFFLINE = 2; //no active session

    private $dbdriver;
    private $controllerManager;

    public function __construct($dbdriver, $controllerManager)
    {
        $this->dbdriver = $dbdriver;
        $this->controllerManager = $controllerManager;

    }

    private function _getPengingCommand($session)
    {


    }

    /**
     * @param $controller_id
     *  Append get_controller_info_command into session command queue
     */

    public function pushGetControllerInfoCommand($controller_id)
    {
        $controller = $this->controllerManager->getController($controller_id);
        if (!is_object($controller ))
            return $this::$CONTROLLER_NOT_FOUND;
        else if (!$controller->online)
            return $this::$CONTROLLER_IS_OFFLINE;


        return $controller;


    }



    private function _checkCommandRequest($request)
    {
        $session = $this->controllerManager->getSessionByToken($request->getToken(), true);



        $response = (new BasicResponse(PumpMessageConsts::$NO_COMMAND_RESPONSE))->serialize();
        return $response;

    }

    public function processRequest($request)
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