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
use SRESTO\DTO\Normalizer;
use SRESTO\Common\Annotations\Service;
use SRESTO\Common\MetaData;
use SRESTO\Tools\Logger;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class Application{
    private static $booted=false;
    private static $processors=[];
    private static $routes=null;
    private static $optionsRoutes=null;
    private static $asset_url=null;
    private static function createRouter($baseurl=''){
        if(is_array($baseurl))
            return Router::createFromArray($baseurl);
        else
            return Router::create($baseurl);
    }
    /**
     * Boots the application for the first time
     */
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

            //autowiring via constructors
            /*$r=new \ReflectionClass($processor);
            $con=$r->getConstructor();
            if($con!=null){
                $params=$con->getParameters();
                foreach($params as $param){
                    $paramClassName=$param->getClass()->name;
                    $procArgs->addArgument(new Reference($paramClassName));
                }
            }*/

            self::$processors[$processor] = $container->get($proc);

            //autowiring via annotations
            $meta=new MetaData($processor);
            $serviceAutowirings=$meta->getPropertiesByAnnotation(Service::class);
            foreach($serviceAutowirings as $prop=>$annotation){
                $property=$meta->reflection->getProperty($prop);
                $property->setAccessible(true);
                $serviceClassName=$annotation->className;
                if(empty($serviceClassName)){
                    $serviceClassName=ucfirst($prop);
                }
                if(strpos($serviceClassName,'\\')===FALSE)
                    $serviceClassName=Configuration::get("service_package")."\\".$serviceClassName;
                $val=$container->get($serviceClassName);
                $property->setValue(self::$processors[$processor],$val);
            }
        }
        self::$asset_url=$settings['asset_url'];
        try{
            $routerCacheFile=Configuration::get("cache_path").DIRECTORY_SEPARATOR."router";
            if(file_exists($routerCacheFile)){
                $routerCache=file_get_contents($routerCacheFile);
                self::$routes=Router::root()->createRoutesFromCache($routerCache);
            }else{
                Router::createFromAnnotaion($processors);
                self::$routes=Router::root()->processRoutes();
            }
            self::$optionsRoutes=Router::createOptionsFromRoutes(self::$routes);
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
        }else if(self::_processAssetRequest($req)){
            return;
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
    private static function _processAssetRequest($req){
        if(empty(self::$asset_url)) return false;
        $url=substr($req->getPath(),1);
        if(empty($url))
            $file='../index.html';
        else{
            if(substr($url, 0, strlen(self::$asset_url)) !== self::$asset_url)
                return false;
            $file='../asset/'.substr($url,strlen(self::$asset_url)+1);
        }
        if (file_exists($file)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            header('Content-Type: '.finfo_file($finfo, $file));
            finfo_close($finfo);
            header('Content-Length: ' . filesize($file));
            readfile($file);
        }else{
            header("HTTP/1.0 404 Not Found");
            exit();
        }
        return true;
    }
    private static function _executeRoutes($req,$res){
        $url=$req->getPath();
		try{
            $found=false;
            $method=$req->getMethod();
			foreach(self::$routes[$method] as $pattern => $cb) {
                $resultMatch=preg_match_all("/^".$pattern."$/",$url,$matches,PREG_PATTERN_ORDER);
                //if($resultMatch===FALSE) echo $pattern;
                if($resultMatch<1)
                    continue;
                $newparam=[];
                $params=$cb['params'];
                if($params!=NULL){
                    foreach($params as $key => $value){
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
                    $fn_name=$procs['fn'];
                    if(!$o->$fn_name($req,$res))
                        break;
                }
                $found=TRUE;
                break;
			}
			if(!$found){
                if($method=="OPTIONS"){
                    $resultOptions=null;
                    foreach(self::$optionsRoutes as $pattern => $options) {
                        if(preg_match_all("/^".$pattern."$/",$url,$matches,PREG_PATTERN_ORDER)>0){
                            $resultOptions=$options;
                            break;
                        }
                    }
                    if($resultOptions==null)
                        throw new Error400Exception(404);
                    else
                        $res->setStatus(201)->setHeader('Allow',$resultOptions);
                }else
                    throw new Error400Exception(404);
            }
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