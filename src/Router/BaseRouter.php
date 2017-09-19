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
	const SUPPORTED_METHODS=['GET','POST','PUT','DELETE','PATCH','HEAD'];
	//protected $middlewares=[];
	protected $beforeProcessors=[];
	public function __construct($baseurl=''){
		$this->router=[
			'GET'=>[],
			'POST'=>[],
			'PUT'=>[],
			'DELETE'=>[],
			'PATCH'=>[],
			'HEAD'=>[],
			'error'=>[
				'404'=>function($req,$res,$e){$res->setStatus(404)->message("Sorry! Page not found!");},
				'500'=>function($req,$res,$e){
					$res->setStatus(500)->setContent([
						'message'=>"Sorry! Internal server error!",
						'error'=>$e
					]);
				}
			]
		];
		$this->baseURL=$baseurl;
		
		$this->router['GET']['\/']=['proc'=>[['class'=>'SRESTO\Processors\DemoProcessor','fn'=>'process']],'params'=>[],'before'=>[]];
		//$this->refreshMiddlewares();
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
	/*protected function registerMethod($method,$pattern,$cb,$params){
		if($params==NULL) $params=[];
		if($pattern[0]!='/') $pattern='/'.$pattern;
		$pat=$this->createPattern($this->baseURL.$pattern,$params);
		$this->router[$method][$pat]=[
			'fn'=>$cb,
			'params'=>$params,
			//'middlewares'=>$this->getActiveMiddlewares()
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
	}*/
	public function error($code,$cb){//$cb should not be array
		$this->router['error'][strval($code)]=$cb;
	}
	protected function registerMethodFromArray($pattern,$array){
		$params=[];
		if(array_key_exists('param',$array)){
			$params=$array['param'];
			unset($array['param']);
		}
		if($pattern[0]!='/') $pattern='/'.$pattern;
		$pat=$this->createPattern($pattern,$params);
		foreach($array as $key=>$val){
			if($key==='BEFORE'){
				/*foreach(self::SUPPORTED_METHODS as $m){
					if(isset($this->router[$m][$pat])){
						if(is_array($val)){
							foreach($val as $vv){
								if(in_array($vv,$this->router[$m][$pat]['before'],true))
									throw new \Exception("Processor $vv already used in $pattern.");
								else
									array_unshift($this->router[$m][$pat]['before'],$vv);
							}
						}else{
							if(in_array($val,$this->router[$m][$pat]['before'],true))
								throw new \Exception("Processor $val already used in $pattern.");
							else
								array_unshift($this->router[$m][$pat]['before'],$val);
						}
					}else
						$this->router[$m][$pat]=['proc'=>[],'params'=>$params,'before'=>[$val]];
				}*/
				if(!is_array($val))
					throw new \Exception("Syntax error: BEFORE must be an array.");
				if(isset($this->beforeProcessors[$pat]))
					throw new \Exception("Processor $val already used in $pattern.");
				$this->beforeProcessors[$pat]=['params'=>$params,'before'=>$this->parseProcess($val)];
			}else if(isset($this->router[$key][$pat])){
				if(is_array($val)){
					foreach($val as $vv){
						if(in_array($vv,$this->router[$key][$pat]['proc'],true))
							throw new \Exception("Route $pattern already exist.");
						else
							$this->router[$key][$pat]['proc'][]=$vv;
					}
				}else if(in_array($val,$this->router[$key][$pat]['proc'],true))
					throw new \Exception("Route $pattern already exist.");
				else
					$this->router[$key][$pat]['proc'][]=$val;
			}else{
				$this->router[$key][$pat]=['proc'=>$this->parseProcess($val),'params'=>$params,'before'=>[]];
			}
		}
	}
	protected function parseProcess($name){
		if(is_array($name)){
			$fs=[];
			foreach($name as $n){
				/*if($n[0]=='%'){
					$fs[]=$n;
				}else{*/
					$ex=explode('@',$n,2);
					$classPath=$ex[0];
					$method=(count($ex)==2)?$ex[1]:'process';
					//$function = new \ReflectionClass($classPath);
					$fs[]=['class'=>$classPath,'fn'=>$method];
				//}
			}
			return $fs;
		}/*else if($name[0]=='%'){
			return [$name];
		}*/else{
			$ex=explode('@',$name,2);
			$classPath=$ex[0];
			$method=(count($ex)==2)?$ex[1]:'process';
			//$function = new \ReflectionClass($classPath);
			return [['class'=>$classPath,'fn'=>$method]];
		}
	}
	protected function processRoutes(){
		foreach($this->beforeProcessors as $pat=>$val)
			foreach(self::SUPPORTED_METHODS as $m)
				foreach($this->router[$m] as $pat2=>$val2)
					if(strpos($pat2,$pat)===0)
						$this->router[$m][$pat2]['before']+=$val['before'];
	}
	/*private function refreshMiddlewares(){
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
	}*/
}