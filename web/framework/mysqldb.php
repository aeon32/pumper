<?php
/**
 * В этом файле описаны классы для работы с ДБ MySQL
 *
 *
 */
require_once("abstractdb.php");


class CMySQLQuery extends CDBQuery
{
    private $link = false;     //указатель на соединение
    private $script = '';      //текст скрипта
    private $result = false;   //результат работы скрипта
    private $debug = false;
    private $affected_rows = 0;
    private $last_insert_key = NULL; //идентификатор последней вставленной строки

    /**
     * Конструктор для создания экземляра драйвера CMySQLDriver
     * Может выбрасывать исключение класса ESQLException
     * @access    public
     * @param    $link -указатель на соединение с базой данных
     * @param  $script string- SQL-текст запроса
     */
    public function __construct($link, $script, $debug = false)
    {
        $this->debug = $debug;
        $this->link = $link;
        $this->script = $script;
        $this->result = @mysqli_query($link, $script);
        $this->affected_rows = @mysqli_affected_rows($link);
        $this->last_insert_key = @mysqli_insert_id($link);
        if (!$this->result) {
            //Если запрос не был завершён успешно
            $servermessage = mysqli_error($link);
            $errorno = mysqli_errno($link);
            throw (new ESQLException("Ошибка  выполнения запроса SQL." . ($debug ? "Текст запроса:$script. Сообщение сервера: $servermessage." : "") . "Код ошибки: $errorno "));
        }

    }

    /**
     * Функция возвращает количество столбцов запроса
     * @return  int
     */
    public function num_fields()
    {
        if ($this->result) {
            return mysqli_num_fields($this->result);
        };
        return 0;
    }

    /**
     * Функция возвращает количество строк  запроса
     * @return int
     */
    public function num_rows()
    {
        if ($this->result) {
            return mysqli_num_rows($this->result);
        }
    }

    /**
     * Функция возвращает количество затронутых строк в БД
     *
     */
    public function affected_num_rows()
    {
        return $this->affected_rows;
    }

    /**
     * Функция возвращает последнее сгенерированное AUTOINCREMENT-значение
     *
     */
    public function insert_id()
    {
        return $this->last_insert_key;

    }


    /**
     * Функция возвращает результат запроса как ассоциативный массив
     * где в качестве ключа используется значение ключевого поля таблицы
     */
    public function getResultAsArray($key_name)
    {
        if ($this->result) {
            $res = array();
            mysqli_data_seek($this->result, 0);
            while ($row = mysqli_fetch_assoc($this->result)) {
                $res[] = $row;
            }
            return $res;
        } else
            return NULL;
    }

    /**
     * Enter description here...
     *
     */

    public function __destruct()
    {
        if (is_object($this->result)) {
            mysqli_free_result($this->result);
        };
    }

    /**
     *  Функция возвращает одну строку результата как ассоциированный массив;
     *
     */
    public function getRowAssoc()
    {
        if (is_object($this->result)) {
            return mysqli_fetch_assoc($this->result);
        } else
            return NULL;
    }

    /**
     * Функция возвращает одну строку результата как числовой массив
     *
     */
    public function getRow()
    {
        if ($this->result) {
            return mysqli_fetch_row($this->result);
        }
    }
}

;

//Класс CMySQLDriver-реализация драйвера для базы данных MySQL
class CMySQLDriver extends CDBDriver
{

    private $host;                          //параметр "хост"
    private $user;                          //параметр "имя пользователя"
    private $password;                      //параметр "пароль"
    private $prefix;                        //префикс таблиц БД
    private $link = false;                    //переменная хранит соединение с базой данных
    private $debug = false;                   //Отладочная версия сайта

    /**
     * Конструктор для создания экземляра драйвера CMySQLDriver
     *
     * @access    public
     * @param    array    Функция принимает массив опций соединения с базой данных.
     *                   Передаются параметры host,user,password,database,prefix,select
     */
    public function __construct(& $options)
    {
        $host = array_key_exists('host', $options) ? $options['host'] : 'localhost';
        $user = array_key_exists('user', $options) ? $options['user'] : '';
        $password = array_key_exists('password', $options) ? $options['password'] : '';
        $database = array_key_exists('database', $options) ? $options['database'] : '';
        $this->prefix = array_key_exists('prefix', $options) ? $options['prefix'] : 'pz_';
        $this->debug = array_key_exists('debug', $options) ? $options['debug'] : false;

        //Устанавливаем соединение с базой данных
        if (!$this->link = @mysqli_connect($host, $user, $password)) {
            $servermessage = mysqli_connect_error();
            $errorno = mysqli_connect_errno();
            throw (new ESQLException("Ошибка при соединении с базой данных." . ($this->debug ? "Сообщение сервера: $servermessage." : "") . "Код ошибки: $errorno"));
        };

        if (!@mysqli_set_charset($this->link, "utf8")) {
            $servermessage = mysqli_connect_error();
            $errorno = mysqli_connect_errno();
            throw (new ESQLException("Ошибка при соединении с базой данных." . ($this->debug ? "Сообщение сервера: $servermessage." : "") . "Код ошибки: $errorno"));
        };

        $this->select($database);
    }

    /**
     * Функция экранирует строку для использования в mysql-скрипте
     *
     */
    public function escapeString($str)
    {
        return mysqli_real_escape_string($this->link, $str);
    }

    /**
     * Функция, выбирающая базу данных
     * @access public
     * @param  string имя базы данных
     */
    public function select($database)
    {
        if (!@mysqli_select_db($this->link, $database)) {
            $servermessage = mysqli_error($this->link);
            $errorno = mysqli_errno($this->link);
            throw (new ESQLException("Ошибка при соединении с базой данных." . ($this->debug ? "Сообщение сервера: $servermessage." : "") . "Код ошибки: $errorno"));
        };
    }


    /**
     * Функция, выполняющая запрос и возвращающая результат в виде объекта CDBQuery
     *
     * @access    public
     * @param    string    Функция принимает текст запроса
     * @return   CDBQuery -объект, инкапсулирующий результат запроса
     */
    public function exec($script)
    {
        return new CMySQLQuery($this->link, $script, $this->debug);
    }

    /**
     * Функция, выполняющая запрос без завершения результата.Может выбрасывать исключени типа ESQLException
     * @param string функция принимает текст запроса
     */
    public function simpleExec($script)
    {
        $result = @mysqli_query($this->link, $script);
        if (!$result) {
            //Если запрос не был завершён успешно
            $servermessage = mysqli_error($this->link);
            $errorno = mysqli_errno($this->link);
            throw (new ESQLException("Ошибка  выполнения запроса SQL." . ($this->debug ? "Текст запроса:$script. Сообщение сервера: $servermessage." : "") . "Код ошибки: $errorno "));
        }
        if (is_resource($result))
            @mysqli_free_result($result);
    }


    /**
     * Деструктор класса. Уничтожает всё нахрен
     * @access    public
     */
    function __destruct()
    {
        if ($this->link)
            @mysqli_close($this->link);

    }

    /**
     * Функция, возвращающая префикс базы данных
     * @access public
     * @param  none
     * @return string- префикс таблиц базы данных
     *
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Функция, возвращающая последний идентификатор после выполнения операции INSERT с автоинкрементом;
     *
     */
    public function getInsertId()
    {
        return @mysqli_insert_id($this->link);
    }
}

?>