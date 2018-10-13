<?php
/*
 * Вспомогательный простой модуль для выдачи результатов аякс-запросов;
 *
 */
require_once("configuration.php");
require_once("framework/mysqldb.php");
require_once("framework/session.php");
require_once("framework/user.php");
require_once("site.php");

/**
 * Пользователь не авторизован
 *
 */
function notAuthorizedResponse()
{
    print('<?xml version="1.0"  encoding="utf-8"?>');
    print('<error type="not_authorized" />');
}

function errorRequest($type, $message = NULL, $buffer = NULL)
{
    print('<?xml version="1.0"  encoding="utf-8"?>');
    if ($message) {
        print("<error type=\"$type\">");
        if ($buffer) {
            //print('<buffer>'.htmlspecialchars($buffer).'</buffer>');
        };
        print(htmlspecialchars($message));
        print('</error>');

    } else {
        print("<error type=\"$type\" />");
    }
}


function getControllersList($database, $options)
{

    ob_start();
    require_once("framework/controller.php");
    $controllers_manager = new ControllersManager($database, $options);
    $controllers = $controllers_manager->getControllersAsPlainArray();


    $out = ob_get_contents();
    ob_end_clean();
    if ($options["debug"])
        print($out);
    print('<?xml version="1.0"  encoding="utf-8"?>');

    print('<controller_list>');
    //print_r($controllers);
    print(htmlspecialchars(json_encode($controllers)));
    print('</controller_list>');
};





function getControllerInfo($database, $options, $controller_id)
{
    if (!$controller_id)
        die();

    ob_start();
    require_once("framework/controller.php");
    require_once("framework/pumpcommandengine.php");

    $controllers_manager = new ControllersManager($database, $options);
    $command_engine = new PumpCommandEngine($database, $controllers_manager, $options);
    $command_result = $command_engine->pushGetControllerInfoCommand($controller_id);


    $out = ob_get_contents();
    ob_end_clean();

    if (is_object($command_result) && $command_result->online)
    {
        print('<?xml version="1.0"  encoding="utf-8"?>');
        print('<controller_info>');
        print('</controller_info>');

    } else {
        $error_string = "";
        switch ($command_result)
        {
            case PumpCommandEngine::$CONTROLLER_NOT_FOUND:
                $error_string = "controller_not_found";
                break;
            case PumpCommandEngine::$CONTROLLER_IS_OFFLINE:
                $error_string = "controller_is_offline";
                break;
            case PumpCommandEngine::$CONTROLLER_NOT_RESPONDS:
                $error_string = "controller_not_responds";
                break;
        }
        errorRequest($error_string);

    }






}




header("Content-type:text/xml", true);

try {

    $options = (array)(new CConfig);   //получили массив свойств
    if ($options["debug"]) {
        header("Access-Control-Allow-Origin:*", true);
        ini_set('display_errors', 1);
        error_reporting(E_ALL ^ E_NOTICE);
    };

    $user = null;
    if ($options["database"]) {       //типа если всё проинсталировано...
        $database = new CMySQLDriver($options);
        $session = new CSession($options);
        $user = new CUser($database, $session, $options);
    };

    if (!$options["debug"] && (!$user || !$user->getAuthorized())) {
        notAuthorizedResponse();
    } else {
        //$_POST['request']='save_data';
        //$_POST['scheme_id'] =40;
        //$_POST['image_file']='usr/';
        //$_POST['name']='edf';
        if (!array_key_exists('request', $_GET))
            $_GET['request'] = '';
        switch ($_GET['request']) {
            case 'get_controllers_list':
                getControllersList($database, $options);
                break;

            case 'get_controller_info':
                getControllerInfo($database,  $options, (int) $_GET["controller_id"]);
                break;
            default:
                errorRequest("error_request");
        };
    }
} catch (Exception $exc)
{
    errorRequest("internal_error", $exc->getMessage());

}



?>