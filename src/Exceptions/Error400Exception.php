<?php
namespace SRESTO\Exceptions;
class Error400Exception extends SRESTOException{
    public $code=400;
    public $message="Sorry, resource not found";
    public function __construct($code,$message=null){
        $this->code=$code;
        if($message)
            $this->message=$message;
    }
}