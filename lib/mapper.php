<?php

class RequestMapper {
    private static $controllers;

    static function registerRequest($regex, $index, $class){
        self::$controllers[] = array('pattern' => $regex, 'index' => $index, 'cls' => $class);
    }

    static function excuteAction($url){
        
    }
}
