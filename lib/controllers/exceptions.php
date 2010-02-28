<?php
RequestMapper::registerRequest('#^/exceptions#', '/exceptions', 'ExceptionController');


class ExceptionController extends BaseController{

    function handle(){
        return array('gnegg' => 'blepp');
    }

}