<?php
/**
 * Класс-абстрактный модуль
 *
 */

abstract class CAbstractModule { 
  protected $site=NULL;
  public function  getSite() {
     return $this->site;
  }
  
  public function __construct(CSite $site) {
   $this->site=$site;		
  }
 /** Функция осуществляет вывод модуля 
  * 
  */  	
  abstract function render(array $params=array()); 
	
}
?>