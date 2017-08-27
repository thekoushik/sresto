<?php
namespace SRESTO\Exception;
class ParameterException extends Exception{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Exception $previous = null) {
        //
        parent::__construct($message, $code, $previous);
    }
}