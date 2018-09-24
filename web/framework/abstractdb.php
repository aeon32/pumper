<?php
 /**
 * В этом файле описаны абстрактные классы для работы с ДБ
 *
 *
 */ 

//ESQLException-класс исключения, вызываемый при работе с ДБ
class ESQLException extends Exception {
  protected $message="";
  /**
   * Конструктор исключения при работе с БД 
   * @param string текст сообщения об ошибке
   * @param string текст сервера
   * @param int код ошибки 
   */
  public function __construct($message,$code=0) {
    parent::__construct($message,$code);
  }
  
  public function __toString() {
    return $this->message; 
  }

}
 



//CDBQuery-класс, инкапсулирующий результаты выполнения запроса к базе данных;
abstract class CDBQuery  {

 /**
  * Функция возвращает количество строк запроса
  * @return  int 
  */
 abstract function num_fields();
 
 

}; //CDBQuery






abstract class CDBDriver {
   /**
	* Конструктор для создания экземляра драйвера CMySQLDriver
	*
	* @access	public
	* @param	array	Функция принимает массив опций соединения с базой данных. 
	*                   Передаются параметры host,user,password,database,prefix,select
	*/
  abstract public function exec($script);
 
  /**
    * Функция, возвращающая префикс базы данных
    * @access public
    * @param  none
    * @return string- префикс таблиц базы данных
    * 
    */  
  abstract public function getPrefix();

  /**
    * Функция, выбирающая базу данных
    * @access public
    * @param  string имя базы данных
    */  
  abstract public function select($database);

 };
 


 
 
?>