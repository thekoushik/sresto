<?php
namespace SRESTO;
class Response{
    protected $status=200;
    protected $response='';
    protected $headers=array(
        'Content-type'=>'text/plain',
        'X-Powered-By'=>'SRESTO'
        );
    protected $flushed=FALSE;
    //public function __construct(){}
    public function status($st){
        $this->status=$st;
        return $this;
    }
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
    public function message($msg){
        $this->json(array('message'=>$msg));
        return $this;
    }
    protected function isObject($val){
        return is_array($val)?TRUE:(is_scalar($val)?FALSE:TRUE);
    }
}