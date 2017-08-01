<?php
namespace SRESTO;
class REST{
	protected $router;
	public function __construct(){
		$this->router=array('GET'=>array(),
							'POST'=>array(),
							'PUT'=>array(),
							'DELETE'=>array(),
							'error'=>array(
								'404'=>function($req,$res,$s){$res->status(404)->send("Sorry! Page not found!");},
								'500'=>function($req,$res,$s){$res->status(404)->send("Sorry! Internal server error!");}
							));
	}
	public function get($pattern,$cb){
		$this->router['GET'][$pattern]=$cb;
	}
	public function post($pattern,$cb){
		$this->router['POST'][$pattern]=$cb;
	}
	public function put($pattern,$cb){
		$this->router['PUT'][$pattern]=$cb;
	}
	public function delete($pattern,$cb){
		$this->router['DELETE'][$pattern]=$cb;
	}
	public function error($code,$cb){
		$this->router['error'][strval($code)]=$cb;
	}
}