<?php
/*
	SRESTO(Simple REST Object)
	A very lightweight REST for php
*/
namespace SRESTO;

use SRESTO\Router\MainRouter as Router;
use SRESTO\Request\HTTPRequest as Request;
use SRESTO\Response\RESTResponse as Response;
use SRESTO\MIMEs\ContentNegotiator;

class Application{
    protected static $services=[];
    protected static $config=[];
    public static $processors=[];
    /*protected static $middlewares=[
        'auth'=>\SRESTO\Middleware\Auth::class
    ];
    public static function getMiddlewares(){
        return self::$middlewares;
    }
    public static function registerMiddleware($name,$clazz){
		self::$middlewares[$name]=$clazz;
	}*/
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
        
        foreach($res->getHeaders() as $key=>$val)
            header($key.": ".$val);
        if($res->getStatus()!=200)
            http_response_code($res->getStatus());
        
        $content=ContentNegotiator::processResponse($req,$res);
        //clean output buffer if (needed in config)
        echo $content;
    }
}