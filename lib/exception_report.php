<?php

class ExceptionReport implements ArrayAccess {
    private $data;

    static function getReports($limit = 10){
        $db = Database::getDatabase();
        $q = sprintf(
            "select id, uri, type, ts from exception_logging order by ts desc limit %d",
            $limit
        );
        return $db->queryArray($q);
    }

    function __construct($id){
        $db = Database::getDatabase();
        $this->data = $db->querySingle(sprintf(
            "select * from exception_logging where id = %d",
            $id
        ));
        if (!$this->data){
            throw new NotFound();
        }
        $this->data['error_info'] = unserialize($this->data['error_info']);
    }

    function __get($v){
        return $this->data[$v];
    }

    /* we'll never know why json_encode apparently can't work with
       something that implements ArrayAccess */
    function asArray(){
        return $this->data;
    }


    // ArrayAccess implementation methods

    function offsetExists ($offset){
        return isset($this->data[$offset]);
    }

    function offsetGet ($offset){
         return $this->data[$offset];
    }

    function offsetSet ($offset, $value){
         throw NotImplementedException("ExceptionReport is read-only");
    }

    function offsetUnset ($offset){
         throw NotImplementedException("ExceptionReport is read-only");
    }

}
