<?php
namespace SRESTO\Security;
class Authentication{
    protected $clientService;
    protected $tokenService;
    protected $config=[
        'id_lifetime'              => 3600,
        'access_lifetime'          => 3600,
        'www_realm'                => 'Service',
        'token_param_name'         => 'access_token',
        'token_bearer_header_name' => 'Bearer',
    ];
    protected $headers;
    public $request=[
        'token_type'=>null,
        'token'=>null,
        'client_id'=>null,
        'client_secret'=>null,
    ];

    public function __construct($config=[],$clientService=null,$tokenService=null){
        $this->clientService = $clientService;
        $this->tokenService = $tokenService;
        $this->config=array_merge($this->config,$config);
        $this->headers=[];
    }
    public function setHeader($headers){
        $this->headers=$headers;
    }
    public function validate($req,$res){
        $t=explode(" ",trim($this->headers['AUTHORIZATION']),2);
        $token_type=$t[0];
        $this->request['token_type']=$token_type;
        if($token_type=="Basic"){
            $b=explode(':',base64_decode($t[1]),2);
            if(count($b)>1){
                $this->request['client_id']=$b[0];
                $this->request['client_secret']=$b[1];
            }else
                $res->sendError(400,"invalid_credentials","Invalid credentials");
        }else if($token_type=="Bearer")
            $this->request['token']=$t[1];
        else
            $res->sendError(400,"unsupported_token","Unsupported token type '".$token_type."'.");//throw new \Exception();
        return true;//return false to stop route forwarding
    }
}