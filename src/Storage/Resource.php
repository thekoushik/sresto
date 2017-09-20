<?php
namespace SRESTO\Storage;
use SRESTO\DTO\Serializer;

abstract class Resource{
    public function serializeMe(){
        return get_object_vars($this);
    }
    /*public function serializeMe($track=[],$trackClass=[]){
        return Serializer::mapOut($this,get_object_vars($this),$track,$trackClass);
    }*/
}