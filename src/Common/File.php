<?php
namespace SRESTO\Common;
use SRESTO\Configuration;

class File{
    private $name=null;
    private $tmp_name=null;
    private $type=null;
    private $error=null;
    private $size=null;
    public function __construct($data=null){
        $this->name=$data['name'];
        $this->tmp_name=$data['tmp_name'];
        $this->type=$data['type'];
        $this->size=$data['size'];
        switch ($data['error']) { 
            case UPLOAD_ERR_OK:
                $message = "The file uploaded with success";
                break;
            case UPLOAD_ERR_INI_SIZE: 
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini"; 
                break; 
            case UPLOAD_ERR_FORM_SIZE: 
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"; 
                break; 
            case UPLOAD_ERR_PARTIAL: 
                $message = "The uploaded file was only partially uploaded"; 
                break; 
            case UPLOAD_ERR_NO_FILE: 
                $message = "No file was uploaded"; 
                break; 
            case UPLOAD_ERR_NO_TMP_DIR: 
                $message = "Missing a temporary folder"; 
                break; 
            case UPLOAD_ERR_CANT_WRITE: 
                $message = "Failed to write file to disk"; 
                break; 
            case UPLOAD_ERR_EXTENSION: 
                $message = "File upload stopped by extension"; 
                break;
            default:
                $message = "Unknown upload error"; 
        }
        $this->error=['code'=>$data['error'],'message'=>$message];
    }
    public static function fromRaw($data){
        if(is_array($data['tmp_name'])){
            $instances_data=[];
            foreach (['name','tmp_name','type','error','size'] as $value)
                foreach($data[$value] as $index=>$v)
                    $instances_data[$index][$value]=$v;
            $instances=[];
            foreach ($instances_data as $value)
                $instances[]=new self($value);
            return $instances;
        }else
            return new self($data);
    }
    public function getName(){return $this->name;}
    public function setName($name){$this->name;}
    public function getType(){return $this->type;}
    public function hasError(){return (bool)($this->error['code']!=0);}
    public function getError(){return $this->error;}
    public function getSize(){return $this->size;}
    public function upload($destination,$folder=null){
        return move_uploaded_file($this->tmp_name,(($folder==null)?Configuration::get('asset_path'):$folder).(empty($destination)?$this->name:$destination));
    }
}