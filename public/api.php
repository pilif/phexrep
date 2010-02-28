<?php

require_once(__DIR__.'/../lib/init.php');

$action = $_SERVER['PATH_INFO'] ? $_SERVER['PATH_INFO'] : '/index';
RequestMapper::excuteAction($action);

