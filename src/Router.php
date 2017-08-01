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
	public function inject($name,$service){
		$this->services[$name]=$service;
	}
	public function execute(){
		$req=new Request();
		$url=$req->query;
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
			$res->flush();
		}
		return TRUE;
	}
}