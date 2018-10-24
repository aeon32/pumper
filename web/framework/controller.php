<?php
require_once ("controllersession.php");

class Controller
{
    public $id = NULL;
    public $name = NULL;
    public $imei;
    public $description = '';
    public $last_session = NULL;
    public $online = FALSE;
    public $info_is_full;


    public function __construct($id, $name, $imei)
    {
        $this->id = $id;
        $this->name = $name;
        $this->imei = $imei;

        $this->info_is_full = false;
    }
}

class ControllerLite
{
    public $id;
    public $name;
    public $imei;
    public $last_session;
    public $online;
};


function sortByNameFunction(Scheme $a, Scheme $b)
{
    $i = strcmp($a->name, $b->name);
    return $i > 0;
}

/**
 *
 * Менеджер контроллерова..
 * @author user
 *
 */
class ControllersManager
{
    private $database = NULL;
    private $controller_table;

    private $controllers;
    private $list_loaded = false;
    private $last_sessions;


    public function __construct($dbdriver, $options)
    {
        $this->database = $dbdriver;

        $this->controller_table = $options['prefix'] . 'controller';
        $this->controller_list_view = $options['prefix'] . 'controller_list';
        $this->controller_session_table = $options['prefix'] . 'controller_session';
        $this->controller_monitoring_info_table = $options['prefix'] . 'controller_monitoring_info';
        $this->controllers = array();
        $this->last_sessions = array();
    }


    /**
     *
     * Функция возвращает число удалённых контроллерова
     * @param unknown_type $id
     */

    public function deleteController($id)
    {
        return $this->_delete_controller("controller_id=$id");
    }

    /**
     *
     * Функция удаления схемы
     * @param unknown_type $condition
     */
    public function _delete_controller($condition)
    {
        $driver = $this->site->getDBDriver();
        $driver->simpleExec("LOCK TABLES $this->controller_table WRITE");
        $list_query = $driver->exec("SELECT controller_id FROM $this->controller_table WHERE $condition ");
        $driver->simpleExec("DELETE FROM $this->controller_table WHERE  $condition");
        $list = '';
        $len_count = 0;

        //
        $driver->simpleExec("UNLOCK TABLES");
        for ($i = 0; $i < $list_query->num_rows(); $i++) {
            $row = $list_query->getRow();
            $controller_id = $row[0];

            if (array_key_exists($controller_id, $this->controllers))
                unset ($this->controllers[$row[0]]);
        };
        return $list_query->num_rows();


    }


