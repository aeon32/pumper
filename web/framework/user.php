<?php

/**
 * Исключение при работе с пользователем
 * @author andreika
 */
class EUserException extends Exception
{
    const LOGIN_ERROR = 1;
    const PASSWORD_ERROR = 2;
    const USER_NOT_FOUND = 3;
    protected $message = "";

    /**
     * Конструктор исключения при работе с БД
     * @param string текст сообщения об ошибке
     * @param string текст сервера
     * @param int код ошибки
     */
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return $this->message;
    }

}

/**
 *  Класс CUser предназначен для идентификации пользователя
 *  Он сохраняет в сессионных переменных параметры пользователя: имя, пароль и т.д
 */
class CUser
{
    const NON_AUTHORIZED = 0;   //Значение флага авторизации "пользователь не авторизован"
    const ERROR_LOGIN = 1;      //Ошибка при логировании
    const AUTHORIZED = 2;

    private $database = NULL;
    private $session = NULL;

    private $users_table;    //Таблица users с учётом префикса
    private $error_login = false;
    /**
     * Массив свойств пользователя;
     * Основные свойства:
     * $id-идентификатор пользователя
     * $nick-его ник
     * $name-полное имя
     */
    private $userinfo = NULL;

    /**
     * Конструктор класса. На основании данных сессии и cookie пытается восстановить информацию о текущем пользователе.
     * Создаётся в CSite в единственном виде;
     * @param драйвер базы данных
     * @param текущая сессия
     */
    public function __construct($database, $session, &$options)
    {
        $this->database = $database;
        $this->session = $session;
        $this->users_table = $options['prefix'] . 'user';
    }

    /*
     * пользователь является администратором
     */
    public function isAdmin()
    {
        return ($this->userinfo['user_id'] === '0');

    }

    /**
     * Функция, пытающаяся авторизовать пользователя
     *
     */
    public function tryToAuthorize()
    {
        if ($this->session->get("cr_user_authorized_status", self::NON_AUTHORIZED) == self::AUTHORIZED) {                //Если данные не содержатся в сессионных переменных
            $this->loadFromDB();
        } else {
            $this->session->set("cr_user_authorized_status", self::NON_AUTHORIZED);
            $this->session->set("cr_user_id", NULL);
        }
    }

    /**
     * Функция возвращает данные о состоянии пользователя, если пользователь авторизован
     *
     */
    public function getAuthorizeStatus()
    {
        if ($this->error_login)
            return self::ERROR_LOGIN;
        else
            return $this->session->get('cr_user_authorized_status', self::NON_AUTHORIZED);
    }

    /**
     * Функция возвращает идентификатор текущего пользователя
     *
     */
    public function getUserId()
    {
        return $this->session->get('cr_user_id', NULL);
    }

    /**
     * Функция загружает информацию о пользователе из базы данных
     */
    public function loadFromDB()
    {
        $user_id = (int)$this->getUserId();
        $users = $this->users_table;
        $query = $this->database->exec("SELECT id,login FROM $users WHERE id=$user_id");
        if ($query->num_rows()) {    //если пользователь нашёлся
            $this->userinfo = $query->getRowAssoc();
            $this->session->set('cr_user_authorized_status', self::AUTHORIZED);
        } else {
            $this->userinfo = NULL;
            $this->error_login = true;
            $this->session->set('cr_user_authorized_status', self::NON_AUTHORIZED);
        }
    }

    /**
     * Функция возвращает свойство пользователя с название id
     *
     */
    public function getProperty($name)
    {
        if (isset($this->userinfo) && array_key_exists($name, $this->userinfo)) {
            return $this->userinfo[$name];
        } else return NULL;
    }

    public function createNewUser($login, $password)
    {
        $nick = trim($login);
        $password = trim($password);
        $md5 = md5($nick . $password);

        $nick = $this->database->escapeString($nick);
        $password = $this->database->escapeString($password);

        $users = $this->users_table;
        $this->database->simpleExec("LOCK TABLES $users WRITE");
        $query = $this->database->exec("DELETE FROM $users");
        $query = $this->database->exec("INSERT INTO $users (id,login,password) VALUES (0,'$nick','$md5')");
        $this->database->simpleExec("UNLOCK TABLES");


    }

