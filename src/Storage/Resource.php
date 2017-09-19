<?php
namespace SRESTO\Storage;
use SRESTO\DTO\Serializer;

abstract class Resource{
    /*private $name;
    public function findById($id){
        //
    }
    public function findAll($id){
        //
    }*/
    public function serializeMe($track=[],$trackClass=[]){
        return Serializer::mapOut($this,get_object_vars($this),$track,$trackClass);
    }
}