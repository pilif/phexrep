<?php

class Configuration {
    var $_config;

    function __construct($fn = ''){
        if (!$fn)
            $fn = implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', 'config.ini'));
        $this->read_config($fn);
    }

    function getValue($section, $name){
        return $this->_config[$section][$name];
    }

    private function read_config($fn){
        $this->_config = parse_ini_file($fn, true);
    }

}

function c(){
    if (!$GLOBALS['cfg']) $GLOBALS['cfg'] = new Configuration();
    return $GLOBALS['cfg'];
}