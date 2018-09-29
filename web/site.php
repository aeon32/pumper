<?php
define('PATH_BASE', dirname(__FILE__));

require_once("configuration.php");
require_once("framework/mysqldb.php");
require_once("framework/session.php");
require_once("framework/user.php");


class CSite
{
    static private $instance = NULL;     //Ссылка на единственный экземпляр класса CSite
    private $database = NULL;            //ссылка на базу данных (СВ
    private $session = NULL;             //ссылка на объект сессии
    private $user = NULL;                //ссылка на информацию о текущем пользователе
    private $content = NULL;             //текущий отображаемый модуль (его имя);
    private $options = NULL;             //опции базы данных

    private $component_object = NULL;     //ссылка на объект
    private $prefix = NULL;
    private $gen_ierar_links = false;     //генерировать иерархические ссылки

    private $redirect = false;           //страница будет перенаправлена
    private $action = NULL;

    public $url_parameters = NULL;       //параметры иерархической ссылки
    public $get_parameters = NULL;       //параметры, передаваемые методом get (за исключением параметров, передаваемых по ссылке)
    public $full_url = NULL;             //полный url к скрипту (например, wifi/kamera1/)
    private $controllers_manager = NULL;
    private $installMode = false;
    private $contentname = NULL;

    public $ierar_link = false;          //использовалась иерархическая ссылка при заходе на сайт

