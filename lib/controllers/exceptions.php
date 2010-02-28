<?php
RequestMapper::registerRequest('#^/exceptions#', '/exceptions', 'ExceptionController');


class ExceptionController extends BaseController{

    function handle($url){
        list($name, $exid, $data) = explode('/', $url);
        if ($data){
            throw new BadRequest('Syntax: /exceptions[/id]');
        }
        if ($extid){
            return $this->getException($exid);
        }else{
            return $this->getExceptionList();
        }
    }

    private function getException($exid){

    }

    private function getExceptionList(){
        $limit = isset($_GET['pagesize']) ? $_GET['pagesize'] : 10;

        $db = Database::getDatabase();
        $q = sprintf(
            "select '/exceptions/'||id as href, id, uri, type, ts from exception_logging order by ts desc limit %d",
            $limit
        );

        return $db->queryArray($q);
    }

}