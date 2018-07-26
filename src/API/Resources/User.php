<?php
namespace API\Resources;
use SRESTO\Storage\Resource;

/**
 * @Entity
 * @Table(name="users")
 */
class User extends Resource{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     * @DTOIgnore
     */
    protected $id;
    /**
     * @Column(length=50, type="string")
     * @Map(name="first_name")
     */
    protected $name;
    /**
     * @Column(length=100, unique=true, type="string")
     * @Map(name="email_address")
     */
    protected $email;
    /**
     * @OneToMany(targetEntity="API\Resources\Product", mappedBy="user")
     * @Map(name="items")
     * @ArrayOf(name="API\Resources\Product")
     */
    protected $products;
    public function __construct(){$this->products = new \Doctrine\Common\Collections\ArrayCollection();}
    public function getId(){return $this->id;}
    public function setId($id){$this->id=$id;}
    public function getName(){return $this->name;}
    public function setName($name){$this->name = $name;}
    public function getEmail(){return $this->email;}
    public function setEmail($email){$this->email = $email;}
    public function getProducts(){return $this->products;}
    public function setProducts($products){$this->products=$products;}
}