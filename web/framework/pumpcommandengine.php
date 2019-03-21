<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.09.18
 * Time: 17:14
 */

require_once("pumpprotocol.php");
require_once("user.php");


abstract class CommandResult
{

}


class Command
{
    public $id;
    public $type;  //one of PumpMessageConsts

    public $session_id;
    public $command_result;

    public function __construct($id,$type, $session_id) {
        $this->id = $id;
        $this->type = $type;
        $this->session_id = $session_id;
    }
}

class SendInfoRequestCommandResult extends  CommandResult
{

    public static function from_db($command, $dbdriver)
    {
        $table = $dbdriver->getPrefix()."controller_command_send_info_result";
        //$query = "SELECT "

    }

}

class PumpCommandResultLoader
{
    public function __construct($dbdriver)
    {
        $this->dbdriver = $dbdriver;
    }

    public function getCommandResult($command)
    {
        $result = NULL;
        switch ($command->type)
        {
            case PumpMessageConsts::$SEND_INFO_REQUEST:
                $result = SendInfoRequestCommandResult::from_db($command, $this->dbdriver);



        }
        return $result;


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
        $this->command_send_info_result_table = $options['prefix'].'controller_command_send_info_result';
        $this->controller_pumping_table = $options['prefix'].'controller_pumping_table';
        $this->controller_pumping_table_rows = $options['prefix'].'controller_pumping_table_rows';
        $this->options = $options;



    }

