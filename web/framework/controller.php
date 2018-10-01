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
    private $sessions;


    public function __construct($dbdriver, $options)
    {
        $this->database = $dbdriver;

        $this->controller_table = $options['prefix'] . 'controller';
        $this->controller_list_view = $options['prefix'] . 'controller_list';
        $this->controller_session_table = $options['prefix'] . 'controller_session';
        $this->controllers = array();
        $this->sessions = array();
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
     * Функция возвращает информацию о контроллере по идентификатору
     */
    public function getInfo($id)
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
            $driver->simpleExec("LOCK TABLES $this->controller_table READ, $this->controller_session_table READ");
            $query = $driver->exec("SELECT name 
     								FROM $this->controller_table
     								WHERE id=$id");
            /*
            select pcs.id AS id,pcs.token, (NOW(3) - last_sessions.lasttime ) < (SELECT session_expiration_time FROM pump_settings) AS section_active
      from
		  pump_controller_session pcs
		  join
          (select pump_controller_session.controller_id AS controller_id,max(pump_controller_session.lasttime) AS lasttime
		    from pump_controller_session  where controller_id = 1 group by pump_controller_session.controller_id

		   ) as last_sessions
		  on pcs.controller_id = last_sessions.controller_id and pcs.lasttime = last_sessions.lasttime;
            */

            $driver->simpleExec("UNLOCK TABLES");
            if ($query->num_rows()) {
                $row = $query->getRow(0);
                $controller->name = $row[0];
            } else {
                unset ($this->controllers[$id]);
                return NULL;
            };

        };
        return $controller;
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

        $session = new ControllerSession($insert_id, $token, $controller);
        $controller->session = $session;
        return $controller;

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


        $driver->simpleExec("LOCK TABLES $this->controller_table WRITE");
        $query = $driver->exec("UPDATE $this->controller_table SET name='$name' WHERE controller_id=$controller->id");

        if ($return_saved)
            $new_controller = $this->getInfo($controller->id);
        $driver->simpleExec("UNLOCK TABLES");
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
				SELECT controller_id, name, imei, last_session_id, token,session_active, lasttime 
				FROM $controller_list
			");


            for ($i = 0; $i < $query->num_rows(); $i++) {
                $row = $query->getRow($i);
                $controller = null;
                if (!array_key_exists($row[0], $this->controllers))
                {
                    $controller = new Controller($row[0], $row[1], $row[2]);
                    $this->controllers[$row[0]] = $controller;
                } else {
                    $controller = $this->controllers[ $row[0]];

                }

                if (!is_null($row[3]))
                {
                    $controller->last_session = new ControllerSession($row[3], $row[4], $controller);
                    $this->last_sessions[ $row[4] ] = $controller->session;
                    if ($row[5])
                        $controller->online = true;

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
        if (array_key_exists( $token, $this->sessions))
        {
            $session = $this->sessions[$token];
            $this->updateSession($session);

        }  else
        {
            $this->database->simpleExec("LOCK TABLES $this->controller_table WRITE, $this->controller_session_table WRITE");
            //token===imei (?)
            //try to find controller
            $findedController = null;
            foreach ($this->controllers as &$controller)
            {
                if ($controller->imei === $token )
                {
                    $findedController = $controller;
                    break;
                }
            }
            if (!$findedController)
                $findedController = $this->createController($token);

            $session = $this->createSession($findedController, $token );

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