<?php
RequestMapper::registerRequest('#^/exceptions', 'ExceptionController');


class ExceptionController extends BaseController{

    function handle(){
        return array('gnegg' => 'blepp');
    }

}