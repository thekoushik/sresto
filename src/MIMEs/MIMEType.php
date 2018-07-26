<?php
namespace SRESTO\MIMEs;
class MIMEType{
    const TEXT=1;
    const JSON = 0;
    const XML = 2;
    const FORM=3;

    const TYPES=[
        'application/json',
        'text/plain',
        'application/xml',
        'application/x-www-form-urlencoded'
    ];
    private $current;

    public function __construct($str){
        $this->current=self::fromString($str);
    }
    public function getType(){
        return $this->current;
    }
    public static function fromString($str){
        if($str==="*/*") return self::JSON;/////json for all
        foreach (self::TYPES as $index=>$value)
            if( strpos($str,$value)!==false )
                return $index;
        return self::TEXT;
    }
    public function parseFrom($str){
        switch($this->current){
            case 1:
                return $str;
            case 0:
                return self::parseFromJSON($str);
            case 2:
                return self::parseFromXML($str);
            case 3:
                parse_str($str,$data);
                return $data;
            default:
                return $str;
        }
    }
    public function parseTo($str){
        switch($this->current){
            case 0:
                return self::parseToJSON($str);
            case 2:
                return self::parseToXML($str);
            default:
                return $str;
        }
    }

    public static function parseFromXML($xmlString){
        $backup = libxml_disable_entity_loader(true);
        $backup_errors = libxml_use_internal_errors(true);
        $body = simplexml_load_string($xmlString);
        libxml_disable_entity_loader($backup);
        libxml_clear_errors();
        libxml_use_internal_errors($backup_errors);
        if ($body === false)
            $body=null;
        return $body;
    }
    public static function parseToXML($obj){
        return "";
    }
    public static function parseFromJSON($jsonString){
        return json_decode($jsonString,true);
    }
    public static function parseToJSON($obj){
        return json_encode($obj);
    }
}