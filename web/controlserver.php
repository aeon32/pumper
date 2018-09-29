<?php
require_once("configuration.php");
require_once("framework/mysqldb.php");
require_once("framework/session.php");
require_once("framework/user.php");
require_once("framework/pumpprotocol.php");
require_once("framework/pumpcommandengine.php");
require_once("framework/testcommandengine.php");

class ControlServerException extends Exception {
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

class ControlServer
{
    static private $instance = NULL;     //Ссылка на единственный экземпляр класса CSite
    private $database = NULL;            //ссылка на базу данных (СВ
    private $options = NULL;             //опции базы данных

    public $url_parameters = NULL;       //параметры иерархической ссылки
    public $get_parameters = NULL;       //параметры, передаваемые методом get (за исключением параметров, передаваемых по ссылке)
    private $controllers_manager = NULL;
    private $pump_command_engine = NULL;    //обработчик команд
    private $testing_pump_command_engine = NULL;
    private $testing = false;
    private $test_mode = NULL;

    public $ierar_link = false;          //использовалась иерархическая ссылка при заходе на сайт

    /**
     *функция возвращает ссылку на единственный экземпляр CSite
     * @return CSite
     * $prefix-параметр, задающий префикс компонентов (по сути режим-папку, откуда будут браться компоненты)
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new ControlServer();
        };
        return self::$instance;
    }

    /**
     * Функция возвращает указатель на объект базы данных
     */
    public function getDBDriver()
    {
        return $this->database;
    }


    /** Конструктор.
     *  Создаёт основные объекты для работы с сайтом
     *
     */
    public function __construct()
    {
        $this->options = (array)(new CConfig);   //получили массив свойств
        if ($this->options["debug"])
        {
            ini_set('display_errors', 1);
            error_reporting(E_ALL ^ E_NOTICE);
        };

        if ($this->options["database"]) {       //типа если всё проинсталировано...
            $this->gen_ierar_links = $this->options['use_ierar_links'];
            $this->database = new CMySQLDriver($this->options);


            //process url
            $this->urlDispatcher();
            if ($this->options["debug"] && is_string($this->test_mode))
            { //in testing mode we use testcommandengine
                $this->testing_pump_command_engine  = new TestCommandEngine($this->database, $this->test_mode);
            };

            $this->pump_command_engine = new PumpCommandEngine($this->database);


        } else {
            //$this->install();
            //TODO : error
            throw ControlServerException("Server does not install properly");
        }

    }


    /**
     * Функция возвращает опции
     */
    public function getOptions()
    {
        return $this->options;
    }


    /**
     *  function handles incoming request from controller, do commands
     *  Returns one of PumpMessageBase child class instance
     */
    private function _handleRequest($parsedRequest)
    {
        if ($this->options["debug"] && is_string($this->test_mode))
        {
            $res = $this->testing_pump_command_engine->processCommand($parsedRequest);
        } else
        {
            $res = $this->pump_command_engine->processCommand($parsedRequest);
        };


        return $res;

    }


    /**
     *
     */
    public function run()
    {
        //$component_name = NULL;             //имя компонента, который будет отображать основное содержимое


        $decoded = $_SERVER['QUERY_STRING'];
        $decoded = base64_decode($decoded);
        $output = NULL;

        $parsedRequest = pumpProtocolMessageFromBytes($decoded);
        if (!is_null($parsedRequest))
            $output = $this->_handleRequest($parsedRequest);


        if (is_string($output)) {
            //header('Content-Type:application/octet-stream');
            //header('Content-Length:' . strlen($output));
            print ($output);
        }
        else
            http_response_code(500);


    }


    private function urlDispatcher()
    {
        $script_name = dirname($_SERVER['SCRIPT_NAME']) . '/';


        $this->url_parameters = explode('/', str_ireplace($script_name, '', $_SERVER['REQUEST_URI']));


        if (count($this->url_parameters) && empty($this->url_parameters[0])) {
            unset($this->url_parameters[0]);
            $this->url_parameters = &array_values($this->url_parameters);
        };

        if (count($this->url_parameters) > 1) {  //по идее, в этом случае мы имеем дело с иерархическими ссылками
            $this->ierar_link = true;
            unset($this->url_parameters[count($this->url_parameters) - 1]);  //стираем ненужную хрень

            //обрабатываем второй параметр;
            $second_parameter = array_key_exists(1, $this->url_parameters) ? $this->url_parameters[1] : NULL;
            $third_parameter = array_key_exists(2, $this->url_parameters) ? $this->url_parameters[2] : NULL;

            if ($second_parameter == "test")
            {
                $this->test_mode = $third_parameter;

            };



        };

    }


    /**
     * Функция возвращает менеджер схем;
     */
    public function getControllersManager()
    {
        if (!isset($this->controllers_manager)) {
            require_once("framework/controller.php");
            $this->controllers_manager = new ControllersManager($this, $this->options);
        };
        return $this->controllers_manager;
    }

}

;

/**
 *Функция startApplication создаёт единственный экземпляр объекта CSite и выводит ошибку в случае неудачи
 * @return  ControlServer instance
 */
function startApplication()
{
    try {
// @set_magic_quotes_runtime(0);       //Вырубаем нафиг
        $site = ControlServer::getInstance(); //Получили ссылку на единственный объект

        $site->run();
        return $site;
    } catch (Exception $exc) {         //Если произошла ошибка создания ключевых объектов
        http_response_code(500);
        die();
    };
};

//phpinfo();

startApplication();
?>
