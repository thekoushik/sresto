<?php
namespace SRESTO\Common;
//use SRESTO\Exceptions\SRESTOException;
//use SRESTO\Tools\Logger;

class Validator{
    private $obj;
    private $objMeta;
    private $condition;
    private $messages;
    /*
        [
            'required'=>['id','name'],
            'email'=>'email',
            'min'=>['name'=>6],
            'max'=>['name'=>100,'email'=>100],
            'diff'=>['name'=>'id'],
            'exist'=>['email'=>['Student'=>'student_email']],
            'unique'=>['email','name'],
            'numeric'=>['id'],
        ]
    */
    public function __construct($obj,$condition){
        $this->obj=$obj;
        $this->condition=$condition;
        $this->objMeta=new MetaData($obj);
    }
    protected function rule_required($prop){
        if($this->objMeta->fetchPropertyValue($prop,$this->obj)==null)
            return '\''.$prop.'\' is required';
    }
    protected function rule_min($prop,$param){
        if(strlen($this->objMeta->fetchPropertyValue($prop,$this->obj))<$param)
            return '\''.$prop.'\' should be at least '.$param.' characters long';
    }
    protected function rule_max($prop,$param){
        if(strlen($this->objMeta->fetchPropertyValue($prop,$this->obj))>$param)
            return '\''.$prop.'\' should be at most '.$param.' characters long';
    }
    protected function rule_numeric($prop){
        if(!is_numeric($this->objMeta->fetchPropertyValue($prop,$this->obj)))
            return '\''.$prop.'\' should be numeric';
    }
    public function validate(){
        $message=[];
        foreach($this->condition as $rule=>$props){
            foreach($props as $index=>$prop){
                $method='rule_'.$rule;
                if(!method_exists($this,$method))
                    throw new \Exception('Unsupported rule \''.$rule.'\'');
                if(is_integer($index)){
                    if(isset($this->message[$prop])) continue;
                    $result=$this->$method($prop);
                }else{
                    if(isset($this->message[$index])) continue;
                    $result=$this->$method($index,$prop);
                    $prop=$index;
                }
                if($result!=null)
                    $message[$prop]=$result;
            }
        }
        $this->messages=$message;
        return (bool)(count($message)!=0);
    }
    public function getMessages(){
        return $this->messages;
    }
}