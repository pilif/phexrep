<?php
RequestMapper::registerRequest('#^/exceptions#', '/exceptions', 'ExceptionController');


class ExceptionController extends BaseController{

    function handle($url){
        return array('gnegg' => 'blepp');
    }

}