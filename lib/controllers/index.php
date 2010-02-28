<?php
RequestMapper::registerRequest('#^/(index)?$#', '/', 'IndexController');


class IndexController extends BaseController{
    function handle($url){
        return RequestMapper::getRequests();
    }
}