    /**
     * Функция используется для установки свойства пользователя;
     */
    public function setProperty($name, $value)
    {

    }

    /**
     *
     * Функция для изменения пароля
     */
    public function changePassword($newlogin, $oldpassword, $newpassword)
    {
        $new_nick = trim((string)$newlogin);
        $new_pass = trim((string)$newpassword);
        $old_pass = trim((string)$oldpassword);
        $new_md5 = md5($new_nick . $new_pass);

        if (strlen($new_nick) < 1) {
            throw new EUserException("Отсутствует имя пользователя", EUserException::LOGIN_ERROR);
        };

        if (strlen($new_nick) > 70) {
            throw new EUserException("Длинное имя пользователя", EUserException::LOGIN_ERROR);
        };

        if (strlen($new_pass) == 0) {
            throw new EUserException("Отсутствует новый пароль", EUserException::PASSWORD_ERROR);
        };

        if (strlen($new_pass) < 4) {
            throw new EUserException("Короткий новый пароль", EUserException::PASSWORD_ERROR);
        };
        $user_id = (int)$this->getUserId();
        $old_md5 = md5($this->getProperty('login') . $old_pass);

        $error_flag = NULL;

        $users = $this->users_table;
        $this->database->simpleExec("LOCK TABLES $users WRITE");
        $query = $this->database->exec("SELECT id FROM $users WHERE id=$user_id AND password='$old_md5' ");
        if ($query->num_rows()) {
            $nick = $this->database->escapeString($new_nick);
            $query1 = $this->database->exec("UPDATE $users SET login='$nick',password='$new_md5' WHERE id=$user_id");
        } else {
            $error_flag = EUserException::USER_NOT_FOUND;

        };
        $this->database->simpleExec("UNLOCK TABLES");
        if ($error_flag == EUserException::USER_NOT_FOUND) {
            throw new EUserException("Неверные пользователь\пароль", EUserException::USER_NOT_FOUND);
        };


    }

    /**
     * Функция используется для входа в систему.
     * Принимает в качестве единственного параметра массив ('password=>password','login=>'login')
     *
     */
    public function login(&$params)
    {

        $nick = trim($params['login']);
        $password = trim($params['password']);
        $md5 = md5($nick . $password);

        $nick = $this->database->escapeString($nick);
        $password = $this->database->escapeString($password);

        $users = $this->users_table;
        $this->database->simpleExec("LOCK TABLES $users READ");
        $query = $this->database->exec("SELECT id,login FROM $users WHERE login='$nick' AND password='$md5' ");
        $this->database->simpleExec("UNLOCK TABLES");


        if ($query->num_rows()) {    //если пользователь нашёлся
            $row = $query->getRow();
            $this->session->set('cr_user_id', $row[0]);  //сохранили идентификатор пользователя  в сессионных переменных (?)
            $this->session->set('cr_user_authorized_status', self::AUTHORIZED);
            //$this->loadFromDB();                        //Загружаем информацию пользователя
        } else {
            $this->session->set('cr_user_authorized_status', self::ERROR_LOGIN);
            $this->error_login = true;
        }


    }

    public function getAuthorized()
    {
        return $this->getAuthorizeStatus() == self::AUTHORIZED;
    }

    /**
     * Функция позволяет пользователь выйти из системы
     */
    public function logout()
    {

        $this->session->destroy();
        /*
        if ($this->session->get("cr_user_authorized_status",self::NON_AUTHORIZED)==self::AUTHORIZED) {
        $users=$this->users_table;
        }
        $this->session->set('cr_user_authorized_status',self::NON_AUTHORIZED);
        */
    }

    /**
     * Функция возвращает параметры пользователя
     *
     */
    public function getInfo()
    {
        return $this->user_info;
    }
}

?>