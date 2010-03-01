<?php

abstract class BaseController {
    public function render($url){
#        $this->authenticate();
        header('Content-Type: application/json');
        $data = json_encode($this->handle($url));
        header('Content-Length: '.strlen($data));
        echo $data;
    }

    private function authenticate(){
        $u = c()->getValue('access_control', 'username');
        $p = c()->getValue('access_control', 'password');
        if ($_SERVER['PHP_AUTH_USER'] !== $u  ||
            crypt($_SERVER['PHP_AUTH_PW'], $p) != $p){
            header('WWW-Authenticate: Basic realm="phexrep"');
            throw Unauthorized();
        }
    }

    abstract function handle($url);
}
