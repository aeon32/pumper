<?php
 /**
  * Модуль-шапка 
  * 
  */
 require_once (PATH_BASE."/framework/abstractmodule.php");
 
 class  mod_header extends CAbstractModule  {
 	
  	public function __construct(CSite $site) {
 		parent::__construct($site);
 	}
 	
 	
 	public function render(array $params=array())  {
	  $site = $this->getSite();
	  if ($site->isInstallMode()) {
?>     
	<div class="head">
    	<a href="#" id="logo"></a>
        <ul id="MenuBar1">
        </ul>
	<div id="strip"></div>
       </div>
<?php  		  
	  
	  } else {
?>     
	<div class="head">
    	<a href="schemes/" id="logo"></a>
        <ul id="MenuBar1">
          <li><a href="сontrollers/">Контроллеры</a></li>
          <li><a href="change_password/">Смена пароля</a></li>
          <li><a href="monitoring_management/">Настройки</a></li>
        </ul>
        <a href="login/?action=logout" id="enter">Выход</a>

	<div id="strip"></div>
       </div>
<?php  	 
      };//else
    } //render
 	
 }


?>
