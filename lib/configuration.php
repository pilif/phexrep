<?php

class Configuration {
    var $_config;

    function __construct($fn = ''){
        if (!$fn)
            $fn = implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', 'config.ini'));
        $this->read_config($fn);
    }

    function getValue($section, $name){
        if ( ($section === 'report_format') && ($name === 'app_root')){
            return $this->getDocumentRoot();
        }else{
            return $this->_config[$section][$name];
        }
    }

    private function read_config($fn){
        $this->_config = parse_ini_file($fn, true);
    }

    private function getDocumentRoot(){
        $dr = $this->_config['report_format']['app_root'];
        if ($dr === '%') $dr = $_SERVER['DOCUMENT_ROOT'];
        return $dr;
    }

}

function c(){
    if (!$GLOBALS['cfg']) $GLOBALS['cfg'] = new Configuration();
    return $GLOBALS['cfg'];
}