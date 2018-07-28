<?php
namespace SRESTO\MIMEs;
class MIMEType{
    const TEXT=1;
    const JSON = 0;
    const XML = 2;
    const FORM=3;
    const MULTIPART=4;

    
    const TYPES=[
        'application/json',
        'text/plain',
        'application/xml',
        'application/x-www-form-urlencoded',
        'multipart/form-data'
    ];
    private $current;
    private $multipartBoundary=null;

    public function __construct($str){
        $this->current=self::fromString($str);
        if($this->current===self::MULTIPART){
            $this->multipartBoundary=substr($str,strpos($str,'boundary=')+10);
        }
    }
    public function getType(){
        return $this->current;
    }
    public static function fromString($str){
        if($str==="*/*") return self::JSON;/////json for all
        foreach (self::TYPES as $index=>$value)
            if( strpos($str,$value)!==false )
                return $index;
        
        return self::TEXT;
    }
    public function parseFrom($str){
        switch($this->current){
            case 1:
                return $str;
            case 0:
                return self::parseFromJSON($str);
            case 2:
                return self::parseFromXML($str);
            case 3:
                /*$data=[];
                if(strpos($str,'Content-Disposition: form-data;')>0){
                    //var_dump($str);
                    //var_dump($_POST);
                    parse_str($str,$data);
                }else{
                }*/
                parse_str($str,$data);
                return $data;
            case self::MULTIPART:
                $arr=explode($this->multipartBoundary,$str);
                $len=count($arr);
                $data=[];
                for($i=1;$i<$len-1;$i++){
                    list($key,$val)=explode("\"\r\n\r\n",substr($arr[$i],40,strlen($arr[$i])-45),2);
                    $data[$key]=$val;
                }
                return $data;
            default:
                return $str;
        }
    }
    public function parseTo($str){
        switch($this->current){
            case 0:
                return self::parseToJSON($str);
            case 2:
                return self::parseToXML($str);
            default:
                return $str;
        }
    }

    public static function parseFromXML($xmlString){
        $backup = libxml_disable_entity_loader(true);
        $backup_errors = libxml_use_internal_errors(true);
        $body = simplexml_load_string($xmlString);
        libxml_disable_entity_loader($backup);
        libxml_clear_errors();
        libxml_use_internal_errors($backup_errors);
        if ($body === false)
            $body=null;
        return $body;
    }
    public static function parseToXML($obj){
        return "";
    }
    public static function parseFromJSON($jsonString){
        return json_decode($jsonString,true);
    }
    public static function parseToJSON($obj){
        return json_encode($obj);
    }
    const FILE_MIME_TYPES = array(
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',
        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );
    public static function getMimeType($filename){
        $parts=explode('.',$filename);
        $ext = strtolower(array_pop($parts));
        if (array_key_exists($ext, self::FILE_MIME_TYPES)) {
            return self::FILE_MIME_TYPES[$ext];
        }else if(function_exists('mime_content_type')){
            return mime_content_type($filename);
        }else if(function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }else{
            return 'application/octet-stream';
        }
    }
}