    /**
     *функция возвращает ссылку на единственный экземпляр CSite
     * @return CSite
     * $prefix-параметр, задающий префикс компонентов (по сути режим-папку, откуда будут браться компоненты)
     */
    public static function getInstance($prefix = 'components')
    {
        if (!isset(self::$instance)) {
            self::$instance = new CSite($prefix);
        };
        return self::$instance;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * Функция возвращает указатель на объект базы данных
     */
    public function getDBDriver()
    {
        return $this->database;
    }

    /**
     * Функция возвращает указатель на объект сессии
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     *  функция возвращает true, если необходимо генерировать иерархические ссылки
     */
    public function genIerarLinks()
    {
        return $this->gen_ierar_links;
    }

    /**
     * Функция возвращает указатель на текущего пользователя
     *
     */
    public function getUser()
    {
        return $this->user;
    }


    /** Конструктор.
     *  Создаёт основные объекты для работы с сайтом
     *
     */
    public function __construct($prefix)
    {
        $this->prefix = $prefix;
        $this->options = (array)(new CConfig);   //получили массив свойств
        if ($this->options["debug"])
        {
            ini_set('display_errors', 1);
            error_reporting(E_ALL ^ E_NOTICE);
        };

        if ($this->options["database"]) {       //типа если всё проинсталировано...
            $this->gen_ierar_links = $this->options['use_ierar_links'];
            $this->database = new CMySQLDriver($this->options);
            $this->session = new CSession($this->options);
            $this->user = new CUser($this->database, $this->session, $this->options);
            $this->urlDispatcher();
            $this->dispatch();
            $this->createComponent();
        } else {
            $this->install();
        }

    }

    public function isInstallMode()
    {
        return $this->installMode;
    }


    private function install()
    {
        $this->installMode = true;
        $this->contentname = "com_install";
        $this->createComponent();
    }

    /**
     * Функция создаёт компонент для рендеринга основного содержания
     *
     */
    private function createComponent()
    {
        require_once(PATH_BASE . "/$this->prefix/" . ($this->contentname) . "/" . ($this->contentname) . ".php");     //Если ничего не указано-загружаем по умолчанию
        $contentname = $this->contentname;
        $this->component_object = new $contentname($this);
    }

    /**
     * Функция возвращает объект-компонент для рендеринга основного содержания страницы
     */
    public function getComponentObject()
    {
        return $this->component_object;
    }


    /**
     * Функция возвращает опции
     */
    public function getOptions()
    {
        return $this->options;
    }


    /**
     * Функция возвращает имя хоста
     */
    public function getHostName()
    {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * Функция предназначена для рендеринга модуля в произвольном месте
     * @param $modulename -имя модуля (файл в папке modules/
     * @param массив имён option (вариантов содержимого), при котором модуль НЕ отображается
     */
    public function renderModule($modulename, $disable_options = NULL, array $options = array())
    {
        //пытаемся загрузить файл с классом:
        if (!isset($disable_options) || in_array($this->contentname, $disable_options)) {
            $filename = PATH_BASE . "/modules/$modulename/$modulename.php";
            require_once($filename);
            $module = new $modulename($this);
            $module->render($options);
        }
    }

    /**
     * Функция осуществляет рендеринг основного содержимого страницы
     */
    public function renderContent()
    {
        try {
            $this->component_object->render();
        } catch (Exception $exc) {
            print("При обработке страницы произошла ошибка. $exc");
        }
    }

    /**
     * Функция разбирает $_POST и $_GET переменные и осуществляет некоторые первичные действия
     * (логирования пользователя и т.д)
     */
    public function dispatch()
    {
        $component_name = NULL;             //имя компонента, который будет отображать основное содержимое

        if (isset($_GET['option'])) {     //пытаемся определить имя модуля, с которым мы будем отображать информацию
            $component_name = basename($_GET['option']);
        } else if (isset ($_POST['option'])) {
            $component_name = basename($_POST['option']);
        }
        if (isset($component_name)) {
            $filename = PATH_BASE . "/$this->prefix/$component_name/$component_name.php";
            if (file_exists($filename)) {
                $this->contentname = $component_name;
            }
        }


        $action = NULL;
        if (isset($_POST['action'])) {         //action-некое действие
            $action = trim($_POST['action']);
        } else if (isset($_GET['action'])) {
            $action = trim($_GET['action']);
        };
        $this->action = $action;

        if (isset($action)) {
            switch ($action) {
                case 'login':
                    {    //если пользователь пытается залогиниться
                        $login = array_key_exists('login', $_POST) ? $_POST['login'] : NULL;
                        $password = array_key_exists('password', $_POST) ? $_POST['password'] : NULL;
                        $params = array('login' => $login, 'password' => $password);
                        $this->user->login($params);

                        if ($this->user->getAuthorizeStatus() == CUser::AUTHORIZED) {
                            $this->contentname = "com_main";
                            $host = $_SERVER['HTTP_HOST'];
                            $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                            header("Location: http://$host$uri/main/");
                        };
                    };
                    break;
                    break;
                case 'logout':
                    {
                        $this->user->logout();
                    }
                    break;

            }
        }

        if ($action != 'login' && $action != "logout") {
            $this->user->tryToAuthorize();
        };

        if ($this->user->getAuthorizeStatus() != CUser::AUTHORIZED) {
            if ($this->contentname != "com_login") {
                $this->contentname = "com_login";
                $this->redirect = true;
                $host = $_SERVER['HTTP_HOST'];
                $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                header("Location: http://$host$uri/login/");

            };

        };
        if (!isset($this->contentname)) {
            $this->contentname = "com_main";
        };

        if ($this->contentname == "com_main") {
            $this->contentname = "com_controllers";


        }
    }

    /**
     * Функция для обработки иерархических ссылок
     * управлять мы будем, изменяя переменные GET. Криво, но что сделаешь?
     */

    private function urlDispatcher()
    {
        $script_name = dirname($_SERVER['SCRIPT_NAME']) . '/';


        $this->url_parameters = explode('/', str_ireplace($script_name, '', $_SERVER['REQUEST_URI']));
        $this->get_parameters = $_GET;

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

            switch ($this->url_parameters[0]) {
                case 'login':
                    $_GET['option'] = 'com_login';
                    break;
                case 'main_page':
                    $_GET['option'] = 'com_main';
                    break;
                case 'change_password':
                    $_GET['option'] = 'com_change_password';
                    break;
                case 'controllers':
                    $_GET['option'] = 'com_controllers';
                    break;

            };


            $aux_get = '';//вспомогательная переменная
            if (!isset($second_parameter)) {

            }; // if (isset($second_parameter))


            $aux_get2 = array();

            foreach (explode('&', $aux_get) as $value) {
                if (strlen($value)) {
                    $aux = explode('=', $value);
                    if (array_key_exists(0, $aux) && array_key_exists(1, $aux)) {
                        $aux_get2[$aux[0]] = array_key_exists(1, $aux) ? $aux[1] : '';
                    };
                };
            };
            $_GET = array_merge($_GET, $aux_get2);

            $this->full_url = implode('/', $this->url_parameters);
            if (strlen($this->full_url)) $this->full_url .= '/';
        } else {
            $this->full_url = "index.php";
        }

    }

    /**
     * Функция возвращает базовый (base) адрес;
     */
    public function getBaseName()
    {
        $base = "http://" . $_SERVER['HTTP_HOST'];
        if ($_SERVER['SERVER_PORT'] != 80) $base .= ":{$_SERVER['SERVER_PORT']}";
        $base .= dirname($_SERVER['SCRIPT_NAME']);
        $base = trim($base, '.\\/') . '/';
        return $base;
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
 * @return  CSite instance
 */
function startApplication($prefix)
{
    try {
        // @set_magic_quotes_runtime(0);       //Вырубаем нафиг
        $site = CSite::getInstance($prefix);        //Получили ссылку на
        return $site;
    } catch (Exception $exc) {         //Если произошла ошибка создания ключевых объектов 
        ?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    </head>
    <body>
    Произошла фатальная ошибка на стороне сервера <br/>
    <?php
    print ($exc);
    ?>
    </body>
        <?php
        die();
    };
}

?>