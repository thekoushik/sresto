<?php
namespace SRESTO\Request;
use SRESTO\MIMEs\ContentNegotiator;

class HTTPRequest{
    /*protected $method;
    protected $body;
    public $originalURL;
    protected $query;
    protected $fragment;
    protected $path;
    protected $contentType;
    protected $contentLength;
    protected $param;
    protected $accept;
    protected $headers=[];*/
    protected static $env;
    protected $data=[];
    protected static $requestData=null;

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
        if(self::$requestData!=null) return;
        self::$requestData=['data'=>[],'path'=>null];
        self::$env=$_SERVER;
        if(!isset(self::$env['HTTP_MOD_REWRITE']))//if (defined('URL_REWRITE_IS_OFF'))//define("URL_REWRITE_IS_OFF", "1");
            $this->fetchHeadersWhenURLRewriteIsOff(self::$env);
        else
            $this->fetchHeaders(self::$env);
        self::$requestData['param']=[];
        if(self::$requestData['contentLength']<0)
            $content=file_get_contents('php://input');
        else
            $content=file_get_contents('php://input', false , null, -1 , self::$requestData['contentLength'] );
        
        self::$requestData['body']=ContentNegotiator::processRequest(self::$requestData['contentType'],$content);
    }
    
    private function fetchHeaders($env){
        self::$requestData['method']=$env['REQUEST_METHOD'];
        self::$requestData['originalURL']=(isset($env['HTTPS'])?"https":"http")."://$env[HTTP_HOST]$env[REQUEST_URI]";
        
        $headers_alt=[];
        if(function_exists("getallheaders"))
            $headers_alt=array_change_key_case(getallheaders(),CASE_UPPER);
        if(isset($env["CONTENT_LENGTH"]))
            self::$requestData['contentLength']=intval($env['CONTENT_LENGTH']);
        else
            self::$requestData['contentLength']=isset($headers_alt['CONTENT-LENGTH'])?intval($headers_alt['CONTENT-LENGTH']):0;
        if(isset($env["CONTENT_TYPE"]))
            self::$requestData['contentType']=$env['CONTENT_TYPE'];
        else
            self::$requestData['contentType']=isset($headers_alt['CONTENT-TYPE'])?$headers_alt['CONTENT-TYPE']:null;
        if(isset($env["CONTENT_LENGTH"]))
            self::$requestData['contentLength']=intval($env['CONTENT_LENGTH']);
        else
            self::$requestData['contentLength']=isset($headers_alt['CONTENT-LENGTH'])?intval($headers_alt['CONTENT-LENGTH']):0;
        if(isset($env["HTTP_ACCEPT"]))
            self::$requestData['accept']=$env['HTTP_ACCEPT'];
        else
            self::$requestData['accept']=isset($headers_alt['ACCEPT'])?$headers_alt['ACCEPT']:self::$requestData['contentType'];
        
        self::$requestData['headers']=$headers_alt;

        foreach(self::$header_keys as $h)
            if(isset($env[$h]))
                self::$requestData['headers'][$h]=$env[$h];
        
        foreach($env as $key=>$val){
            if(strpos($key,"HTTP_")===0){
                self::$requestData['headers'][substr($key,5)]=$val;
            }
        }

        $scriptName = parse_url($env['SCRIPT_NAME'], PHP_URL_PATH);
        $scriptDir = dirname($scriptName);
        $uri = parse_url('http://sresto.com' . $env['REQUEST_URI'], PHP_URL_PATH);
        $base = '/';
        $path = $uri;
        if (stripos($uri, $scriptName) === 0)
            $base = $scriptName;
        elseif ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0)
            $base = $scriptDir;
        if ($base)
            $path = ltrim(substr($uri, strlen($base)), '/');
        self::$requestData['path']='/'.$path;
        $query =isset($env['QUERY_STRING'])? $env['QUERY_STRING']:'';
        if ($query === '')
            $query = parse_url('http://sresto.com' . $env['REQUEST_URI'], PHP_URL_QUERY);
        if($query==null)
            self::$requestData['query']=[];
        else
            parse_str($query,self::$requestData['query']);
        //#fragment
    }
    private function fetchHeadersWhenURLRewriteIsOff($env){
        self::$requestData['method']=$env['REQUEST_METHOD'];
        $url=$env['QUERY_STRING'];
        
        $i=strpos($url,"?");
        if($i===false)
            self::$requestData['query']=[];
        else{
            parse_str(substr($url,$i+1),self::$requestData['query']);
            $url=substr($url,0,$i);
        }
        self::$requestData['path']=$url;
        
        self::$requestData['originalURL']=(isset($env['HTTPS'])?"https":"http")."://$env[HTTP_HOST]$env[REQUEST_URI]";
        $headers=apache_request_headers();
        self::$requestData['headers']=$headers;
        if(isset($env["CONTENT_TYPE"]))
            self::$requestData['contentType']=$env['CONTENT_TYPE'];
        else
            self::$requestData['contentType']=isset($headers['Content-Type'])?$headers['Content-Type']:'text/plain';
        if(isset($env["CONTENT_LENGTH"]))
            self::$requestData['contentLength']=intval($env['CONTENT_LENGTH']);
        else 
            self::$requestData['contentLength']=isset($headers['Content-Length'])?intval($headers['Content-Length']):-1;
        self::$requestData['accept']=isset($headers['Accept'])?$headers['Accept']:'text/plain';
    }
    public function isJSON(){
        return (self::$requestData['contentType']==="application/json");
    }
    public function isAJAX(){
        return (bool) (isset(self::$requestData['headers']['X_REQUESTED_WITH']) & (self::$requestData['headers']['X_REQUESTED_WITH'] === 'XMLHttpRequest'));
    }

    public function getMethod(){return self::$requestData['method'];}
    public function getBody(){return self::$requestData['body'];}
    public function setBody($body){self::$requestData['body']=$body;}
    public function getQuery($name){return self::$requestData['query'][$name];}
    public function getFragment(){return self::$requestData['fragment'];}
    public function getPath(){return self::$requestData['path'];}
    public function getContentType(){return self::$requestData['contentType'];}
    public function getContentLength(){return self::$requestData['contentLength'];}
    public function getParam($name){return self::$requestData['param'][$name];}
    public function setParam($array){return self::$requestData['param']=$array;}
    public function getAccept(){return self::$requestData['accept'];}
    public function getHeader($name){return self::$requestData['headers'][$name];}
    public function hasHeader($name){return (bool)isset(self::$requestData['headers'][$name]);}
    public function getData($key=null){return ($key!=null)?self::$requestData['data'][$key]:self::$requestData['data'];}
    public function setData($key,$data=null){
        if($key!=null) self::$requestData['data'][$key]=$data;
    }
}