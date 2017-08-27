<?php
/*
	SRESTO(Simple REST Object)
	A very lightweight REST for php
*/
namespace SRESTO;

use SRESTO\Router\MainRouter as Router;
use SRESTO\Request\HTTPRequest as Request;
use SRESTO\Response\RESTResponse as Response;

class Application{
    protected static $services=[];
    protected static $config=[];
    protected static $middlewares=[
        'auth'=>\SRESTO\Middleware\Auth::class
    ];
    /*public function __constructor(){
    }*/
    public static function getMiddlewares(){
        return self::$middlewares;
    }
    public static function registerMiddleware($name,$clazz){
		self::$middlewares[$name]=$clazz;
	}
    public static function createRouter($baseurl=''){
        if(is_array($baseurl))
            Router::createFromArray($baseurl);
        else
            return Router::create($baseurl);
    }
    public static function execute(){
        $router=Router::root();
        $req=new Request();
		$res=new Response();
        $router->execute($req,$res);
        $res->flush();
    }
}