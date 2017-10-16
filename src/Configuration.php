<?php
namespace SRESTO;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use SRESTO\Utils\CoreUtil;
use SRESTO\Exceptions\SRESTOException;

final class Configuration{
    private static $configs=["resource_package"=>"API\\Resources",
                             "processor_package"=>"API\\Processors"];
    /*private static $dtoMetaData=[];
    public static function setDTOMetaData($array){
        if(count(self::$dtoMetaData)==0)
            self::$dtoMetaData=$array;
        else
            throw SRESTOException::cannotReAssignDTOMetaDataException();
    }
    public static function getDTOMetaData($clazz){
        return self::$dtoMetaData[$clazz];
    }*/
    public static function set($name,$array=null){
        if(is_array($name)){
            foreach($name as $key=>$value)
                if(!isset(self::$configs[$key]))
                    self::$configs[$key]=$value;
                else
                    throw new Exception("Cannot modify config '$key'");
        }else{
            if(!isset(self::$configs[$name]))
                self::$configs[$name]=$array;
            else
                throw new Exception("Cannot modify config '$name'");
        }
    }
    public static function get($name){
        /*if(!isset(self::$configs[$name]))
            return null;//throw new Exception("")
        */
        return self::$configs[$name];
    }
    public static function has($name,$attr=null){
        if($name==null)
            return false;
        if($attr==null)
            return (bool)isset(self::$configs[$name]);
        else
            return (bool)isset(self::$configs[$name][$attr]);
    }
    public static function load($path){
        $path=rtrim($path,"/");
        $files=CoreUtil::scanDirectories($path);
        foreach($files as $file){
            $info=pathinfo($file);
            if($info['extension']!="yml") continue;
            try{
                self::set($info['filename'],CoreUtil::parseYML(/*$path."/".*/$file));
            }catch(ParseException $e){
                return false;
            }
        }
        return true;
    }
}