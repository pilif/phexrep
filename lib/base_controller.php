<?php

abstract class BaseController {
    public function render($url){
        header('Content-Type: application/json');
        $data = json_encode($this->handle($url));
        header('Content-Length: '.strlen($data));
        echo $data;
    }

    abstract function handle($url);
}
