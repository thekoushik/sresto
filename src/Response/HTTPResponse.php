<?php
namespace SRESTO\Response;
use SRESTO\Response\ResponseInterface;
class HTTPResponse implements ResponseInterface{
    protected $status=200;
    protected $response='';
    protected $headers=[
        'Content-type'=>'application/json',
        'X-Powered-By'=>'SRESTO'
    ];
    public static $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    ];
    public function __construct(){
    }
    public function status($st){
        $this->status=$st;
        return $this;
    }
    public function location($str){
        return $this->header('Location',$str);
    }
    public function message($msg,$name=null){
        if(!$name) $name='message';
        $this->response=[$name=>$msg];
        return $this;
    }
    public function getHeaders(){
        return $this->headers;
    }
    public function setHeaders($array){
        $this->headers=array_merge($this->headers,$array);
        return $this;
    }
    public function getHeader($name){
        return $this->headers[$name];
    }
    public function setHeader($name,$value){
        $this->headers[$name]=$value;
        return $this;
    }
    public function hasHeader($name){
        return (bool)isset($this->headers[$name]);
    }
    public function removeHeader($name){
        if(isset($this->headers[$name]))
            unset($this->headers[$name]);
    }
    public function getContent(){
        return $this->response;
    }
    public function setContent($val){
        $this->response=$val;
        return $this;
    }
    public function getStatus(){
        return $this->status;
    }
    public function setStatus($val){
        if(!array_key_exists($val,self::$statusTexts))
            throw new \Exception("Invalid status ".$val);
         $this->status=$val;
         return $this;
    }
    public function abort($status=500,$reason=null){
        $this->setStatus($status);
        throw new \Exception("Abort: ".$reason);
    }
}