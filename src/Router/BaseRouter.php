<?php
namespace SRESTO\Router;

use SRESTO\Application;
use SRESTO\Exception\ParameterException;

class BaseRouter{
	protected $router;
	protected $baseURL='';
	protected $named_regex=[
		'digits'=>"\d+",
		'alphabets'=>"[a-zA-Z]+",
		'alphanumerics'=>"[a-zA-Z0-9]+"
	];
	protected $middlewares=[];
	public function __construct($baseurl=''){
		$this->router=[
			'GET'=>[],
			'POST'=>[],
			'PUT'=>[],
			'DELETE'=>[],
			'PATCH'=>[],
			'HEAD'=>[],
			'error'=>[
				'404'=>function($req,$res){$res->status(404)->send("Sorry! Page not found!");},
				'500'=>function($req,$res){$res->status(500)->send("Sorry! Internal server error!");}
			]
		];
		$this->baseURL=$baseurl;

		$this->refreshMiddlewares();
	}
	protected function createPattern($pat,&$param){
		$newparam=[];
		$pat_arr=explode('/',$pat);
		$used_params=[];
		for($i=0;$i<count($pat_arr);$i++){
			if(strpos($pat_arr[$i],":")===0){
				$name=substr($pat_arr[$i],1);
				//[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*
				if(isset($used_params[$name]))
					throw new ParameterException("Parameter '$name' already been used once.");
				$used_params[$name]=TRUE;
				if(isset($param[$name])){
					if(!isset($this->named_regex[$param[$name]]))
						throw new ParameterException("Unrecognized parameter type '".$param[$name]."'. Make sure to register it before use.");
				}else{//default digits
					$param[$name]='alphanumerics';
				}
				$newparam[$i]="(?<".$name.">".$this->named_regex[$param[$name]].")";
			}else
				$newparam[$i]=$pat_arr[$i];
		}
		$newparam=str_replace('/', '\/', implode("/",$newparam));
		return $newparam;
	}
	protected function createParamFromMatch($matches,$param){
		$newparam=[];
		if($param==NULL) return $newparam;
		foreach($param as $key => $value){
			if(isset($matches[$key])){
				$newparam[$key]=$matches[$key][0];
			}else{
				$newparam[$key]=NULL;
			}
		}
		return $newparam;
	}
	public function registerURLRegex($name,$regex){
		if(strlen($name)<1)
			throw new \Exception("name must not be blank.");
		if(strlen($regex)<1)
			throw new \Exception("regex must not be blank.");
		$this->named_regex[$name]=$regex;
	}
	protected function registerMethod($method,$pattern,$cb,$params){
		if($params==NULL) $params=[];
		$pat=$this->createPattern($this->baseURL.$pattern,$params);
		$this->router[$method][$pat]=[
			'fn'=>$cb,
			'params'=>$params,
			'middlewares'=>$this->getActiveMiddlewares()
		];
	}
	public function get($pattern,$cb,$params=NULL){
		$this->registerMethod('GET',$pattern,$cb,$params);
	}
	public function post($pattern,$cb,$params=NULL){
		$this->registerMethod('POST',$pattern,$cb,$params);
	}
	public function put($pattern,$cb,$params=NULL){
		$this->registerMethod('PUT',$pattern,$cb,$params);
	}
	public function delete($pattern,$cb,$params=NULL){
		$this->registerMethod('DELETE',$pattern,$cb,$params);
	}
	public function patch($pattern,$cb,$params=NULL){
		$this->registerMethod('PATCH',$pattern,$cb,$params);
	}
	public function head($pattern,$cb,$params=NULL){
		$this->registerMethod('HEAD',$pattern,$cb,$params);
	}
	public function error($code,$cb){
		$this->router['error'][strval($code)]=$cb;
	}
	private function refreshMiddlewares(){
		$m=Application::getMiddlewares();
		foreach($m as $key=>$v)
			if(!isset($this->middlewares[$key]))
				$this->middlewares[$key]=['active'=>0,'class'=>$v];
	}
	protected function getActiveMiddlewares(){
		$middleware=[];
		foreach($this->middlewares as $key=>$m){
			if($m['active']!=0)
				$middleware[]=$m['class'];
			if($m['active']===1)
				$this->middlewares[$key]['active']=0; 
		}
		return $middleware;
	}
	public function with($middleware){
		if(is_array($middleware)){
			foreach($middleware as $m)
				if($this->middlewares[$m]['active']===0)
					$this->middlewares[$m]['active']=1;
		}else if(strlen($middleware)>0){
			if($this->middlewares[$middleware]['active']===0)
				$this->middlewares[$middleware]['active']=1;
		}
		return $this;
	}
}