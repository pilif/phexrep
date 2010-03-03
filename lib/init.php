<?php
define(APP_ROOT, __DIR__.'/..');
define(LIB_DIR, APP_ROOT.'/lib/');

ini_set('include_path', LIB_DIR);

if (file_exists(LIB_DIR.'/_autoload.php')){
    include_once(LIB_DIR.'/_autoload.php');
    function _exrep_autoload($classname){
        $classname = strtolower($classname);
        if ($GLOBALS['__er_autoload_funcmapping'][$classname]){
            $file = LIB_DIR.'/'.$GLOBALS['__er_autoload_funcmapping'][$classname];
            if (file_exists($file))
                include_once($file);
        }
    }
    spl_autoload_register('_exrep_autoload');
}else{
    die("File does not exist: ".CACHE_ROOT.'/_autoload.php');
}

$GLOBALS['cfg'] = new Configuration();

$dir = dir(LIB_DIR.'/controllers/');
while(false !== ($entry = $dir->read())){
    $fp = LIB_DIR.'/controllers/'.$entry;
    if (is_file($fp) && preg_match('#\.php$#', $fp)){
        include($fp);
    }
}