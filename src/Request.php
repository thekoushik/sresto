<?php
namespace SRESTO;
class Request{
    public $method;
    public $body;
    public $originalURL;
    public $query;
    public $contentType;
    public $param;
    public function __construct(){
        $this->method=$_SERVER['REQUEST_METHOD'];
        $this->query=$_SERVER['QUERY_STRING'];
        $this->originalURL=(isset($_SERVER['HTTPS'])?"https":"http")."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        if(isset($_SERVER["CONTENT_TYPE"]))
            $this->contentType=$_SERVER['CONTENT_TYPE'];
        else{
            $headers=apache_request_headers();
            $this->contentType=isset($headers['Content-Type'])?$headers['Content-Type']:'text/plain';
        }
        if($this->method==="GET") $this->param=&$_GET;
        else $this->param=array();
        switch($this->contentType){
            case "application/json":
                $this->body = json_decode(file_get_contents('php://input'), true);
                break;
            case "application/x-www-form-urlencoded":
                parse_str(file_get_contents('php://input', false , null, -1 , $_SERVER['CONTENT_LENGTH'] ), $this->body);
                break;
            default:
                $this->body=file_get_contents("php://input");
        }
    }
    public function isJSON(){
        return ($this->contentType==="application/json");
    }
}