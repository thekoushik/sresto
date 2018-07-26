<?php
$settings=require_once __DIR__ . '/config/settings.php';

date_default_timezone_set($settings['timezone']);

require __DIR__ . '/../vendor/autoload.php';

use SRESTO\Utils\CoreUtil;
$environment=CoreUtil::parseENV(__DIR__.'/../.env');

use SRESTO\Tools\Logger;
Logger::create(__DIR__."/sresto.log");

use SRESTO\Configuration;
Configuration::set(["base_path"=>__DIR__,
                    "processor_package_path"=>__DIR__.'/API/Processors',
                    "resource_package_path"=>__DIR__.'/API/Resources',
                    "resource_yaml_path"=>__DIR__."/db/resources",
                    "config_path"=>__DIR__."/config",
                    "cache_path"=>__DIR__."/config/cache"]);

register_shutdown_function(function() {
    $error = error_get_last();
    if( $error !== NULL) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];
        Logger::fatal($errno,$errfile,$errline,$errstr);
    }
});

//$services=require_once __DIR__ . '/config/services.php';

$processors=CoreUtil::scanClasses(__DIR__,'/API/Processors');
$services=CoreUtil::scanClasses(__DIR__,'/API/Services');

//use SRESTO\Common\MetaData;
//$resourceMetaData=array_map(function($item){return new MetaData($item);},CoreUtil::scanClasses(__DIR__,'/API/Resources'));

use SRESTO\Application;
$entityManager=Application::boot($environment,$settings,$processors,$services);

/*\SRESTO\Common\Event::addListener(\SRESTO\MIMEs\ContentNegotiator::PreProcessResponse,function($res){
    $content=$res->getContent();
    var_dump($content);
});*/
//Configuration::load(__DIR__."/config/maps");
//SRESTO\DTO\Normalizer::useMap('maps');
