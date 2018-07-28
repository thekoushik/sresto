<?php
namespace SRESTO\Router;

use SRESTO\Application;
use SRESTO\Exceptions\SRESTOException;
use SRESTO\Utils\CoreUtil;
use SRESTO\DTO\Normalizer;
use SRESTO\Configuration;
use SRESTO\Common\MetaData;
use SRESTO\Common\Annotations\RequestMapping;
use SRESTO\Common\Annotations\RequestBody;

class BaseRouter{
	/**
	 * Names for named routes
	 *
	 * @var array
	 * @todo make it simple
	 */
	protected $names=[
		//'hello'=>['GET','/hello']
	];
	protected $router;
	protected $baseURL='';
	protected $named_regex=[
		'digits'=>"\d+",
		'alphabets'=>"[a-zA-Z]+",
		'alphanumerics'=>"[a-zA-Z0-9]+"
	];
	const SUPPORTED_METHODS=['GET','POST','PUT','DELETE','PATCH','HEAD','OPTIONS'];
	protected $beforeProcessors=[];
	protected $afterProcessors=[];

	private static $instance=null;

	public function __construct($baseurl=''){
		$this->router=[
			'GET'=>[],
			'POST'=>[],
			'PUT'=>[],
			'DELETE'=>[],
			'PATCH'=>[],
			'HEAD'=>[],
			'OPTIONS'=>[]
		];
		$this->baseURL=$baseurl;
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
					throw SRESTOException::sameParameterException("Parameter '$name' already been used once.");
				$used_params[$name]=TRUE;
				if(isset($param[$name])){
					if(!isset($this->named_regex[$param[$name]]))
						throw SRESTOException::unrecognizedParameterException("Unrecognized parameter type '".$param[$name]."'. Make sure to register it before use.");
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
	protected function registerMethodFromArray($pattern,$array){
		$params=[];
		if(array_key_exists('param',$array)){
			$params=$array['param'];
			unset($array['param']);
		}
		$body=null;
		if(array_key_exists('body',$array)){
			$body=$array['body'];
			unset($array['body']);
			if(!class_exists($body,false)){
				$temp=explode("\\",$body);
				$temp=Configuration::get("resource_package")."\\".end($temp);//broken into two statements because it throws warning if put into single statement
				if(class_exists($temp))
					$body=$temp;
				else
					throw SRESTOException::classNotFoundException($body);
			}
		}
		if(empty($pattern)) $pattern="/";
		if($pattern[0]!='/') $pattern='/'.$pattern;
		$pat=$this->createPattern($pattern,$params);
		foreach($array as $key=>$val){
			if($key==='BEFORE'){
				if(!is_array($val))
					throw new \Exception("Syntax error: BEFORE must be an array.");
				if(isset($this->beforeProcessors[$pat]))
					throw new \Exception("Processor $val already used in $pattern.");
				$this->beforeProcessors[$pat]=['params'=>$params,'before'=>$this->parseProcess($val),'body'=>$body];
			}else if($key==='AFTER'){
				if(!is_array($val))
					throw new \Exception("Syntax error: AFTER must be an array.");
				if(isset($this->afterProcessors[$pat]))
					throw new \Exception("Processor $val already used in $pattern.");
				$this->afterProcessors[$pat]=['params'=>$params,'after'=>$this->parseProcess($val),'body'=>$body];
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
				$this->router[$key][$pat]=['proc'=>$this->parseProcess($val),'params'=>$params,'before'=>[],'after'=>[],'body'=>$body];
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
					$fs[]=['class'=>$this->getProcessorClassName($classPath),'fn'=>$method];
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
			return [['class'=>$this->getProcessorClassName($classPath),'fn'=>$method]];
		}
	}
	protected function getProcessorClassName($processor){
		if(class_exists($processor))
			return $processor;
		else{
			$temp=explode("\\",$processor);
			$temp=Configuration::get("processor_package")."\\".end($temp);
			if(class_exists($temp))
				return $temp;
			throw SRESTOException::classNotFoundException($processor);
		}
	}
	public function processRoutes(){
		foreach($this->beforeProcessors as $pat=>$val)
			foreach(self::SUPPORTED_METHODS as $m)
				foreach($this->router[$m] as $pat2=>$val2)
					if(strpos($pat2,$pat)===0)
						$this->router[$m][$pat2]['before']+=$val['before'];
		return $this->router;
	}
	public function createCacheFromRoutes(){
		return base64_encode(serialize($this->router));
	}
	public function createRoutesFromCache($cache){
		return $this->router=unserialize(base64_decode($cache));
	}
	public static function root(){
		if(self::$instance==null)
			self::$instance=new self();
		return self::$instance;
	}
	public static function create($baseurl=''){
		self::$instance=new self($baseurl);
		return self::$instance;
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
		return self::$instance;
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
							$reqMethod='GET';//throw SRESTOException::methodNotDefinedException($path);
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

	public static function createOptionsFromRoutes($routes){
		$paths=[];
		foreach(self::SUPPORTED_METHODS as $m){
			foreach($routes[$m] as $pat => $cb){
				if(isset($paths[$pat])){
					array_push($paths[$pat],$m);
				}else
					$paths[$pat]=[$m];
			}
		}
		foreach($paths as $p=>$d)
			$paths[$p]=implode(',',$paths[$p]);
		return $paths;
	}
}