<?php
namespace SRESTO\Request;

class HTTPRequest{
    public $method;
    public $body;
    public $originalURL;
    public $query;
    public $path;
    public $contentType;
    public $contentLength;
    public $param;
    public $accept;
    public $headers;
    
    public function __construct(){
        $this->fetchHeaders();
        $this->param=[];
        switch($this->contentType){
            case "application/json":
                $this->body = json_decode(file_get_contents('php://input'), true);
                break;
            case "application/x-www-form-urlencoded":
                if($this->contentLength<0)
                    parse_str(file_get_contents('php://input'), $this->body);
                else
                    parse_str(file_get_contents('php://input', false , null, -1 , $this->contentLength ), $this->body);
                break;
            default:
                $this->body=file_get_contents("php://input");
        }
    }
    private function fetchHeaders(){
        $this->method=$_SERVER['REQUEST_METHOD'];
        $url=$_SERVER['QUERY_STRING'];
        //parse url for query parameters
        $i=strpos($url,"?");
        if($i===false)
            $this->query=[];
        else{
            parse_str(substr($url,$i+1),$this->query);
            $url=substr($url,0,$i);
        }
        $this->path=$url;
        
        $this->originalURL=(isset($_SERVER['HTTPS'])?"https":"http")."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $headers=apache_request_headers();
        $this->headers=$headers;
        if(isset($_SERVER["CONTENT_TYPE"]))
            $this->contentType=$_SERVER['CONTENT_TYPE'];
        else
            $this->contentType=isset($headers['Content-Type'])?$headers['Content-Type']:'text/plain';
        if(isset($_SERVER["CONTENT_LENGTH"]))
            $this->contentLength=intval($_SERVER['CONTENT_LENGTH']);
        else 
            $this->contentLength=isset($headers['Content-Length'])?intval($headers['Content-Length']):-1;
        $this->accept=isset($headers['Accept'])?$headers['Accept']:'text/plain';
    }
    public function isJSON(){
        return ($this->contentType==="application/json");
    }
}