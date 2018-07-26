<?php
namespace API\Services;
use SRESTO\Service\ClientServiceInterface;
use API\Resources\User;

class UserService{
    private $entityManager;//https://www.doctrine-project.org/api/orm/2.6/Doctrine/ORM/EntityManager.html
    private $userRepository;//https://www.doctrine-project.org/api/orm/2.6/Doctrine/ORM/EntityRepository.html
    public function __construct($em){
        $this->entityManager=$em;
        $this->userRepository = $this->entityManager->getRepository('API\\Resources\\User');
    }
    public function createUser(User $user){
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }
    public function getUsers(){
        return $this->userRepository->findAll();
    }
    public function getUser($id){
        return $this->userRepository->find($id);
    }
}