    /**
     * Функция возвращает информацию о контроллере
     */
    public function getController($id)
    {
        //пытаемся найти в словаре:
        $need_load = false;
        $controller = NULL;
        if (array_key_exists($id, $this->controllers)) {
            $controller = $this->controllers[$id];
            $need_load = !$controller->info_is_full;
        } else {
            $controller = new Controller($id, '',null);
            $need_load = true;
            $this->controllers[$id] = $controller;
        };

        if ($need_load) {
            $driver = $this->database;
            $query = $driver->exec(
                 "SELECT name, pcs.id, pcs.token, pcs.lasttime,TIMESTAMPDIFF(MICROSECOND, pcs.lasttime, NOW(3) )/1000000  < (SELECT session_expiration_time FROM pump_settings) AS section_active   
                  FROM $this->controller_table
	              LEFT JOIN $this->controller_session_table as pcs ON pcs.id = pump_controller.last_session_id
                  WHERE pump_controller.id = $id");
            if ($query->num_rows()) {
                $row = $query->getRow(0);
                $controller->name = $row[0];

                if (!is_null($row[1]))
                {
                    $online = false;
                    if ($row[4])
                        $online = true;

                    $controller->last_session = new ControllerSession($row[1], $row[2], $controller, $online);
                    $this->last_sessions[ $row[2] ] = $controller->last_session;
                    $controller->online = $online;

                };
            } else {
                unset ($this->controllers[$id]);
                return NULL;
            };

        };
        return $controller;
    }

    public function saveControllerMonitoringInfo($session, $monitoringInfo)
    {
        $driver=$this->database;

        try {
            $driver->exec("LOCK TABLES $this->controller_table WRITE, $this->controller_monitoring_info_table WRITE");


            $query = $driver->exec("INSERT INTO $this->controller_monitoring_info_table 
                               (session_id, createtime, pressure, is_working, current_valve, current_step )
                                VALUES($session->id, NOW(3), $monitoringInfo->pressure, $monitoringInfo->is_working,
                                       $monitoringInfo->current_valve, $monitoringInfo->current_step 
                                      )"
            );
            $insert_id = $query->insert_id();
            $controller_id = $session->controller->id;
            $query = $driver->exec("UPDATE $this->controller_table SET last_monitoring_info_id = $insert_id WHERE id=$controller_id");
        } finally
        {
            $driver->simpleExec("UNLOCK TABLES");

        }




    }

    public function createController($imei) {
        $driver=$this->database;
        $escapedImei = $driver->escapeString($imei);

        $query = $driver->exec("INSERT INTO $this->controller_table (name,createtime, imei) VALUES('$escapedImei',NOW(3),'$escapedImei')");
        $insert_id = $query->insert_id();

        //все схемы сь
        //$this->deleteTemporaries();

        $controller = new Controller($insert_id,$imei, $imei);
        $this->controllers[$insert_id] = $controller;
        return $controller;

    }

    public function createSession($controller, $token) {
        $driver=$this->database;
        $escapedToken = $driver->escapeString($token);

        $query = $driver->exec("INSERT INTO $this->controller_session_table (controller_id, token, createtime, lasttime) 
                                       VALUES($controller->id,'$escapedToken', NOW(3), NOW(3))"
                               );
        $insert_id = $query->insert_id();

        $query = $driver->exec("UPDATE $this->controller_table SET last_session_id = $insert_id WHERE id=$controller->id");

        $session = new ControllerSession($insert_id, $token, $controller, true);
        $controller->session = $session;
        return $session;

    }

    public function updateSession($session) {
        $driver=$this->database;

        $query = $driver->exec("UPDATE $this->controller_session_table SET lasttime = NOW(3) WHERE id=$session->id");


    }


    /**
     *
     * Функция сохраняет информацию о контроллере
     * @param Scheme $scheme
     */

    public function saveController(Controller $controller, $return_saved)
    {
        $driver = $this->site->getDBDriver();
        $name = $driver->escapeString($controller->name);
        try
        {

            $driver->exec("LOCK TABLES $this->controller_table WRITE");
            $query = $driver->exec("UPDATE $this->controller_table SET name='$name' WHERE controller_id=$controller->id");

            if ($return_saved)
                $new_controller = $this->getInfo($controller->id);
        } finally {
            $driver->simpleExec("UNLOCK TABLES");
        };
        if ($return_saved)
            return $new_controller;
    }




    /**
     * Функция возвращает список контроллеров
     */
    public function getControllersList()
    {
        if (!$this->list_loaded) {
            $driver = $this->database;
            $controller_table = $this->controller_table;
            $controller_list = $this->controller_list_view;


            $query = $driver->exec("
				SELECT controller_id, name, imei, last_session_id, token,session_active, lasttime,
				       monitoring_info_id, monitoring_time, monitoring_info_actual, 
				       pressure, is_working, current_valve, current_step
				FROM $controller_list
			");


            for ($i = 0; $i < $query->num_rows(); $i++) {
                //$row = $query->getRow($i);
                $row = $query->getRowAssoc($i);
                $controller = null;
                if (!array_key_exists($row["controller_id"], $this->controllers))
                {
                    $controller = new Controller($row["controller_id"], $row["name"], $row["imei"]);
                    $this->controllers[$row["controller_id"]] = $controller;
                } else {
                    $controller = $this->controllers[ $row["controller_id"]];

                }

                if (!is_null($row["last_session_id"]))
                {
                    $online = false;
                    if ($row["session_active"])
                        $online = true;

                    $controller->last_session = new ControllerSession($row["last_session_id"], $row["token"], $controller, $online);
                    $this->last_sessions[ $row["token"] ] = $controller->last_session;
                    $controller->online = $online;
                };

            }
            $this->list_loaded = true;
        }
        return $this->controllers;
    }

    public function getSessionByToken($token, $updateTime)
    {
        $this->getControllersList(); //load data
        $session = null;
        try {
            $this->database->simpleExec("LOCK TABLES $this->controller_table WRITE, $this->controller_session_table WRITE");
            if (array_key_exists($token, $this->last_sessions) && $this->last_sessions[$token]->active) {
                $session = $this->last_sessions[$token];
                $this->updateSession($session);

            } else {
                //token===imei (?)
                //try to find controller
                $findedController = null;
                foreach ($this->controllers as &$controller) {
                    if ($controller->imei === $token) {
                        $findedController = $controller;
                        break;
                    }
                }
                if (!$findedController)
                    $findedController = $this->createController($token);

                $session = $this->createSession($findedController, $token);
            };
        } finally
        {
            $this->database->simpleExec("UNLOCK TABLES");
        };

        return $session;

    }

    /**
     * @param $controller
     * @return bool
     */
    static private  function plainArrayFilter($controller)
    {
        return true;
    }

    /**
     * Функция возвращает список контроллеров в виде простого массива
     */
    public function getControllersAsPlainArray()
    {


        $res = array();
        $this->getControllersList();
        foreach ($this->controllers as &$controller)
        {
            $controllerLite = new ControllerLite();
            $controllerLite->id = $controller->id;
            $controllerLite->name = $controller->name;
            $controllerLite->imei = $controller->imei;
            $controllerLite->last_session = is_object($controller->last_session ) ? $controller->last_session->id : null;
            $controllerLite->online = $controller->online;
            $res[] = $controllerLite;

        };

        $plainControllersArray = array_values($res);

        return $res;
    }

};