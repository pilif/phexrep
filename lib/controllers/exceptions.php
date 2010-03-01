<?php
RequestMapper::registerRequest('#^/exceptions#', '/exceptions', 'ExceptionController');


class ExceptionController extends BaseController{

    function handle($url){
        list($null, $name, $exid, $data) = explode('/', $url);
        if ($data){
            throw new BadRequest('Syntax: /exceptions[/id]');
        }
        if ($exid){
            return $this->getException($exid);
        }else{
            return $this->getExceptionList();
        }
    }

    private function getException($exid){
        $ex = new ExceptionReport($exid);

        return $ex->asArray();

    }

    private function getExceptionList(){
        $limit = isset($_GET['pagesize']) ? $_GET['pagesize'] : 10;
        $reps = ExceptionReport::getReports($limit);
        foreach($reps as &$rep){
            $rep['href'] = '/exceptions/'.$rep['id'];
        }
        return $reps;
    }

}