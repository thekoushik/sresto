<?php
namespace API\Resources;
use SRESTO\Storage\Resource;

/**
 * @Entity
 * @Table(name="products")
 */
class Product extends Resource{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    /** @Column(length=50, type="string") */
    protected $name;
    /**
     * @ManyToOne(targetEntity="API\Resources\User", inversedBy="products")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    public function getId(){return $this->id;}
    public function setId($id){$this->id=$id;}
    public function getName(){return $this->name;}
    public function setName($name){$this->name = $name;}
    public function getUser(){return $this->user;}
    public function setUser($user){$this->user = $user;}
}