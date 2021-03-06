<?php
/*
	SRESTO(Simple REST Object)
	A very lightweight REST for php
*/
namespace SRESTO;
class Router extends BaseRouter{
	protected $request=NULL;
	protected $request_type='';
	private $throw_on_unknown_request=FALSE;
	protected $services=array();
	private $subRouters=array();
	public function __construct($baseurl=''){
		parent::__construct($baseurl);
	}
	public function &subRouter($baseurl='/'){
		if($baseurl=='') $baseurl='/';
		$this->subRouters[$baseurl]=new Router($this->baseURL.$baseurl);
		return $this->subRouters[$baseurl];
	}
	public function inject($name,$service){
		$this->services[$name]=$service;
	}
	private function matchURL($url,$pattern){
		$result=preg_match_all("/^".$pattern."$/",$url,$out,PREG_PATTERN_ORDER);
		if($result===FALSE) echo $pattern;
		if($result<1) return NULL;
		return $out;
		//return strpos($url,$pattern)===0;
	}
	public function execute($req=NULL,$res=NULL,$flush=TRUE){
		if($req==NULL)
			$req=new Request();
		$url=$req->query;
		$found=FALSE;
		if($res==NULL)
			$res=new RESTResponse();
		$base=$this->baseURL;
		try{
			foreach ($this->subRouters as $pattern => $subRouter) {
				if(strpos($url,$base.$pattern)===0){//if($this->matchURL($url,$base.$pattern)){
					$found=$subRouter->execute($req,$res,FALSE);
				}
			}
			if(!$found){
				foreach ($this->router[$req->method] as $pattern => $cb) {
					$result=$this->matchURL($url,$pattern);
					if($result!=NULL){
						if(is_callable($cb['fn'])){
							$req->param=$this->createParamFromMatch($result,$cb['params']);
							call_user_func($cb['fn'],$req,$res,$this->services);//$cb($req,$res,$this->services);
						}else
							$res->send($cb['fn']);
						$found=TRUE;
						break;
					}
				}
			}
			if(!$found){
				$cb=$this->router['error']['404'];
				if(is_callable($cb))
					call_user_func($cb,$req,$res,$this->services);//$cb($req,$res,$this->services);
				else
					$res->send($cb);
			}
		}catch(\Exception $e){
			$cb=$this->router['error']['500'];
			if(is_callable($cb))
				call_user_func($cb,$req,$res,$this->services);//$cb($req,$res,$this->services);
			else
				$res->send($cb);
		}finally{
			if($flush)
				$res->flush();
		}
		return $found;
	}
}