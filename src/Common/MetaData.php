<?php
namespace SRESTO\Common;

class MetaData{
    const METHOD='methods';
    const PROPERTY='props';

    private static $cache=[];

    protected $data;
    public $reflection;

    public function __construct($clazz){
        $this->reflection=new \ReflectionClass($clazz);
        $clazz=$this->reflection->name;
        if(isset(self::$cache[$clazz]))
            $this->data=self::$cache[$clazz];
        else{
            $data=self::scanMetadata($this->reflection);
            self::$cache[$clazz]=$data;
            $this->data=$data;
        }
    }
    public function newInstance(){
        return $this->reflection->newInstance();
    }
    public function fetchPropertyValue($propertyName,$obj){
        if(is_string($propertyName))
            $reflection=$this->reflection->getProperty($propertyName);
        else{
            $reflection=$propertyName;
            $propertyName=$reflection->name;
        }
        if($reflection->isStatic()){
            if($reflection->isPublic())
                return $obj::$$propertyName;
            return null;
        }
        if($reflection->isPublic())
            return $obj->{$propertyName};
        if(($getter=$this->getPublicGetter($propertyName)))
            return $getter->invoke($obj);
        if(($isser=$this->getPublicIsser($propertyName)))
            return $isser->invoke($obj);
        return null;
    }
    public function setPropertyValue($propertyName,$value,$obj){
        if(is_string($propertyName))
            $reflection=$this->reflection->getProperty($propertyName);
        else{
            $reflection=$propertyName;
            $propertyName=$reflection->name;
        }
        if($reflection->isStatic()){
            if($reflection->isPublic()){
                $obj::$$propertyName=$value;
                return true;
            }
            return false;
        }
        if($reflection->isPublic()){
            $obj->{$propertyName}=$value;
            return true;
        }
        if(($setter=$this->getPublicSetter($propertyName))){
            $setter->invoke($obj,$value);
            return true;
        }
        return false;
    }
    public function getPublicGetter($propertyName){
        return $this->getPublicMethod("get".strToPascalCase($propertyName));
    }
    public function getPublicIsser($propertyName){
        return $this->getPublicMethod("is".strToPascalCase($propertyName));
    }
    public function getPublicSetter($propertyName){
        return $this->getPublicMethod("set".HstrToPascalCase($propertyName));
    }
    public function getPublicMethod($name){
        $methods=$this->reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach($methods as $method)
            if($method->name==$name)
                return $method;
        return null;
    }
    public function getPropertyAnnotation($propertyName,$annotation=null){
        if($annotation==null)
            return $this->data[self::PROPERTY][$propertyName]['annotations'];
        else{
            foreach($this->data[self::PROPERTY][$propertyName]['annotations'] as $anno)
                if(get_class($anno)===$annotation)
                    return $anno;
            return null;
        }
    }
    public function getMethodAnnotation($methodName,$annotation=null){
        if($annotation==null)
            return $this->data[self::METHOD][$methodName]['annotations'];
        else{
            foreach($this->data[self::METHOD][$methodName]['annotations'] as $anno)
                if(get_class($anno)===$annotation)
                    return $anno;
            return null;
        }
    }
    public function getClassAnnotation($annotation=null){
        if($annotation==null)
            return $this->data['class']['annotations'];
        else{
            foreach($this->data['class']['annotations'] as $anno)
                if(get_class($anno)===$annotation)
                    return $anno;
            return null;
        }
    }
    ///////////////////////////////////////////////////////////
    public function getPublicMethods(){
        return $this->getMembersWithModifiers("public");
    }
    public function getPublicProperties(){
        return $this->getMembersWithModifiers("public",self::PROPERTY);
    }
    public function hasGetter($propertyName){
        return $this->hasMethod("get".strToPascalCase($propertyName));
    }
    public function hasIsser($propertyName){
        return $this->hasMethod("is".strToPascalCase($propertyName));
    }
    public function hasSetter($propertyName){
        return $this->hasMethod("set".strToPascalCase($propertyName));
    }
    public function hasMethod($name){
        return isset($this->data[self::METHOD][$name]);
    }
    public function isMethodPublic($method){
        return (bool)in_array("public",$this->data[self::METHOD][$name]['modifiers']);
    }
    /*public function getAnnotation($memberName,$type=self::PROPERTY,$annotation=null){
        if($annotation==null)
            return $this->data[$type][$memberName]['annotations'];
        else{
            foreach($this->data[$type][$memberName]['annotations'] as $anno)
                if(get_class($anno)===$annotation)
                    return $anno;
            return null;
        }
    }*/
    public function getByAnnotation($annotationClazz){
        $members=[];
        foreach($this->data[self::PROPERTY] as $name=>$prop){
            foreach($prop['annotations'] as $anno){
                if(get_class($anno)==$annotationClazz)
                    $member[$name]='property';
            }
        }
        foreach($this->data[self::METHOD] as $name=>$prop){
            foreach($prop['annotations'] as $anno){
                if(get_class($anno)==$annotationClazz)
                    $member[$name]='method';
            }
        }
        return $members;
    }
    public function getGetter($propertyName){
        $method="get".strToPascalCase($propertyName);
        return $this->hasMethod($method)?$method:null;
    }
    public function getIsser($propertyName){
        $method="is".strToPascalCase($propertyName);
        return $this->hasMethod($method)?$method:null;
    }
    public function getSetter($propertyName){
        $method="set".strToPascalCase($propertyName);
        return $this->hasMethod($method)?$method:null;
    }
    public function getGetters(){
        return $this->getMembersLike("/^get/");
    }
    public function getIssers(){
        return $this->getMembersLike("/^is/");
    }
    public function getSetters(){
        return $this->getMembersLike("/^set/");
    }
    public function getMembersLike($regex,$type=self::METHOD){
        $members=[];
        foreach($this->data[$type] as $name=>$member){
            if(preg_match($regex,$name))
                $members[]=$name;
        }
        return $members;
    }
    public function getMembersWithModifiers($modifierNames,$type=self::METHOD){
        if(empty($modifierNames)) return null;
        $array=is_array($modifierNames);
        $members=[];
        foreach($this->data[$type] as $name=>$member){
            if($array){
                if(count($modifierNames)==count(array_intersect($modifierNames,$member['modifiers'])))
                    $members[]=$name;
            }else if(in_array($modifierNames,$member['modifiers']))
                $members[]=$name;
        }
        return $members;
    }
    /**
     * Returns metadata information of the specified class
     *
     * @param mixed $clazz
     * @return array
     */
    protected static function scanMetadata($reflector){
        $metaData=['class'=>[],self::PROPERTY=>[],self::METHOD=>[]];
        $metaData['class']['annotations']=self::extractAnnotations($reflector->getDocComment());
        $metaData['class']['namespace']=$reflector->getNamespaceName();
        $props=$reflector->getProperties();
        foreach($props as $prop){
            $metaData[self::PROPERTY][$prop->getName()]['annotations']=self::extractAnnotations($prop->getDocComment());
            $metaData[self::PROPERTY][$prop->getName()]['reflection']=$prop;
            $metaData[self::PROPERTY][$prop->getName()]['modifiers']=\Reflection::getModifierNames($prop->getModifiers());
        }
        $methods=$reflector->getMethods();
        foreach($methods as $method){
            $metaData[self::METHOD][$method->getName()]['annotations']=self::extractAnnotations($method->getDocComment());
            $metaData[self::METHOD][$method->getName()]['reflection']=$method;
            $metaData[self::METHOD][$method->getName()]['modifiers']=\Reflection::getModifierNames($method->getModifiers());
            $metaData[self::METHOD][$method->getName()]['params']=array_map(function($item){
                return ['name'=>$item->getName(),'required'=>!$item->isOptional()];
            },$method->getParameters());
            //$metaData[self::METHOD][$method->getName()]['return']=$method->getReturnType();
        }
        return $metaData;
    }
    /**
     * Extracts annotations from docblock comment
     *
     * @param string $comment
     * @return array
     */
    protected static function extractAnnotations($comment){
        $comment=substr($comment,3,-2);
        return array_values(array_filter(array_map(function($item){//array_values used because array_filter unset items by index
            $item=trim($item," \t*");
            if(!empty($item))
                return self::parseAnnotation($item);
            return null;
        },preg_split("/[\r\n]/",$comment,null,PREG_SPLIT_NO_EMPTY))));
    }
    /**
     * Parses annotation from string(nested annotations not supported yet)
     *
     * @param string $annotation
     * @return array
     */
    protected static function parseAnnotation($annotation){
        if($annotation[0]!="@") return null;
        $annotation=substr($annotation,1);
        if(!preg_match("/([^a-zA-Z0-9_\\\])/",$annotation,$match,PREG_OFFSET_CAPTURE)){
            return self::createAnnotationFromClassName($annotation);
        }
        if($match[0][0]!="("){
            return null;
        }
        $clazz=substr($annotation,0,$match[0][1]);
        $obj=self::createAnnotationFromClassName($clazz);
        if($obj==null) return null;
        $options=substr($annotation,$match[0][1]+1,-1);
        $options=explode(",",$options);
        foreach($options as $op){
            $op=explode("=",trim($op),2);
            if(count($op)==1){
                //$op[0]=true;
            }else
                $obj->{$op[0]}=trim($op[1],"'\"");
        }
        return $obj;
    }
    protected static function createAnnotationFromClassName($clazz){
        if(class_exists($clazz)){
            ;//
        }else if(class_exists("SRESTO\\Common\\Annotations\\".$clazz)){
            $clazz="SRESTO\\Common\\Annotations\\".$clazz;
        }else
            return null;
        $reflector=new \ReflectionClass($clazz);
        if($reflector->implementsInterface("SRESTO\\Common\\Annotations\\Annotation"))
            return $reflector->newInstance();
        return null;
    }
}