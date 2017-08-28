<?php
require __DIR__ . '/../vendor/autoload.php';

use SRESTO\Application;

$router=Application::createRouter();
$router->get("hello",function($req,$res){
    $res->message("Hello World");
});
$sub=Application::createRouter("class");
$sub->get(":id",function($req,$res){
    $res->message("class id is ".$req->param['id']);
},['id'=>'digits']);

$sub2=$sub->createBranch("school");
$sub2->with('auth')->get(":name",function($req,$res){
    $res->message("school name is ".$req->param['name']);
},['name'=>'alphabets']);
$sub2->get(":id",function($req,$res){
    $res->message("school id is ".$req->param['id']);
},['id'=>'digits']);

Application::execute();
/*
//TODO:
$route=[
    '/hello'=>['ClassName@method'],//,'get' 
    '/class'=>[
        '/:id'=>['ClassName@method',['id'=>'digits'],'post'],
        '/:name'=>['ClassName@method',['name'=>'alphabets']],
    ],
    '/auth'=>[
        '/check'=>['ClassName@method']
    ]
];
Application::createRouter($route);
*/