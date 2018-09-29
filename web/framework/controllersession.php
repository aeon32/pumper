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

    public function __construct($id,$token) {
        $this->id = $id;
        $this->token = $token;

    }

}


