<?php
namespace SRESTO\Request;

class HTTPRequest{
    public $method;
    public $body;
    public $originalURL;
    public $query;
    public $fragment;
    public $path;
    public $contentType;
    public $contentLength;
    public $param;
    public $accept;
    public $headers=[];
    protected $env;

    protected static $header_keys=[
        'SERVER_NAME',
        'SERVER_ADDR',
        'SERVER_PORT',
        'REMOTE_ADDR',
        'REQUEST_SCHEME',
        'SERVER_PROTOCOL',
        'REQUEST_METHOD',
        'QUERY_STRING',
        'REQUEST_URI',
        'SCRIPT_NAME',
        'REQUEST_TIME',
        'X_HTTP_METHOD_OVERRIDE'//TODO
    ];
    
    public function __construct(){
        $this->env=$_SERVER;
        if(!isset($_SERVER['HTTP_MOD_REWRITE']))//if (defined('URL_REWRITE_IS_OFF'))//define("URL_REWRITE_IS_OFF", "1");
            $this->fetchHeadersWhenURLRewriteIsOff($this->env);
        else
            $this->fetchHeaders($this->env);
        $this->param=[];
        switch($this->contentType){
            case "application/json":
                $this->body = json_decode(file_get_contents('php://input'), true);
                break;
            case "application/xml":
                $backup = libxml_disable_entity_loader(true);
                $backup_errors = libxml_use_internal_errors(true);
                $this->body = simplexml_load_string(file_get_contents('php://input'));
                libxml_disable_entity_loader($backup);
                libxml_clear_errors();
                libxml_use_internal_errors($backup_errors);
                if ($this->body === false)
                    $this->body=null;
                break;
            case "application/x-www-form-urlencoded":
                if($this->contentLength<0)
                    parse_str(file_get_contents('php://input'), $this->body);
                else
                    parse_str(file_get_contents('php://input', false , null, -1 , $this->contentLength ), $this->body);
                break;
            default:
                $this->body=file_get_contents("php://input");
        }
    }
    
    private function fetchHeaders($env){
        $this->method=$env['REQUEST_METHOD'];
        $this->originalURL=(isset($_SERVER['HTTPS'])?"https":"http")."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $headers_alt=[];
        if(function_exists("getallheaders"))
            $headers_alt=array_change_key_case(getallheaders(),CASE_UPPER);
        if(isset($env["CONTENT_LENGTH"]))
            $this->contentLength=intval($env['CONTENT_LENGTH']);
        else
            $this->contentLength=isset($headers_alt['CONTENT-LENGTH'])?intval($headers_alt['CONTENT-LENGTH']):0;
        if(isset($env["CONTENT_TYPE"]))
            $this->contentType=$env['CONTENT_TYPE'];
        else
            $this->contentType=isset($headers_alt['CONTENT-TYPE'])?$headers_alt['CONTENT-TYPE']:'text/plain';
        if(isset($env["CONTENT_LENGTH"]))
            $this->contentLength=intval($env['CONTENT_LENGTH']);
        else
            $this->contentLength=isset($headers_alt['CONTENT-LENGTH'])?intval($headers_alt['CONTENT-LENGTH']):0;
        if(isset($env["HTTP_ACCEPT"]))
            $this->accept=intval($env['HTTP_ACCEPT']);
        else
            $this->accept=isset($headers_alt['ACCEPT'])?intval($headers_alt['ACCEPT']):$this->contentType;
        
        $this->headers=$headers_alt;

        foreach(self::$header_keys as $h)
            if(isset($env[$h]))
                $this->headers[$h]=$env[$h];
        
        foreach($env as $key=>$val){
            if(strpos($key,"HTTP_")===0){
                $this->headers[substr($key,5)]=$val;
            }
        }

        $scriptName = parse_url($env['SCRIPT_NAME'], PHP_URL_PATH);
        $scriptDir = dirname($scriptName);
        $uri = parse_url('http://sresto.com' . $env['REQUEST_URI'], PHP_URL_PATH);
        $base = '/';
        $path = $uri;
        if (stripos($uri, $requestScriptName) === 0)
            $base = $scriptName;
        elseif ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0)
            $base = $scriptDir;
        if ($base)
            $path = ltrim(substr($uri, strlen($base)), '/');
        $this->path='/'.$path;
        $query =isset($env['QUERY_STRING'])? $env['QUERY_STRING']:'';
        if ($query === '')
            $query = parse_url('http://sresto.com' . $env['REQUEST_URI'], PHP_URL_QUERY);
        if($query==null)
            $this->query=[];
        else
            parse_str($query,$this->query);
        //#fragment
    }
    private function fetchHeadersWhenURLRewriteIsOff($env){
        $this->method=$env['REQUEST_METHOD'];
        $url=$env['QUERY_STRING'];
        
        $i=strpos($url,"?");
        if($i===false)
            $this->query=[];
        else{
            parse_str(substr($url,$i+1),$this->query);
            $url=substr($url,0,$i);
        }
        $this->path=$url;
        
        $this->originalURL=(isset($_SERVER['HTTPS'])?"https":"http")."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $headers=apache_request_headers();
        $this->headers=$headers;
        if(isset($_SERVER["CONTENT_TYPE"]))
            $this->contentType=$_SERVER['CONTENT_TYPE'];
        else
            $this->contentType=isset($headers['Content-Type'])?$headers['Content-Type']:'text/plain';
        if(isset($_SERVER["CONTENT_LENGTH"]))
            $this->contentLength=intval($_SERVER['CONTENT_LENGTH']);
        else 
            $this->contentLength=isset($headers['Content-Length'])?intval($headers['Content-Length']):-1;
        $this->accept=isset($headers['Accept'])?$headers['Accept']:'text/plain';
    }
    public function isJSON(){
        return ($this->contentType==="application/json");
    }
    public function isAJAX(){
        return (bool) (isset($this->headers['X_REQUESTED_WITH']) & ($this->headers['X_REQUESTED_WITH'] === 'XMLHttpRequest'));
    }
}