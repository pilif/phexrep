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
        $data = json_encode(array(
            'error_code' => $this->status_code,
            'message' => $this->status_message,
            'error_data' => $this->getBody()
        ));
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

class Unauthorized extends HttpException{

    function __construct(){
        parent::__construct(401, 'Unauthorized');
    }

    function getBody(){
        return array();
    }
}


class NotFound extends HttpException{

    function __construct(){
        parent::__construct(404, 'Not Found');
    }

    function getBody(){
        return array();
    }
}

class BadRequest extends HttpException{
    private $reason;

    function __construct($reason){
        parent::__construct(400, 'Bad Request');
        $this->reason = $reason;
    }

    function getBody(){
        return array('reason' => $this->reason);
    }
}

class InternalServerError extends HttpException{

    function __construct(){
        parent::__construct(500, 'Internal Server Error');
    }

    function getBody(){
        return array();
    }
}
