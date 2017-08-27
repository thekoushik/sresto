<?php
namespace SRESTO\Middleware;
use SRESTO\Security\Authentication;
class Auth implements Middleware{
    public function __construct(){
        //init
    }
    public function run($req,$res){
        $auth=new Authentication();
        $auth->setHeader($req->headers);
        if(isset($req->headers['Authorization'])){
            if(!$auth->validate($req,$res))
                return false;
            
            if($auth->request['token_type']=="Basic"){
                if($auth->request['client_id']=='koushik' && $auth->request['client_secret']=='hello123'){
                    //$res->sendToken("mF_9.B5f-4.1JqM","tGzv3JOkF0XG5Qx2TlKWIA")->noCache();
                }else{
                    $res->sendError(400,"Bad Request");
                    return false;
                }
            }/*else if($auth->request['token_type']=="Bearer")
                $res->message($auth->request['token']);*/
            
        }else{
            $res->status(401)->header('WWW-Authenticate','Bearer realm="Service"');
            return false;
        }
        return true;//go forward
    }
}