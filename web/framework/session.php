<?php
/**
 * Класс для поддержки HTTP-сессий
 * 
 *
 */

@ini_set('include_path',"pear/".":".ini_get('include_path'));
require_once ('HTTP/Session2.php');

class CSession {
	
	/**
	 * внутреннее состояние
	 *
	 * @access protected
	 * @var	string $_state one of 'active'|'expired'|'destroyed|'error'
	 * @see getState()
	 */
	var $_state = 'active';
	
	
	/**
	 * конструктор сессии. Открывает новую сессию.
	 *
	 */
	public function __construct($options) {
		//$this->destroy(); 
		//set default sessios save handler
		/*
		@ini_set ( 'session.save_handler', 'files' );
		//disable transparent sid support
		@ini_set ( 'session.use_trans_sid', '0' );
		@session_start ();
		*/
		HTTP_Session2::useCookies(true);
		HTTP_Session2::useTransSID(false);
		HTTP_Session2::start($options["session_name"]);
		if (HTTP_Session2::isIdle() ) {
			HTTP_Session2::destroy();
			HTTP_Session2::start($options["session_name"]);
		};
		
		//HTTP_Session2::setExpire(time() + $options["session_expire"]*60);
		HTTP_Session2::setIdle($options["session_expire"]*60);
				
		return true;
	}
	
	/**
	 * Функция возвращает текущий идентификатор сессии
	 * @return id 
	 */
	public function getCurrentId() {
		return HTTP_Session2::id ();
	}
	
	/**
	 * Деструктор сессии
	 */
	public function __destruct() {
		//@session_write_close (); //Закрываем сессию
		//@session_destroy ();
		HTTP_Session2::pause();
	}
	
	/**
	 * Функция закрывают сессию
	 *
	 */
	
	public function destroy() {
		/*
		if (@session_id ()) { //Закрываем предыдущую сессию (если она существовала)
			@session_write_close ();
			@session_unset ();
			@session_destroy ();
		}*/
		HTTP_Session2::destroy();
	}
	
	/**
	 * Получает данные сессии
	 * @static
	 * @access public
	 * @param  string $name	 Имя переменнной,которое надо получить из хранилища
	 * @param  string $default имя переменной, возвращаемое по дефолту 
	 * @return mixed  Value of a variable
	 */
	function &get($name, $default = NULL) {
		return HTTP_Session2::get($name,$default);
	}
	
	/**
	 * Сохраняет данные сессии
	 * @access public
	 * @param  string $name  		Имя переменной
	 * @param  mixed  $value 		Значение переменной
	 * @return mixed  Старое значение переменной
	 */
	function set($name, $value) {
		HTTP_Session2::set($name,$value);
	}
	
	/**
	 * Проверяет, установлена ли сессионная переменная
	 * @access public
	 * @param string 	$name 		Имя переменной
	 * @return boolean $result true , если переменная существует
	 */
	function has($name) {
		return isset ( $_SESSION [$name] );
	}
	
	/**
	 * Функция возвращает капча-код (если такой установлен) и NULL в противном случае 
	 */
	public function getCaptchaCode() {
		return $this->get ( "cr_captcha_code", NULL );
	}
	
	/**
	 * Функция устанавливает капча-код
	 */
	
	public function setCaptchaCode($code) {
		$this->set ( "cr_captcha_code", $code );
	}

}
?>