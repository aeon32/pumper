<?php 
   
   require_once (PATH_BASE.'/framework/abstractcomponent.php');

   
   class com_change_password extends  AbstractComponent {
     //Режим работы:
   	 const CHANGE_PASSWORD=1;   //Пользователь пытается поменять пароль
     const SHOW_FORM = 2;       //Просто показать форму


   	 private $state = NULL;
   	 private $site = NULL;
	 private $user = NULL;
	 
	 private $login;
	 private $newPassword;
	 private $oldPassword;
	 private $retypeNewPassword;

	 private $error;
	 
     public function __construct(CSite $site) {
   	   $this->site=$site;
   	   $this->user=$site->getUser();
   	   $this->state= self::SHOW_FORM;
   	   if (!$this->user->getAuthorized())
   	   		die();	
   	    //пользователь пытается поменять пароль
   	   	if ($site->getAction() == "change_password") {
   	   		$this->state = self::CHANGE_PASSWORD;	   	   	
   	   };
   	 }
   	 
   	 public function getTitle() {
   	 	return "Страница смены пароля";
   	 }
   	 
   	 /**
   	  * 
   	  * Функция меняет пароль. Возвращает NULL либо описание ошибки
   	  */
   	 
   	 public function changePassword() {
		if ($this->newPassword != $this->retypeNewPassword) {
			return "Значения полей \"Новый пароль\" и \"Повторите новый пароль\" не совпадают";	
		};
		try {
			$this->user->changePassword($this->login, $this->oldPassword, $this->newPassword);
		} catch (EUserException $exc) {
			return ($exc->getMessage());
		};
  		 	
   	 }
   	 
   	/**
  	 * Функция возвращает ключевые слова (заголовок для страницы)
  	 * @return  string заголовок страницы
  	 */
  	public function getKeyWords() {
   	 	return "Страница смены пароля";	
  	}
  	
   /**
  	 * Функция возвращает description(заголовок для страницы)
  	 * @return  string заголовок страницы
  	 */
  	public function getDescription() { 
   	 	return "Страница смены пароля";		
  	}
  	 
   	 
   	 public function getCSSFile() {
   	 	return "new_main.css";
   	 }
   	 
    
     public function changed() {
?>
<h2>Пароль изменён</h2>
<form id="avtoriz" action="main/" method="post" >

<input type="submit" id="adm_ent" name="adm_ent" value="Выход" />
<input type="hidden" name="action" value="logout" />
</form>
<?php      	
     		
     }
     
   	 public function render() {
   	 	switch ($this->state) {
   	 		case self::CHANGE_PASSWORD:
   	 			   	$this->login = array_key_exists('login',$_POST) ? $_POST['login'] : '';
   	 			   	$this->oldPassword = array_key_exists('old_password',$_POST) ? $_POST['old_password'] : '';
					$this->retypeNewPassword = array_key_exists('repeat_new_password',$_POST) ? $_POST['repeat_new_password'] : '';   	 			   	
					$this->newPassword = array_key_exists('new_password',$_POST) ? $_POST['new_password'] : '';
					$this->error = $this->changePassword();

					if ($this->error)
   	 					$this->renderForm();
   	 				else 
   	 				    $this->changed();
   	 			break;
   	 		case self::SHOW_FORM:
   	 			   	$this->login = $this->user->getProperty('login');
   	 				$this->renderForm();
   	 			break;
   	 		
   	 	};
   	 }
   	 
   	 public function renderForm() {

?>

<div class="content">
 <div class="header_div">
    <h1>Смена пароля</h1>
 </div>

<form id="monitoring_form" action="change_password/" method="post"  class="none">
<?php if ($this->error) print("<h5>Ошибка:$this->error</h5>")?>
<div class="params">
<p>
<label for="admin_pass" class="property_label">Старый пароль:&nbsp;</label> <input type="password" id="old_password" name="old_password" /> 
</p>

<p>
<label for="admin_login" class="property_label">Новый логин:&nbsp;</label> <input type="text" id="login" name="login" value="<?php print(htmlspecialchars($this->login)); ?>"/> 
</p>

<p>
<label for="admin_login" class="property_label">Новый пароль:&nbsp;</label> <input type="password" id="new_password" name="new_password" /> 
</p>
<p>
<label for="admin_pass" class="property_label">Повторите новый пароль:&nbsp;</label> <input type="password" id="repeat_new_password" name="repeat_new_password" /> 
</p>
<input type="hidden" name="action" value="change_password" />
<p>
<input type="submit" id="change_password_button" name="change_password_button" value="Поменять пароль" />
</p>
</form>

</div>
</div>


<?php 	 	
   	 }
   	
   	 public function getAuxScripts() {
   	  return array();
   	 }
   }

?>
