<?php 
   
   require_once (PATH_BASE.'/framework/abstractcomponent.php');
   require_once (PATH_BASE.'/components/com_controllers/com_controllers.php');
   
   class com_main extends  com_controllers {

     public function __construct(CSite $site) {
       $controllers_manager = $site->getControllersManager();
       parent::__construct($site);
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
   	 	return "main.css";
   	 }
   	 
  
     
   	 public function render() {
?>

<?php 	 	
   	 }
   	
   	 public function getAuxScripts() {
   	  return array();
   	 }
   }

?>
