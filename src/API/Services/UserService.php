<?php
namespace API\Services;
use SRESTO\Service\ClientServiceInterface;
use API\Resources\User;

class UserService implements ClientServiceInterface{
    private $entityManager;
    public function __construct($em){
        $this->entityManager=$em;
    }
    public function getClient($client_id){
        return "[UserService]: ";
    }
    public function checkClientCredentials($client_id,$client_secret){
        return "Hello";
    }
    public function createUser(User $user){
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }
    public function getUsers(){
        $userRepository = $this->entityManager->getRepository('API\\Resources\\User');
        return $userRepository->findAll();
    }
}