<?php
namespace SRESTO\Response;
class HTTPResponse{
    protected $status=200;
    protected $response='';
    protected $headers=[
        'Content-type'=>'application/json',
        'X-Powered-By'=>'SRESTO'
    ];
    protected $flushed=FALSE;
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
    //public function __construct(){}
    public function status($st){
        $this->status=$st;
        return $this;
    }
    /* @TODO:
        Check accept header from request and set content(convert if needed) with content-type response header
    */
    public function send($text){
        $this->response=$text;
        return $this;
    }
    /*public function xml($obj){
        $this->headers['Content-type']='application/xml';
        $this->response=xml_encode($obj);
        return $this;
    }*/
    public function json($obj){
        $this->response=json_encode($obj);
        return $this->header('Content-type','application/json');
    }
    public function location($str){
        return $this->header('Location',$str);
    }
    public function header($key,$val){
        if(is_array($key))
            $this->headers=array_merge($this->headers,$key);
        else
            $this->headers[$key]=$val;
        return $this;
    }
    public function flush(){
        if($this->flushed) return;
        foreach($this->headers as $key=>$val)
            header($key.": ".$val);
        if($this->status!=200)
            http_response_code($this->status);
        echo $this->response;
        $this->flushed=TRUE;
    }
    public function message($msg){
        return $this->json(array('message'=>$msg));
    }
}