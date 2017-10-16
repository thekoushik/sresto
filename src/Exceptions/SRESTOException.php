<?php
namespace SRESTO\Exceptions;
class SRESTOException extends \Exception{
    public static function sameParameterException($name){
        return new self("Parameter '$name' already been used once.");
    }
    public static function unrecognizedParameterException($name){
        return new self("Unrecognized parameter type '$name'. Make sure to register it before use.");
    }
    public static function classNotFoundException($name){
        return new self("Class '$name' does not exist.");
    }
    public static function methodReDefineException($method){
        return new self("Method '$method' is already defined in this path.");
    }
    public static function pathReDefineException($path){
        return new self("Path '$path' is already defined.");
    }
    public static function methodNotDefinedException($path){
        return new self("No method has been defined in path '$path'.");
    }
    public static function multipleBootException(){
        return new self("Trying to boot application more than once.");
    }
    public static function invalidValidatorFunctionException($name){
        return new self("Unsupported validator function '$name'");
    }
    /*public static function cannotReAssignDTOMetaDataException(){
        return new self("Cannot re-assign DTOMetaData configuration.");
    }*/
}