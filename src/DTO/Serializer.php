<?php
namespace SRESTO\DTO;
use SRESTO\Configuration as Config;

class Serializer {
    //Serialization Block
    public static function serialize($obj,$track=[],$trackClass=[]){
        if($obj instanceof \Traversable | is_array($obj)){
            $arr=[];
            $keys=array_keys($obj);
            if(array_keys($keys)!==$keys){//associative
                foreach($obj as $key=>$o)
                    $arr[$key]=self::serialize($o,$track,$trackClass);
            }else{//sequential
                $once=true;
                foreach($obj as $o){
                    if(!is_scalar($o))
                        if($once){
                            $once=false;
                            if(in_array((new \ReflectionClass($o))->getShortName(),$trackClass))
                                return null;
                        }
                    $arr[]=self::serialize($o,$track,$trackClass);
                }
            }
            return $arr;
        }else{
            if(!is_scalar($obj)){
                if(in_array((new \ReflectionClass($obj))->getShortName(),$trackClass))
                    return null;
                return $obj->serializeMe($track,$trackClass);
            }else{
                return $obj;
            }
        }
    }
    /**
     * Rename keys of object as specified in map
     *
     * @param       $obj  object being serialized
     * @param array $src  object variables
     * @return associative array with renamed keys
     */
    public static function mapOut($obj,$src,$track,$trackClass){
        $clazz=(new \ReflectionClass($obj))->getShortName();
        if(!Config::has('maps',$clazz))
            return null;
        
        if(in_array($obj,$track)) return null;
        if(in_array($clazz,$trackClass))
            return null;
        $track[]=$obj;
        $trackClass[]=$clazz;
        
        $new_dest=[];
        foreach(Config::get('maps')[$clazz]['fields'] as $key=>$val){
            if(isset($src[$key]))
                $new_dest[$val]=$src[$key];
        }
        if(isset(Config::get('maps')[$clazz]['assoc'])){
            //$methods=get_class_methods($obj);
            foreach(Config::get('maps')[$clazz]['assoc'] as $key=>$val){
                if(isset($src[$key])){
                    $val=explode(' ',$val,2);
                    $val=(count($val)==2)?$val[1]:$key;
                    $method='get'.ucwords($key);
                    if(method_exists($obj, $method)){
                        $new_dest[$val]=self::serialize($obj->$method(),$track,$trackClass);
                    }else{
                        $new_dest[$val]=self::serialize($obj->$key,$track,$trackClass);
                    }
                }
            }
        }
        return $new_dest;
    }
    //End of Serialization Block

    //Deserialization Block
    public static function deserialize($array,$clazz){
        if(!Config::has('maps',$clazz))
            return null;
        $keys = array_keys($array);
        if(array_keys($keys) !== $keys){
            if (class_exists($clazz)) {
                $obj = new $clazz;
            }else{
                $clazz_default=Config::get("resource_package")."\\".$clazz;
                if (class_exists($clazz_default)) {
                    $obj = new $clazz_default;
                }else{
                    throw new \Exception("Class '".$clazz."' is not defined.");
                }
            }
            //$obj=new Config::get("resource_package")."\\".$clazz;
            if(isset(Config::get('maps')[$clazz]['fields'])){
                foreach(Config::get('maps')[$clazz]['fields'] as $key=>$val){
                    if(isset($array[$val])){
                        $setter='set'.ucwords($key);
                        if(method_exists($obj, $setter)){
                            $obj->$setter($array[$val]);
                        }else{
                            $obj->$key=$array[$val];
                        }
                    }
                }
            }
            if(isset(Config::get('maps')[$clazz]['assoc'])){
                //$methods=get_class_methods($obj);
                foreach(Config::get('maps')[$clazz]['assoc'] as $key=>$val){
                    $val=explode(' ',$val,2);
                    $deserialised=null;
                    if(count($val)==2){//class map
                        if(!isset($array[$val[1]])) continue;
                        $deserialised=self::deserialize($array[$val[1]],$val[0]);
                    }else{//class
                        if(!isset($array[$key])) continue;
                        $deserialised=self::deserialize($array[$key],$val[0]);
                    }
                    $setter='set'.ucwords($key);
                    if(method_exists($obj, $setter)){
                        $obj->$setter($deserialised);
                    }else{
                        $obj->$key=$deserialised;
                    }
                }
            }
            return $obj;
        }else{//sequential
            $obj=[];
            foreach($array as $item)
                $obj[]=self::deserialize($item,$clazz);
            return $obj;
        }
    }
    //End of Deserialization Block
}