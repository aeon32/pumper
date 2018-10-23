<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.09.18
 * Time: 14:05
 */

class ControllerSession {
    public $id = NULL;
    public $token = NULL;

    public $controller;
    public $active = false;

    public function __construct($id,$token,$controller, $active) {
        $this->id = $id;
        $this->token = $token;
        $this->controller = $controller;
        $this->active = $active;

    }

}


