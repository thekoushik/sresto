<?php
require __DIR__ . '/../vendor/autoload.php';

use SRESTO\Router;

$router=new Router();

$router->get("/hello",function($req,$res,$s){
    $res->message("Hello World");
});

$sub=$router->subRouter("/class");
$sub->get("/:id",function($req,$res,$s){
    $res->message("Your class id is ".$req->param['id']);
},['id'=>'digits']);
$sub->get("/:name",function($req,$res,$s){
    $res->message("Your class name is ".$req->param['name']);
},['name'=>'alphabets']);

$sub2=$router->subRouter("/auth");
$sub2->get("/check",function($req,$res,$s){
    if($req->token_type=="Basic"){
        if($req->token['client_id']=='koushik' && $req->token['client_secret']=='hello123'){
            $res->sendToken("mF_9.B5f-4.1JqM","tGzv3JOkF0XG5Qx2TlKWIA")->noCache();
        }else{
            $res->status(400);
        }
    }else if($req->token_type=="Bearer")
        $res->message($req->token);
    else
        $res->status(401)->header('WWW-Authenticate','Bearer realm="example"');
});
$router->execute();