<?php
/*
	SRESTO(Simple REST Object)
	A very lightweight REST for php
*/
namespace SRESTO;

use SRESTO\Router\BaseRouter as Router;
use SRESTO\Request\HTTPRequest as Request;
use SRESTO\Response\RESTResponse as Response;
use SRESTO\MIMEs\ContentNegotiator;
use SRESTO\Exceptions\SRESTOException;
use SRESTO\Exceptions\Error400Exception;
use SRESTO\Exceptions\Error500Exception;
use SRESTO\Configuration;
use SRESTO\Utils\CoreUtil;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class Application{
    private static $booted=false;
    private static $processors=[];
    private static $routes=null;
    private static function createRouter($baseurl=''){
        if(is_array($baseurl))
            return Router::createFromArray($baseurl);
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

        //$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ApcuCache());

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
                self::$routes=Router::root()->createRoutesFromCache($routerCache);
            }else{
                if($environment['ROUTER_FROM_ANNOTATION']){
                    Router::createFromAnnotaion($processors);
                }else{
                    self::createRouter(CoreUtil::parseYML(Configuration::get("config_path").DIRECTORY_SEPARATOR.'router.yml'));
                }
                self::$routes=Router::root()->processRoutes();
            }
        }catch(\Exception $e){
            die("Error: ".$e->getMessage());
        }
        return $entityManager;
    }
    public static function execute(){
        //$router=Router::root();
        $req=new Request();
        $res=new Response();
        
        $maintenance=null;
        $maintenanceFile=Configuration::get("config_path").DIRECTORY_SEPARATOR."maintenance.txt";
        if(file_exists($maintenanceFile))
            $maintenance=file_get_contents($maintenanceFile);
        if(!empty($maintenance)){
            $res->setStatus(503)->message($maintenance);
        }else{
            self::_executeRoutes($req,$res);//$router->execute($req,$res);
        }
        foreach($res->getHeaders() as $key=>$val)
            header($key.": ".$val);
        if($res->getStatus()!=200)
            http_response_code($res->getStatus());
        
        $content=ContentNegotiator::processResponse($req,$res);
        //clean output buffer if (needed in config)
        echo $content;
    }
    private static function _executeRoutes($req,$res){
        $url=$req->getPath();
		try{
			$found=false;
			foreach(self::$routes[$req->getMethod()] as $pattern => $cb) {
                $resultMatch=preg_match_all("/^".$pattern."$/",$url,$matches,PREG_PATTERN_ORDER);
                //if($resultMatch===FALSE) echo $pattern;
                if($resultMatch<1)
                    continue;
                $newparam=[];
                $params=$cb['params'];
                if($params!=NULL){
                    foreach($param as $key => $value){
                        if(isset($matches[$key])){
                            $newparam[$key]=$matches[$key][0];
                        }else{
                            $newparam[$key]=NULL;
                        }
                    }
                }
                $req->setParam($newparam);
                if(!empty($bodyType=$cb['body']) && !empty($body=$req->getBody()))
                    $req->setBody(Normalizer::denormalize($body,$bodyType));
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
}