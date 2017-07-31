<?php
/*
	SRESTO(Simple REST Object)
	A very lightweight REST for php
*/
namespace SRESTO;
class Router extends REST{
	protected $request=NULL;
	protected $request_type='';
	private $throw_on_unknown_request=FALSE;
	protected $services=array();
	public function __construct($throw_on_unknown_request=TRUE){
		parent::__construct();
		$this->throw_on_unknown_request=$throw_on_unknown_request;
	}
	/*private function pre_process_request(){
		$this->request_type=$_SERVER['REQUEST_METHOD'];
		switch($this->request_type){
			case 'GET': $this->request = &$_GET; break;
			case 'POST': $this->request = &$_POST; break;
			case 'PUT':
			case 'DELETE':
				parse_str(file_get_contents("php://input"),$this->request);
				break;
			default:
				$this->request=NULL;
				$this->request_type='';
				if($this->throw_on_unknown_request)
					throw new Exception("Request type '".$_SERVER['REQUEST_METHOD']."' is not supported", 1);
		}
	}*/
	public function inject($name,$service){
		$this->services[$name]=$service;
	}
	public function execute(){
		//$this->pre_process_request();
		//if($this->request_type=='') return FALSE;
		$req=new Request();
		$url=$req->query;//$_SERVER['QUERY_STRING'];
		$found=FALSE;
		$res=new Response();
		try{
			foreach ($this->router[$req->method] as $pattern => $cb) {
				//preg_match_all($pattern,$url,$out,PREG_PATTERN_ORDER);
				if(strpos($url,$pattern)!==FALSE){
					if(is_callable($cb))
						$cb($req,$res,$this->services);
					else
						$res->send($cb);
					$found=TRUE;
					break;
				}
			}
			if(!$found){
				$cb=$this->router['error']['404'];
				if(is_callable($cb))
					$cb($req,$res,$this->services);
				else
					$res->send($cb);
			}
		}catch(\Exception $e){
			$cb=$this->router['error']['500'];
			if(is_callable($cb))
				$cb($req,$res,$this->services);
			else
				$res->send($cb);
		}finally{
			$res->end();
		}
		return TRUE;
	}
}