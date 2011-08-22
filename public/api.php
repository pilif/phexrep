<?php
setlocale(LC_ALL, 'de_CH.iso-8859-1');
# do some magic to patch up PATH_INFO to work correctly be
$p = preg_quote('/'.basename(__FILE__));
$_SERVER['PATH_INFO'] = preg_replace("#^$p#", "", $_SERVER['PATH_INFO']);

require_once(__DIR__.'/../lib/init.php');

$action = $_SERVER['PATH_INFO'] ? $_SERVER['PATH_INFO'] : '/index';
RequestMapper::excuteAction($action);

