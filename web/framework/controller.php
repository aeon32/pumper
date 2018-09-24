<?php


class Controller {
	public $id = NULL;
	public $name = NULL;
	public $description ='';
	public $info_is_full;
	public $temporary = true;
	public $is_main_scheme = false;

	public function __construct($id,$name) {
		$this->id = $id;
		$this->name = $name;
		
		$info_is_full = false;
	}
	

	
}


function sortByNameFunction(Scheme $a, Scheme $b) {
  	$i = strcmp($a->name, $b->name);
	return $i>0;	
}
     
function getNotTemporary(Scheme $a) {
   	return !($a->temporary);
}

/**
 * 
 * Менеджер схем..
 * @author user
 *
 */

class ControllersManager {
 	 private $database=NULL;
	 private $site=NULL;
	 private $controllers_table;
	 
	 private $controllers;
	 private $list_loaded=false;
	 

     public function __construct (CSite $site, $options) {
	 	$this->site = $site;
	 	$this->database = $site->getDBDriver();
	 	$this->controllers_table=$options['prefix'].'controllers';
	 	$this->controllers=array();
	 }
	 

	 //функция для удаления старых временных схем и всей связанной информации
	 public function deleteTemporaries() {
	 	  //@TODO Вбить нормальные временные параметры
	 	$this->_delete_controller("temporary AND TIMESTAMPDIFF(HOUR,createtime,NOW())>48;");
	
	 }
	 
	 /**
	  * 
	  * Функция возвращает число удалённых схем
	  * @param unknown_type $id
	  */
	 
	 public function deleteController($id) {
	 	return $this->_delete_controller( "controller_id=$id");
	 }
	 
	 /**
	  * 
	  * Функция удаления схемы
	  * @param unknown_type $condition
	  */
	 public function _delete_controller($condition) {
	 	  $driver=$this->site->getDBDriver();
	 	  $driver->simpleExec("LOCK TABLES $this->controllers_table WRITE");
	 	  $list_query = $driver->exec("SELECT controller_id FROM $this->controllers_table WHERE $condition ");
		  $driver->simpleExec("DELETE FROM $this->controllers_table_table WHERE  $condition");
	 	  $list = '';
	 	  $len_count = 0;
	 	  //Разбиваем список на участки по
         //
          $driver->simpleExec("UNLOCK TABLES");
	 	  for ($i=0; $i<$list_query->num_rows(); $i++) {
	 	  	$row = $list_query->getRow();
	 	  	$controller_id = $row[0];

	 	  	if (array_key_exists($controller_id,$this->controllers))
	 	  		unset ($this->controllers[$row[0]]);
	 	  };
	 	  return $list_query->num_rows();
	 	
	 	
	 }
	 
	 public function createTemporary() {
	      $driver=$this->site->getDBDriver();
	 	  $driver->simpleExec("LOCK TABLES $this->controllers_table WRITE");
	 	  $query = $driver->exec("INSERT INTO $this->controllers_table (temporary,createtime) VALUES(true,NOW())");
		  $insert_id = $query->insert_id();
	 	  $driver->simpleExec("UNLOCK TABLES");
	 	  
	 	  //все схемы сь
	 	  $this->deleteTemporaries();
	 	  
	 	  $controller = new Controller($insert_id,"Новый контроллер $insert_id");
	 	  $this->controllers[$insert_id] = $controller;
	 	  return $controller;
	 	  
     }
     
     /**
      * Функция возвращает информацию о схеме по идентификатору 
      */
     public function getInfo($id) {
     	//пытаемся найти в словаре:
     	$need_load = false;
     	$controller = NULL;
     	if (array_key_exists($id, $this->controllers)) {
     		 $controller  = $this->controllers[$id];
     		 $need_load = !$controller->info_is_full;
     	} else {
     		$controller  = new Controller($id,'');
     		$need_load = true;
     		$this->controllers[$id] = $controller;
     	};

     	if ($need_load) {
     		$driver=$this->site->getDBDriver();
     	    $driver->simpleExec("LOCK TABLES $this->controllers_table READ");
     		$query = $driver->exec("SELECT name, is_temporary, createtime 
     								FROM $this->controllers_table
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
		return  $controller;
     }


     
     /**
      *  
      * Функция сохраняет информацию о схеме в БД
      * @param Scheme $scheme
      */
     
     public function save(Controller $controller, $return_saved ) {
     	$driver=$this->site->getDBDriver();
     	$name = $driver->escapeString($controller->name);


     	$driver->simpleExec("LOCK TABLES $this->controllers_table WRITE");
     	$query = $driver->exec("UPDATE $this->controllers_table SET name='$name',temporary=false WHERE controller_id=$controller->id");

		if ($return_saved)
     		$new_controller = $this->getInfo($controller->id);
     	$driver->simpleExec("UNLOCK TABLES");			
     	if ($return_saved)
     		return $new_controller;
     }
     /**
      * Функция возвращает список схем 
      *
      */
     public function getControllersList() {
     	if (!$this->list_loaded) {
     		$driver=$this->site->getDBDriver();
            $controllers_table = $this->controllers_table;
     		$driver->simpleExec("LOCK TABLES $this->controllers_table READ");
     		$query = $driver->exec("
				SELECT $controllers_table.controller_id, $controllers_table.name,temporary
				FROM $controllers_table
				WHERE not(temporary) 
			");

     		$driver->simpleExec("UNLOCK TABLES");
     		for ($i = 0; $i< $query->num_rows(); $i++) {
     			$row = $query->getRow($i);
     			if (!array_key_exists($row[0], $this->controllers)) {
					$controller = new Controller($row[0],$row[1]);
					$controller->temporary = $row[2];
					$this->controllers[$row[0]] = $controller;
     			}
     		}
     		$this->list_loaded = true;
     	}
     	return $this->controllers;
     }
     
     

     /**
      * Функция возвращает список схем, сортированный по именам
      */
     public function getNotTemporaryControllersListOrdered() {
		$this->getControllersList();
		$notTemporaryControllers = array_filter($this->controllers, "getNotTemporary");
     	return $notTemporaryControllers;
     }
  
};