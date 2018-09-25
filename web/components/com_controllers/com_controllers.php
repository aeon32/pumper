<?php 
   
   require_once (PATH_BASE.'/framework/abstractcomponent.php');

   
   class com_controllers extends  AbstractComponent {

     
     private $site = NULL;
     private $controllers_manager = NULL;
   	 
     public function __construct(CSite $site) {
   	   $this->site=$site;
   	   
   	   $this->controllers_manager = $site->getControllersManager();
   	   if ($site->getAction()=='delete') {
   	   		$this->delete();	
   	   }
   	 }
   	 

   	 
   	 public function getTitle() {
   	 	return "Стартовая страница";
   	 }
   	 
   	/**
  	 * Функция возвращает ключевые слова (заголовок для страницы)
  	 * @return  string заголовок страницы
  	 */
  	public function getKeyWords() {
   	 	return "Wifi";	
  	}
  	
   /**
  	 * Функция возвращает description(заголовок для страницы)
  	 * @return  string заголовок страницы
  	 */
  	public function getDescription() { 
   	 	return "Описание страницы";		
  	}
  	 
   	 
   	 public function getCSSFile() {
   	 	return "new_main.css";
   	 }

   	private function renderForm() {
		?>
<div class="content">	
  <div class="header_div">
    <h1>Cхемы расположения</h1>
    <form name="adminForm" method="post" action="#">
	<input  id="accept_but" type="button" value="Добавить" />
	<input  id="recycle_but" type="button" value="Удалить" />
    </form>
  </div>


<form name="admin_table" id="admin_table" method="post" action="">
<input type="hidden" name="action" id="action" value="accept" />
<table width="100%" class="list_table">
	<tr>
		<th class="td_small">#</th>
		<th class="td_small">&nbsp;</th>
		<th>Название</th>
		<th>Количество wifi-станций</th>
	</tr>
<?php 
	$i = 1;
	$controllers = $this->controllers_manager->getNotTemporaryControllersListOrdered();
	$polos=false;
    foreach ($controllers as &$value) {
	    $class=$polos? ' class="polos_tr" ':NULL;
	    print("
	       <tr $class>
	        <td class=\"td_small\">$i</td>
			<td class=\"td_small\"><input type=\"checkbox\" id=\"a$value->id\"  name=\"selected$value->id\" /></td>
			<td class=\"td_big\"><a href=\"scheme_edit/$value->id/\">".htmlspecialchars($value->name)."</a></td>
			<td class=\"td_big\">".$value->wifi_num."</td>
		</tr>");
		$polos=!$polos;
		$i++;
    }
?>
</table>
</form>
</div>
<?php
	}   	 
  
     
   	 public function render() {
   	 	$this->renderForm();
?>

<?php 	 	
   	 }
   	
   	 public function getAuxScripts() {
	    return array('js/com_schemes.js');
   	    //return array('js/com_install.js');
   	 }
   	 
   	/*
	 * Функция удаляет множество схем
	 * 
	 */
	public function delete() {
	 $to_delete=array();
	 $files_to_delete=array();
	 foreach($_POST as $key=>$value) {
	  	  $fl_array=array();
	  	  if (preg_match("/selected([0-9]+)/", $key,$fl_array)) {
           $scheme_id=(int)$fl_array[1];
           if ($value=="on" && $scheme_id!==0) 
             $to_delete[]=(int)($scheme_id);
	  	  }   
	  }
	  $to_delete[]="-1";
	  $to_delete=implode(',',$to_delete);
	  $this->controllersManager->_delete_scheme(" controller_id in ($to_delete)");
 	}
 	  	 
   }
   

?>