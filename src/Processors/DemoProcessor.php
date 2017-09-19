<?php
namespace SRESTO\Processors;
class DemoProcessor implements RequestProcessor{
    public function process($req,$res){
        $res->message("Welcome to SRESTO!!");
    }
}