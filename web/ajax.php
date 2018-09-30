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

function errorRequest($message = NULL, $buffer = NULL)
{
    print('<?xml version="1.0"  encoding="utf-8"?>');
    if ($message) {
        print('<error type="error_request">');
        if ($buffer) {
            //print('<buffer>'.htmlspecialchars($buffer).'</buffer>');
        };
        print(htmlspecialchars($message));
        print('</error>');

    } else {
        print('<error type="error_request" />');
    }
}



function  getControllersList(CSite $site)
{
    $options = &$site->getOptions();
    ob_start();
    $controllers_manager = $site->getControllersManager();
    $controllers = $controllers_manager->getControllersList();


    $out = ob_get_contents();
    ob_end_clean();
    if ($options["debug"])
        print($out);
    print('<?xml version="1.0"  encoding="utf-8"?>');

    print('<controller_list>');
    print(htmlspecialchars(json_encode( $controllers)));
    print('</controller_list>');
}

;


header("Content-type:text/xml", true);
try {

    $prefix = "components";
    $site = new CSite($prefix);
    $user = $site->getUser();
    if (!$user->getAuthorized()) {
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
                getControllersList($site);
                break;
            default:
                errorRequest();
        };
    }
} catch (ESQLException $exc) {
    print($exc);
    errorRequest();
};


?>