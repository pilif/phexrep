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
        $settings = array();
        foreach(c()->getValue('database') as $k => $v)
            $settings[] = sprintf('%s=%s', $k, $v);

        $cs = 'pgsql:'.implode(';', $settings);
        $this->_link = new PDO($cs);
        if (!$this->_link)
            throw new DatabaseException("Cannot connect to database server");

        $this->_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->_link->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->_link->exec("set client_encoding to 'latin1'");
    }

    /**
     * @return PDOStatement
     */
    public function saveQuery($query){
        $r = $this->_link->prepare($query);
        $r->execute();
        return $r;
    }

    public function querySingle($query){
        $r = $this->saveQuery($query);
        return $r->fetch();
    }

    public function &queryArray($query){
        $r = $this->saveQuery($query);
        return $r->fetchAll();
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
