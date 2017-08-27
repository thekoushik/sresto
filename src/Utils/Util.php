<?php
namespace SRESTO\Utils;
class Util{
    public static function isObject($obj){
        return is_array($val)?TRUE:(is_scalar($val)?FALSE:TRUE);
    }
}