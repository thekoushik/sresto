<?php
namespace SRESTO;
class BaseRouter{
	protected $router;
	protected $baseURL='';
	private $named_regex=[
		'digits'=>"\d+",
		'alphabets'=>"[a-zA-Z]+",
		'alphanumerics'=>"[a-zA-Z0-9]+"
		];
	public function __construct($baseurl=''){
		$this->router=array('GET'=>array(),
							'POST'=>array(),
							'PUT'=>array(),
							'DELETE'=>array(),
							'error'=>array(
								'404'=>function($req,$res,$s){$res->status(404)->send("Sorry! Page not found!");},
								'500'=>function($req,$res,$s){$res->status(500)->send("Sorry! Internal server error!");}
							));
		$this->baseURL=$baseurl;
	}
	private function createPattern($pat,&$param){
		$newparam=[];
		$pat_arr=explode('/',$pat);
		$used_params=[];
		for($i=0;$i<count($pat_arr);$i++){
			if(strpos($pat_arr[$i],":")===0){
				$name=substr($pat_arr[$i],1);
				//[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*
				if(isset($used_params[$name]))
					throw new \Exception("Parameter '$name' already been used once.");
				$used_params[$name]=TRUE;
				if(isset($param[$name])){
					if(!isset($this->named_regex[$param[$name]]))
						throw new \Exception("Unrecognized parameter type '".$param[$name]."'. Make sure to register it before use.");
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
		$newparam=array();
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
	/*public function registerURLRegex($name,$regex=NULL){
		if()
	}*/
	private function registerMethod($method,$pattern,$cb,$params){
		if($params==NULL) $params=[];
		$pat=$this->createPattern($this->baseURL.$pattern,$params);
		$this->router[$method][$pat]=array('fn'=>$cb,'params'=>$params);
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
	public function error($code,$cb){
		$this->router['error'][strval($code)]=$cb;
	}
}