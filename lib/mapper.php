<?php

class RequestMapper {
    private static $controllers;

    static function registerRequest($regex, $index, $class){
        self::$controllers[] = array('pattern' => $regex, 'index' => $index, 'cls' => $class);
    }

    static function getRequests(){
        $res = array();
        foreach(self::$controllers as $ctr){
            $res[] = $ctr['index'];
        }
        return $res;
    }

    static function excuteAction($url){
        try{
            $found = false;
            foreach(self::$controllers as $ctr){
                if (preg_match($ctr['pattern'], $url)){
                    $c = new $ctr['cls']();
                    $c->render($url);
                    $found = true;
                    break;
                }
            }
            if (!$found)
                throw new NotFound();
        }catch(HttpException $h){
            $h->render();
        }catch(Exception $e){
            $e = new InternalServerError($e);
            $e->render();
        }
    }
}
