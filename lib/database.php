<?php

class DatabaseException extends Exception{}

class DatabaseQueryException extends DatabaseException{
    private $_errmsg;
    private $_query;

    function __construct($query, $pglink){
        $this->_query = $query;
        $this->_errmsg = pg_errormessage($pglink);
        parent::__construct("Database-Error {$this->_errmsg} on query $query");
    }

    function getQuery(){
        return $this->_query;
    }

    function getErrmsg(){
        return $this->_errmsg;
    }

}

class DatabaseStreamException extends DatabaseQueryException {
    function __construct($query, $pglink){
        parent::__construct($query, $pglink);

    }

}

class DatabaseConnection{
    private $_link;

    function __construct(){
        $cs = sprintf('dbname=%s user=%s password=%s',
            c()->getValue('database', 'dbname'),
            c()->getValue('database', 'user'),
            c()->getValue('database', 'password')
        );
        $this->_link = pg_connect($cs);
        if (!$this->_link)
            throw new DatabaseException("Cannot connect to database server");
        if (pg_set_client_encoding('utf-8') != 0)
            throw new DatabaseException("Cannot set client encoding");
    }

    public function saveQuery($query){
        $r = @pg_query($this->_link, $query);
        if (!$r){
            throw new DatabaseQueryException($query, $this->_link);
        }
        return $r;
    }

    public function querySingle($query){
        $r = $this->saveQuery($query);
        return pg_fetch_assoc($r);
    }

    public function &queryArray($query){
        $r = $this->saveQuery($query);
        $arr = array();
        while($ra = pg_fetch_assoc($r)){
            $arr[] = $ra;
        }
        return $arr;
    }

    public function putLine($line){
        if (!pg_put_line($this->_link, $line)){
            throw new DatabaseStreamException($line, $this->_link);
        }
    }

    public function closeStream(){
        if (!pg_end_copy($this->_link)){
            throw new DatabaseStreamException("[end of stream]", $this->_link);
        }
    }

}

class Database{
    private static $connection;

   /**
    * @return DatabaseConnection
    * @desc Gets the one and only global database connection
    */
    public static function getDatabase(){
        if (empty(self::$connection)){
            self::$connection = new DatabaseConnection();
        }
        return self::$connection;
    }

}

function nullstr($str){
    return empty($str) || ($str == null) ? 'NULL' : "'".pg_escape_string((string)$str)."'";

}

function sqlstringize($str){
    return "'".pg_escape_string($str)."'";
}

function sqlstringize_array($array){
    $return = array();
    foreach($array as $a){
        $return[] = "'".pg_escape_string($a)."'";
    }
    return $return;
}


?>