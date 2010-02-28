<?php

abstract class BaseController {
    public function render(){
        header('Content-Type: application/json');
        $data = json_encode($this->handle());
        header('Content-Length: '.strlen($data));
        echo $data;
    }

    abstract function handle();
}
