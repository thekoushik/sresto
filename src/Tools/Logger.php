<?php
namespace SRESTO\Tools;

class Logger{
    private static $path=null;
    const ERRORS=[
        E_ERROR=>'E_ERROR',
        E_WARNING=>'E_WARNING',
        E_PARSE=>'E_PARSE',
        E_NOTICE=>'E_NOTICE',
        E_CORE_ERROR=>'E_CORE_ERROR',
        E_CORE_WARNING=>'E_CORE_WARNING',
        E_COMPILE_ERROR=>'E_COMPILE_ERROR',
        E_COMPILE_WARNING=>'E_COMPILE_WARNING',
        E_USER_ERROR=>'E_USER_ERROR',
        E_USER_WARNING=>'E_USER_WARNING',
        E_USER_NOTICE=>'E_USER_NOTICE',
        E_STRICT=>'E_STRICT',
        E_RECOVERABLE_ERROR=>'E_RECOVERABLE_ERROR',
        E_DEPRECATED=>'E_DEPRECATED',
        E_USER_DEPRECATED=>'E_USER_DEPRECATED'
    ];
    public static function create($path){
        self::$path=$path;
    }
    public static function info($str){
        self::log("local.INFO",$str);
    }
    public static function error($str){
        self::log("local.ERROR",$str);
    }
    public static function fatal($errno,$errfile,$errline,$errstr){
        self::log("FATAL.ERROR","Error ".self::ERRORS[$errno]."(".$errno.") in file '".$errfile."' at line ".$errline." : ".$errstr);
    }
    public static function warning($str){
        self::log("local.WARNING",$str);
    }
    public static function debug($str){
        self::log("local.DEBUG",$str);
    }
    public static function log($type,$str){
        $text="[".date("Y-m-d H:i:s")."] ".$type." : ".$str."\r\n";
        file_put_contents(self::$path, $text, FILE_APPEND);
    }
}