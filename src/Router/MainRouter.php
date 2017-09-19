<?php
namespace SRESTO\Router;
use SRESTO\Application;
class MainRouter extends BaseRouter{
	private $throw_on_unknown_request=FALSE;
	private $branches=[];
	private static $instance=null;
	public function __construct($baseurl=''){
		parent::__construct($baseurl);
	}
	/* static section */
	public static function root(){
		if(self::$instance==null)
			self::$instance=new MainRouter();
		return self::$instance;
	}
	/*
		@TODO:
		1. If baseurl exist then merge($merge argument is true,default false) url and return appropriate router
	*/
	public static function create($baseurl=''){
		if(self::$instance==null){
			self::$instance=new MainRouter($baseurl);
			return self::$instance;
		}else
			return self::$instance->createBranch($baseurl);
	}
	public static function createFromArray($array){
		self::root();
		foreach($array as $path=>$item){
			if(is_array($item)){
				if(count($item)==0)
					throw new \Exception("Syntax Error");
				self::$instance->registerMethodFromArray($path,$item);
			}else
				throw new \Exception("Syntax Error");
		}
	}
	/* end of static section */
	/*public function &createBranch($baseurl=''){
		if($baseurl=='') $baseurl='/';
		if($baseurl[0]!='/') $baseurl='/'.$baseurl;
		if(isset($this->branches[$baseurl]))
			throw new \Exception("Route already registered at $baseurl");
		$this->branches[$baseurl]=[
			'router'=>new MainRouter($this->baseURL.$baseurl),
			//'middlewares'=>$this->getActiveMiddlewares()
		];
		return $this->branches[$baseurl]['router'];
	}
	private function matchURL2($url,$pattern){
		$result=preg_match_all("/^".$pattern."$/",$url,$out,PREG_PATTERN_ORDER);
		if($result===FALSE) echo $pattern;
		if($result<1) return NULL;
		return $out;
		//return strpos($url,$pattern)===0;
	}*/
	private function matchURL($url,$pattern){
		$result=preg_match_all("/^".$pattern."$/",$url,$out,PREG_PATTERN_ORDER);
		if($result===FALSE) echo $pattern;
		if($result<1) return NULL;
		return $out;
		//return strpos($url,$pattern)===0;
	}
	public function execute($req,$res){
		$this->processRoutes();
		//print_r($this->router);die();
		$url=$req->getPath();
		try{
			$found=false;
			foreach ($this->router[$req->getMethod()] as $pattern => $cb) {
				$result=$this->matchURL($url,$pattern);
				if($result!=NULL){
					$req->setParam($this->createParamFromMatch($result,$cb['params']));
					$stopNow=false;
					foreach($cb['before'] as $procs){
						$o=Application::$processors[$procs['class']];
						if(!$o->$procs['fn']($req,$res)){
							$stopNow=true; break;
						}
					}
					if($stopNow){
						$found=TRUE;
						break;
					}
					foreach($cb['proc'] as $procs){
						$o=Application::$processors[$procs['class']];
						if(!$o->$procs['fn']($req,$res))
							break;
					}
					$found=TRUE;
					break;
				}
			}
			if(!$found){
				$cb=$this->router['error']['404'];
				if(is_callable($cb))
					call_user_func($cb,$req,$res,404);//$cb($req,$res,$this->services);
				else
					$res->setContent($cb);
			}
		}catch(\Exception $e){
			$cb=$this->router['error']['500'];
			if(is_callable($cb))
				call_user_func($cb,$req,$res,$e->getMessage());//$cb($req,$res,$this->services);
			else
				$res->setContent($cb);
		}
	}
	/*public function execute2($req,$res){
		$url=$req->path;
		//$base=$this->baseURL;
		try{
			foreach ($this->branches as $pattern => $branch) {
				if(strpos($url,$base.$pattern)===0){//if($this->matchURL($url,$base.$pattern)){
					foreach($branch['middlewares'] as $m){
						$x=new $m;
						if(!$x->run($req,$res))
							return true;
					}
					$found=$branch['router']->execute($req,$res);
				}
			}
			if(!$found){
				foreach ($this->router[$req->method] as $pattern => $cb) {
					$result=$this->matchURL($url,$pattern);
					if($result!=NULL){
						$req->param=$this->createParamFromMatch($result,$cb['params']);
						foreach($cb['middlewares'] as $m){
							$x=new $m;
							if(!$x->run($req,$res))
								return true;
						}
						if(is_callable($cb['fn']))
							call_user_func($cb['fn'],$req,$res,$this->services);//$cb($req,$res,$this->services);
						else
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
		}
		return $found;
	}*/
}