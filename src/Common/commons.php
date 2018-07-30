<?php
use SRESTO\Request\HTTPRequest;

if (! function_exists('strToPascalCase')) {
    function strToPascalCase($str){
        return implode("",array_map(function($item){return ucfirst($item);},preg_split("/[^a-zA-Z0-9]/",$str,null,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_NO_EMPTY)));
    }
}
if (! function_exists('strToSnakeCase')) {
    function strToSnakeCase($str){
        return trim(implode("_",array_map(function($item) use($str){return ($item[1]>0)?strtolower($str[$item[1]-1]).$item[0]:$item[0];},preg_split("/([A-Z])/",$str,null,PREG_SPLIT_OFFSET_CAPTURE))),"_");
    }
}
if (! function_exists('abort')) {
    function abort($reason){
        throw new \Exception($reason);
    }
}
if (! function_exists('base')) {
    function base(){
        return (new HTTPRequest())->getBaseURL();
    }
}
if (! function_exists('asset')) {
    function asset($filename){
        return base()."/asset/".$filename;
    }
}