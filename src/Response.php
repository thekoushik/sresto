<?php
namespace SRESTO;
class Response{
    protected $status=200;
    protected $response='';
    protected $headers=array(
        'Content-type'=>'text/plain',
        'X-Powered-By'=>'SRESTO'
        );
    private $flushed=FALSE;
    //public function __construct(){}
    public function status($st){
        $this->status=$st;
        return $this;
    }
    public function send($text){
        $this->response=$text;
        return $this;
    }
    public function json($obj){
        $this->headers['Content-type']='application/json';
        $this->response=json_encode($obj);
        return $this;
    }
    public function location($str){
        $this->headers['Location']=$str;
        return $this;
    }
    public function header($key,$val){
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
}