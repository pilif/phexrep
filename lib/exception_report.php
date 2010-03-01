<?php

class ExceptionReport implements ArrayAccess {
    private $data;

    static function getReports($limit = 10){
        $db = Database::getDatabase();
        $q = sprintf(
            "select id, uri, type, ts, message from exception_logging order by ts desc limit %d",
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
        $pr = preg_quote(c()->getValue('report_format', 'app_root'), '#');

        foreach($this->data['error_info']['callstack'] as &$csi){
            if ($pr){
                $csi['base_file'] = preg_replace("#^$pr/?#", '', $csi['file']);
                $csi['file'] = preg_replace("#^$pr/?#", '<R>/', $csi['file']);
                $csi['scm_link'] = $this->getWebSCMLink($csi['base_file'], $csi['line']);
            }
            if ($csi['class']){
                $csi['call'] = sprintf('%s%s%s(%s)',
                    $csi['class'],
                    $csi['type'],
                    $csi['function'],
                    implode(', ', array_map(array($this, 'formatArgument'), $csi['args']))
                );
            }else{
                $csi['call'] = sprintf('%s(%s)',
                    $csi['function'],
                    implode(', ', array_map(array($this, 'formatArgument'), $csi['args']))
                );
            }
        }
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
         throw new NotImplementedException("ExceptionReport is read-only");
    }

    function offsetUnset ($offset){
         throw new NotImplementedException("ExceptionReport is read-only");
    }

    private function formatArgument($p){
        if (!is_string($p) || strlen($p) < 20)
            return $p;
        return substr($p, 0, 8).'...'.substr($p, strlen($p)-8);
    }

    private function getWebSCMLink($file, $line){
        $pr = c()->getValue('report_format', 'web_repo');
        $rv = c()->getValue('report_format', 'revision_field');
        $dr = c()->getValue('report_format', 'default_revision');


        if (empty($pr) || empty($rv)) return '';
        $pr = str_replace('%r', $this->data[$rv] ? rawurlencode($this->data[$rv]) : rawurlencode($dr), $pr);
        $pr = str_replace('%pi', $file, $pr);
        $pr = str_replace('%pu', rawurlencode($file), $pr);
        $pr = str_replace('%l', rawurlencode($line), $pr);
        return $pr;
    }

}
