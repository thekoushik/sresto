<?php
namespace SRESTO\Storage;
/**
 * @MappedSuperclass
 */
class ClientBasic extends Resource{
    /**
     * @Column(type="string",length=100)
     */
    protected $client_id;
    /**
     * @Column(type="string",length=100)
     */
    protected $client_secret;

    public function getClientId(){return $this->client_id;}
    public function setClientId($id){$this->client_id=$id;}
    public function getClientSecret(){return $this->client_secret;}
    public function setClientSecret($secret){$this->client_secret=$secret;}
}