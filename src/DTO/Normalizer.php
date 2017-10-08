<?php
namespace SRESTO\DTO;
use SRESTO\Configuration as Config;
use SRESTO\Utils\Helper;
use SRESTO\Utils\CoreUtil;
use SRESTO\Common\MetaData;
use SRESTO\Common\Annotations\Map;
use SRESTO\Common\Annotations\ClassOf;
use SRESTO\Common\Annotations\ArrayOf;
use SRESTO\Common\Annotations\DTOIgnore;

class Normalizer {
    private static function normalizeBuiltInClass($obj){
        switch(get_class($obj)){
            case 'DateTime':
                return $obj->format(\DateTime::W3C);
            default:
                return $obj;
        }
    }
    private static function denormalizeBuiltInClass($clazz,$data){
        switch($clazz){
            case 'DateTime':
                return new \DateTime($data);
            default:
                return new $clazz;
        }
    }

    //Normalization Block
    public static function normalize($obj,$track=[],$trackClass=[]){
        if($obj instanceof \Traversable){
            $arr=[];
            $once=true;
            foreach($obj as $o){
                if(!is_scalar($o) && !is_array($o))
                    if($once){
                        $once=false;
                        if(in_array((new \ReflectionClass($o))->getShortName(),$trackClass))
                            return null;
                    }
                $arr[]=self::normalize($o,$track,$trackClass);
            }
            return $arr;
        }else if(is_array($obj)){
            $arr=[];
            if(CoreUtil::isAssociativeArray($obj)){//associative
                foreach($obj as $key=>$o)
                    $arr[$key]=self::normalize($o,$track,$trackClass);
            }else{//sequential
                $once=true;
                foreach($obj as $o){
                    if(!is_scalar($o) && !is_array($o))
                        if($once){
                            $once=false;
                            if(in_array((new \ReflectionClass($o))->getShortName(),$trackClass))
                                return null;
                        }
                    $arr[]=self::normalize($o,$track,$trackClass);
                }
            }
            return $arr;
        }else{
            if(!is_scalar($obj)){
                $meta=new MetaData($obj);
                if(!$meta->reflection->isUserDefined()){
                    return self::normalizeBuiltInClass($obj);//check special classes like datetime
                }
                $clazz=$meta->reflection->getShortName();
                
                if(in_array($obj,$track)) return null;
                if(in_array($clazz,$trackClass))
                    return null;
                $track[]=$obj;
                $trackClass[]=$clazz;
                
                $new_dest=[];
                $props=$meta->reflection->getProperties();
                foreach($props as $prop){
                    if($meta->getPropertyAnnotation($prop->name,DTOIgnore::class)!=null) continue;
                    if($prop->isStatic()) continue;
                    $val=$meta->fetchPropertyValue($prop->name,$obj);
                    $map=$meta->getPropertyAnnotation($prop->name,Map::class);
                    if($map)
                        $new_dest[$map->name]=self::normalize($val,$track,$trackClass);
                    else
                        $new_dest[$prop->name]=self::normalize($val,$track,$trackClass);
                }
                return $new_dest;
            }else{
                return $obj;
            }
        }
    }
    //End of Normalization Block

    //Denormalization Block
    public static function denormalize($array,$clazz){
        $meta=new MetaData($clazz);
        if($meta->reflection->isUserDefined()){
            $obj=$meta->newInstance();
            $props=$meta->reflection->getProperties();
            $hitCount=0;
            foreach($props as $prop){
                $map=$meta->getPropertyAnnotation($prop->name,Map::class);
                $propName=($map==null)?$prop->name:$map->name;
                if(isset($array[$propName])){
                    if(($classOf=$meta->getPropertyAnnotation($prop->name,ClassOf::class))!=null){
                        $val=self::denormalize($array[$propName],$classOf->name);
                    }else if(($arrayOf=$meta->getPropertyAnnotation($prop->name,ArrayOf::class))!=null){
                        $val=[];
                        foreach($array[$propName] as $item)
                            $val[]=self::denormalize($item,$arrayOf->name);
                    }else
                        $val=$array[$propName];
                    if($meta->setPropertyValue($prop,$val,$obj))
                        $hitCount++;
                }
            }
            if($hitCount==0)
                $obj=null;
        }else{
            $obj=self::denormalizeBuiltInClass($clazz,$array);//$meta->newInstance();
        }
        return $obj;
    }
    //End of Denormalization Block
}