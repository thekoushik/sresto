<?php
namespace API\Processors;
use SRESTO\Processors\RequestProcessor;
/**
 * @RequestMapping(path="users")
 */
class UserProcessor implements RequestProcessor{
    /** @Service */
    private $userService;
    /** @RequestMapping(method="GET") */
    public function process($req,$res){
        $res->setContent($this->userService->getUsers());
    }
    /**
     * @RequestMapping(method="POST")
     * @RequestBody(className="User")
     * */
    public function create($req,$res){
        $res->setContent($this->userService->createUser($req->getBody()));
    }
    /**
     * @RequestMapping(method="GET",path=":id")
     */
    public function getUser($req,$res){
        $res->setContent($this->userService->getUser($req->getParam('id')));
    }
}