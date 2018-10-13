<?php
/**
 * Класс ССonfig cодержит параметры конфигурации сайта
 *
 *
 */

class CConfig
{
    /* Site Settings (значения по умолчанию) */
    public $host = 'localhost';
    public $user = 'pump_user';
    public $password = '12345678';
    public $database = NULL;
    public $prefix = 'pump_';
    public $debug = true;
    public $session_name = "pumpcontroller";

    public $sitename = "wifi.ru";
    public $pid_file = "/var/lib/snmp-controller/snmp_daemon.pid";


    public $use_ierar_links = 1;             //использовать иерархические ссылки

    public $session_expire = 130;                 //время жизни сессии



    public function __construct()
    {
        $options = @parse_ini_file(dirname(__FILE__) . '/configuration.ini');
        if (!is_array($options))
            return;
        if (array_key_exists('host', $options)) {
            $this->host = $options['host'];
        }

        if (array_key_exists('user', $options)) {
            $this->user = $options['user'];
        }

        if (array_key_exists('password', $options)) {
            $this->password = $options['password'];
        }

        if (array_key_exists('database', $options) && strlen($options['database'])) {
            $this->database = $options['database'];
        } else {
            $this->database = NULL;
        };

        if (array_key_exists('session_name', $options)) {
            $this->session_name = $options['session_name'];
        }

        if (array_key_exists("command_timeout", $options))
        {
            $this->command_timeout = (int) $options["command_timeout"];
        }

    }

    public function saveSettings()
    {
        $handle = fopen(dirname(__FILE__) . '/configuration.ini', 'w');
        if ($handle) {
            $options = (array)$this;
            $res = "";
            foreach ($options as $key => &$value) {
                $res .= "$key=$value\n";
            }
            fwrite($handle, $res);
            fclose($handle);
        };
    }
}

?>