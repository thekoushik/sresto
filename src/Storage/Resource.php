<?php
namespace SRESTO\Storage;
use SRESTO\DTO\Serializer;

/**
 * @MappedSuperclass
 * @HasLifecycleCallbacks
 */
abstract class Resource{
    /**
     * @Column(type="datetime")
     */
    protected $created_at;
    /**
     * @Column(type="datetime")
     * @DTOIgnore
     */
    protected $updated_at;

    public function getCreatedAt(){return $this->created_at;}
    public function getUpdatedAt(){return $this->updated_at;}
    public function setCreatedAt($date){$this->created_at=$date;}
    public function setUpdatedAt($date){$this->created_at=$date;}

    /**
     * Triggered on insert
     * @PrePersist
     */
    public function onPrePersist(){
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }
    
    /**
     * Triggered on update
     * @PreUpdate
     */
    public function onPreUpdate(){
        $this->updated_at = new \DateTime();
    }

    /*public function normalizeMe(){
        return get_object_vars($this);
    }*/
}