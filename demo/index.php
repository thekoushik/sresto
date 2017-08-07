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

$router->execute();