    private function _getPendingCommand($session)
    {
        //select first not-expired command
        $timeout = $this->options["command_timeout"];
        $timeout = $timeout / 2;
        if ($timeout == 0)
            $timeout = 1;

        try {
            $this->dbdriver->exec("LOCK TABLES $this->commands_table WRITE");
            $test_query = $this->dbdriver->exec("SELECT id, command_type FROM $this->commands_table 
                                              WHERE session_id= $session->id AND NOT processed  AND NOW(3) - createtime < $timeout
                                              ORDER BY id LIMIT 1");

            $res = null;
            if ($test_query->num_rows()) {
                $row = $test_query->getRow(0);
                $command_type = $row[1];
                $command_id = $row[0];
                $res = new PendingCommandResponse($command_type, $command_id);
                $this->dbdriver->exec("UPDATE $this->commands_table SET processed = TRUE WHERE id=$command_id");


            }
        } finally {
            $this->dbdriver->simpleExec("UNLOCK TABLES");
        };
        return $res;

    }

    /**
     * @param $controller_id
     * @param $command_type
     * @param $timeout
     * @return Command|int
     *  Put command to command queue. Wait for result, return command instance or one of $
     *  CONTROLLER_NOT_FOUND, CONTROLLER_IS_OFFLINE, .. constant
     */

    public function pushCommandWaitResponse($controller_id, $command_type, $timeout)
    {
        $controller = $this->controllerManager->getController($controller_id);
        if (!is_object($controller ))
            return $this::$CONTROLLER_NOT_FOUND;
        else if (!$controller->online)
            return $this::$CONTROLLER_IS_OFFLINE;

        $session_id = $controller->last_session->id;


        $query = $this->dbdriver->exec("INSERT INTO $this->commands_table(session_id, command_type, createtime) VALUES($session_id, $command_type, NOW(3))");
        $command_id = $query->insert_id();

        $command = new Command($command_id, $command_type, $session_id);

        $time = microtime(true);
        $command_result = NULL;
        do {
          usleep(1000 * 500);
          $test_query = $this->dbdriver->exec("SELECT TRUE FROM $this->commands_table WHERE id= $command_id AND result IS NOT NULL");
          if ($test_query->num_rows())
          {
              $command_result = true;
          }



        } while ( is_null($command_result) && (microtime(true) - $time)  < $timeout );

        if ($command_result)
        {

        } else
            return $this::$CONTROLLER_NOT_RESPONDS;


        return $command;


    }

    /**
     * @param $controller_id
     *  Append get_controller_info_command into session command queue
     */

    public function pushGetControllerInfoCommand($controller_id)
    {

        $command = $this->pushCommandWaitResponse($controller_id, PumpMessageConsts::$GET_INFO_RESPONSE,  $this->options["command_timeout"]);
        if (is_object($command))
        {
            $pumping_table_query =
                $this->dbdriver->exec
                (
                    "SELECT step_id, valve, time FROM $this->controller_pumping_table_rows 
                     WHERE pumping_table_id IN (
                      SELECT pumping_table_id FROM $this->command_send_info_result_table WHERE command_id= $command->id
                     )
                     ORDER BY step_id;"
                );

            $pumpingTable = array();
            for ($i = 0; $i < $pumping_table_query->num_rows(); $i++)
            {
                $row = $pumping_table_query->getRow($i);
                array_push($pumpingTable, new PumpingTableRow($row[1], $row[2]));
            };

            $pumpingTableObj = new PumpingTable($pumpingTable);
            return (object) array ("pumping_table" => $pumpingTableObj->pumping_table);



        } else
        {
            return $command;
        }
    }


    private function _saveControllerMonitoringInfo($session, $request)
    {
       //request has type SendMonitoringInfoRequest
        $this->controllerManager->saveControllerMonitoringInfo($session, $request->monitoringInfo);


    }

    private function _checkCommandRequest($request)
    {
        $session = $this->controllerManager->getSessionByToken($request->getToken(), true);
        $response = $this->_getPendingCommand($session);

        if ($request->getType() == PumpMessageConsts::$COMMAND_CHECK_WITH_BRIEF_INFO_REQUEST)
        {
            $this->_saveControllerMonitoringInfo($session, $request);

        }

        if (is_null($response))
        {
            $has_active_session = CUser::haveActiveUserSessions($this->dbdriver, $this->options);
            $response = (new BasicResponse($has_active_session ? PumpMessageConsts::$SWITCH_TO_MONITORING_MODE_RESPONSE  : PumpMessageConsts::$NO_COMMAND_RESPONSE ));
        }

        return $response->serialize();

    }

    private function _sendInfoRequest($request)
    {
        $session = $this->controllerManager->getSessionByToken($request->getToken(), true);
        $command_id = $request->getCommandId();
        try {
            $test_query = $this->dbdriver->exec("SELECT TRUE FROM $this->commands_table WHERE id = $command_id AND session_id= $session->id AND result IS NULL" );

            if ($test_query->num_rows() > 0)
            {
                $pumping_table_query = $this->dbdriver->exec("INSERT INTO $this->controller_pumping_table VALUES()");
                $pumping_table_id = $pumping_table_query->insert_id();
                $index = 0;
                foreach ($request->pumpingTable->pumping_table as $pumping_table_row )
                {
                    $pumping_table_rows_query = $this->dbdriver->exec(
                        "INSERT INTO $this->controller_pumping_table_rows(pumping_table_id, step_id, valve, time)
                         VALUES ($pumping_table_id, $index, $pumping_table_row->valve_number, $pumping_table_row->time_to_run )"
                    );
                    $index++;

                };
                $this->dbdriver->exec("INSERT INTO $this->command_send_info_result_table (command_id, pumping_table_id)
                    VALUES($command_id, $pumping_table_id)");
                $this->dbdriver->exec("UPDATE $this->commands_table SET result=1 
                                              WHERE id = $command_id AND session_id=$session->id ");

            };

        } finally {

        };

    }

    public function processRequest($request)
    {
        switch($request->getType())
        {
            case PumpMessageConsts::$COMMAND_CHECK_REQUEST:
                return $this->_checkCommandRequest($request);
                break;
            case PumpMessageConsts::$COMMAND_CHECK_WITH_BRIEF_INFO_REQUEST:
                return $this->_checkCommandRequest($request);
                break;

            case PumpMessageConsts::$SEND_INFO_REQUEST:
                $this->_sendInfoRequest($request);
                return $this->_checkCommandRequest($request);
                break;
        };

        return null;

    }



};