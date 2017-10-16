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
use SRESTO\Exceptions\SRESTOException;
use SRESTO\Configuration;
use SRESTO\Utils\CoreUtil;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class Application{
    private static $booted=false;
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
    private static function createRouter($baseurl=''){
        if(is_array($baseurl))
            Router::createFromArray($baseurl);
        else
            return Router::create($baseurl);
    }
    public static function boot($environment,$settings,$processors,$services){
        if(self::$booted) throw SRESTOException::multipleBootException();
        self::$booted=true;

        $container = new ContainerBuilder();

        $isDevMode = true;
        //$config = Setup::createYAMLMetadataConfiguration([Configuration::get("resource_yaml_path")], $isDevMode);
        $config = Setup::createAnnotationMetadataConfiguration([Configuration::get("resource_package_path")],$isDevMode);

        //$config->setEntityNamespaces(['APIBundle' => Configuration::get("resource_package")]);
        $entityManager = EntityManager::create($settings['db'], $config);

        foreach($services as $service)
            $container
                ->register($service,$service)
                ->addArgument($entityManager);//->addArgument('%us.con%');
        self::$processors=[];
        foreach($processors as $processor){
            $proc=explode('\\',$processor);
            $proc=end($proc);
            $procArgs=$container->register($proc, $processor);

            $r=new \ReflectionClass($processor);
            $con=$r->getConstructor();
            if($con!=null){
                $params=$con->getParameters();
                foreach($params as $param){
                    $paramClassName=$param->getClass()->name;
                    $procArgs->addArgument(new Reference($paramClassName));
                }
            }
            self::$processors[$processor] = $container->get($proc);
        }

        try{
            $routerCacheFile=Configuration::get("cache_path").DIRECTORY_SEPARATOR."router";
            if(file_exists($routerCacheFile)){
                $routerCache=file_get_contents($routerCacheFile);
                Router::root()->createRoutesFromCache($routerCache);
            }else{
                if($environment['ROUTER_FROM_ANNOTATION']){
                    Router::createFromAnnotaion($processors);
                }else{
                    self::createRouter(CoreUtil::parseYML(__DIR__.'/config/router.yml'));
                }
                $router=Router::root();
                $router->processRoutes();
                $routerCache=$router->createCacheFromRoutes();
                file_put_contents(Configuration::get("cache_path").DIRECTORY_SEPARATOR.".router",$routerCache);
            }
        }catch(\Exception $e){
            die("Error: ".$e->getMessage());
        }

        return $entityManager;
    }
    public static function execute(){
        $router=Router::root();
        $req=new Request();
        $res=new Response();
        
        $maintenance=null;
        $maintenanceFile=Configuration::get("config_path").DIRECTORY_SEPARATOR."maintenance.txt";
        if(file_exists($maintenanceFile))
            $maintenance=file_get_contents($maintenanceFile);
        if(!empty($maintenance)){
            $res->setStatus(503)->message($maintenance);
        }else{
            $router->execute($req,$res);
        }
        foreach($res->getHeaders() as $key=>$val)
            header($key.": ".$val);
        if($res->getStatus()!=200)
            http_response_code($res->getStatus());
        
        $content=ContentNegotiator::processResponse($req,$res);
        //clean output buffer if (needed in config)
        echo $content;
    }
}