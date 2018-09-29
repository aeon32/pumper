<?php
require_once ("controllersession.php");

class Controller
{
    public $id = NULL;
    public $name = NULL;
    public $imei;
    public $description = '';
    public $session = NULL;
    public $info_is_full;


    public function __construct($id, $name, $imei)
    {
        $this->id = $id;
        $this->name = $name;
        $this->imei = $imei;

        $this->info_is_full = false;
    }


}


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
    private $site = NULL;
    private $controllers_table;

    private $controllers;
    private $list_loaded = false;


    public function __construct(CSite $site, $options)
    {
        $this->site = $site;
        $this->database = $site->getDBDriver();


        $this->controller_table = $options['prefix'] . 'controller';
        $this->controller_list_view = $options['prefix'] . 'controller_list';
        $this->controller_session_table = $options['prefix'] . 'controller_session';
        $this->controllers = array();
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
            $controller = new Controller($id, '');
            $need_load = true;
            $this->controllers[$id] = $controller;
        };

        if ($need_load) {
            $driver = $this->site->getDBDriver();
            $driver->simpleExec("LOCK TABLES $this->controller_table READ");
            $query = $driver->exec("SELECT name 
     								FROM $this->controller_table
     								WHERE controller_id=$id");
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


    /**
     *
     * Функция сохраняет информацию о схеме в БД
     * @param Scheme $scheme
     */

    public function save(Controller $controller, $return_saved)
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
            $driver = $this->site->getDBDriver();
            $controller_table = $this->controller_table;
            $controller_list = $this->controller_list_view;


            $query = $driver->exec("
				SELECT controller_id, name, imei, session_id, token 
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
                    $controller->session = new ControllerSession($row[3], $row[4]);
                };



            }
            $this->list_loaded = true;
        }
        return $this->controllers;
    }


    /**
     * Функция возвращает список схем, сортированный по именам
     */
    public function getNotTemporaryControllersListOrdered()
    {
        $this->getControllersList();
        //$notTemporaryControllers = array_filter($this->controllers, "getNotTemporary");
        $notTemporaryControllers = $this->controllers;
        return $notTemporaryControllers;
    }

}

;