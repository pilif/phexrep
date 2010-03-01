<?php

class ExceptionReport {

    static function getReports($limit = 10){
        $db = Database::getDatabase();
        $q = sprintf(
            "select id, uri, type, ts from exception_logging order by ts desc limit %d",
            $limit
        );
        return $db->queryArray($q);
    }

}
