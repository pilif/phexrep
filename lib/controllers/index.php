<?php
RequestMapper::registerRequest('#^/(index)?$#', '/', 'IndexController');


class IndexController extends BaseController{
    function handle(){
        return RequestMapper::getRequests();
    }
}
