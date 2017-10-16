<?php
namespace SRESTO\Exceptions;
class Error500Exception extends SRESTOException{
    public $code=500;
    public $message="Service unavailable";
    public function __construct($code,$message=null){
        $this->code=$code;
        if($message)
            $this->message=$message;
    }
}