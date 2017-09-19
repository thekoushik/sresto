<?php
namespace SRESTO\Exceptions;
class SRESTOException extends \Exception{
    public static function sameParameterException($name){
        return new self("Parameter '$name' already been used once.");
    }
    public static function unrecognizedParameterException($name){
        return new self("Unrecognized parameter type '$name'. Make sure to register it before use.");
    }
}