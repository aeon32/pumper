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

class Command
{
    public $id;
    public $type;  //one of PumpMessageConsts

    public $session_id;

    public function __construct($id,$type, $session_id) {
        $this->id = $id;
        $this->type = $type;
        $this->session_id = $session_id;

    }


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
    public static  $CONTROLLER_NOT_RESPONDS = 3; //no active session

    private $dbdriver;
    private $controllerManager;

    public function __construct($dbdriver, $controllerManager, $options)
    {
        $this->dbdriver = $dbdriver;
        $this->controllerManager = $controllerManager;
        $this->commands_table = $options['prefix'] . 'controller_command';
        $this->options = $options;

    }

    private function _getPendingCommand($session)
    {
        //select first not-expired command
        $timeout = $this->options["command_timeout"];
        $timeout = $timeout / 2;
        if ($timeout == 0)
            $timeout = 1;

        $this->dbdriver->simpleExec("LOCK TABLES $this->commands_table WRITE");
        $test_query = $this->dbdriver->exec("SELECT id, command_type FROM $this->commands_table 
                                              WHERE session_id= $session->id AND NOT processed  AND NOW(3) - createtime < $timeout
                                              ORDER BY id LIMIT 1");

        $res = null;
        if ($test_query->num_rows())
        {
            $row = $test_query->getRow(0);
            $command_type = $row[1];
            $command_id = $row[0];
            $res = new PendingCommandResponse($command_type, $command_id);
            $this->dbdriver->exec("UPDATE $this->commands_table SET processed = TRUE WHERE id=$command_id");


        }
        $this->dbdriver->simpleExec("UNLOCK TABLES");
        return $res;

    }

    public function pushCommandWaitResponse($controller_id, $command_type, $timeout)
    {
        $controller = $this->controllerManager->getController($controller_id);
        if (!is_object($controller ))
            return $this::$CONTROLLER_NOT_FOUND;
        else if (!$controller->online)
            return $this::$CONTROLLER_IS_OFFLINE;

        $session_id = $controller->last_session->id;


        $query = $this->dbdriver->exec("INSERT INTO $this->commands_table(session_id, command_type, createtime) VALUES($session_id, $command_type, NOW(3))");
        $insert_id = $query->insert_id();

        $time = microtime(true);
        $command_result = NULL;
        do {
          usleep(1000 * 500);
          $test_query = $this->dbdriver->exec("SELECT TRUE FROM $this->commands_table WHERE session_id= $session_id AND result IS NOT NULL");
          if ($test_query->num_rows())
          {
              $command_result = true;
          }



        } while ( is_null($command_result) && (microtime(true) - $time)  < $timeout );

        if ($command_result)
        {

        } else
            return $this::$CONTROLLER_NOT_RESPONDS;


        return $controller;


    }

    /**
     * @param $controller_id
     *  Append get_controller_info_command into session command queue
     */

    public function pushGetControllerInfoCommand($controller_id)
    {

        return $this->pushCommandWaitResponse($controller_id, PumpMessageConsts::$GET_INFO_RESPONSE,  $this->options["command_timeout"]);
    }



    private function _checkCommandRequest($request)
    {
        $session = $this->controllerManager->getSessionByToken($request->getToken(), true);
        $response = $this->_getPendingCommand($session);

        if (is_null($response))
            $response = (new BasicResponse(PumpMessageConsts::$NO_COMMAND_RESPONSE));

        return $response->serialize();

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