<?php

abstract class HttpException extends Exception {
    private $status_code;
    private $status_message;

    function __construct($code, $message){
        $this->status_code = $code;
        $this->status_message = $message;
    }

    function render(){
        header(sprintf('HTTP 1.0 %d %s', $this->status_code, $this->status_message));
        header('Content-Type: application/json');
        $data = json_encode($this->getBody());
        header('Content-Length: '.strlen($data));
        echo $data;
        exit;
    }

    abstract function getBody();
}

class Forbidden extends HttpException{
    private $body;

    function __construct($data){
        parent::__construct(403, 'Forbidden');
        $this->body = $data;
    }

    function getBody(){
        return $this->body;
    }
}
