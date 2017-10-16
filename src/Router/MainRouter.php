<?php
namespace SRESTO\Router;
use SRESTO\Application;
use SRESTO\Tools\Logger;
use SRESTO\Common\MetaData;
use SRESTO\Common\Annotations\RequestMapping;
use SRESTO\Common\Annotations\RequestBody;
use SRESTO\Exceptions\Error400Exception;
use SRESTO\Exceptions\Error500Exception;
use SRESTO\Exceptions\SRESTOException;
use SRESTO\Utils\CoreUtil;

class MainRouter extends BaseRouter{
	//private $branches=[];
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
		//$this->processRoutes();
		//print_r($this->router);die();
		$url=$req->getPath();
		try{
			$found=false;
			foreach ($this->router[$req->getMethod()] as $pattern => $cb) {
				$result=$this->matchURL($url,$pattern);
				if($result!=NULL){
					$req->setParam($this->createParamFromMatch($result,$cb['params']));
					$req->setBody($this->parseBody($cb['body'],$req->getBody()));
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
			if(!$found)
				throw new Error400Exception(404);
		}catch(Error400Exception $e){
			$res->setStatus($e->code)->message($e->message);
		}catch(Error500Exception $e){
			$res->setStatus($e->code)->setContent([
				'message'=>$e->message,
				'error'=>$e
			]);
		}catch(\Exception $e){
			Logger::error($e->getTraceAsString());
			$res->setStatus(500)->setContent([
				'message'=>"Sorry! Internal server error!",
				'error'=>$e
			]);
		}
	}
	public static function createFromAnnotaion($classes){
		self::root();
		foreach($classes as $clazz){
			$meta=new MetaData($clazz);
			if($meta->reflection->isAbstract() || $meta->reflection->isInterface())
				continue;
			$reqMap=$meta->getClassAnnotation(RequestMapping::class);
			if($reqMap==null)
				continue;
			$path=($reqMap->path=="")?"/":$reqMap->path;
			$methods=$meta->reflection->getMethods();
			foreach($methods as $method){
				$reqMapMethod=$meta->getMethodAnnotation($method->name,RequestMapping::class);
				if($reqMapMethod){
					$reqMethod=$reqMap->method;
					if(empty($reqMapMethod->method)){
						if(empty($reqMethod))
							throw SRESTOException::methodNotDefinedException($path);
					}else if(!empty($reqMap->method))
						throw SRESTOException::methodReDefineException($reqMap->method);
					else
						$reqMethod=$reqMapMethod->method;
					$uri=CoreUtil::concatURLs([$path,$reqMapMethod->path]);
					$route=[$reqMethod=>$clazz."@".$method->name];
					$body=$meta->getMethodAnnotation($method->name,RequestBody::class);
					if($body)
						$route['body']=$body->className;
					self::$instance->registerMethodFromArray($uri,$route);
				}
			}
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