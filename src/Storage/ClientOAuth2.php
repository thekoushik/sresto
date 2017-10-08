<?php
namespace SRESTO\Storage;
/**
 * @MappedSuperclass
 */
class ClientOAuth2 extends Resource{
    /**
     * @Column(type="string",length=100)
     */
    protected $client_token;
    /**
     * @Column(type="string",length=100)
     */
    protected $client_refresh_token;

    public function getClientToken(){return $this->client_token;}
    public function setClientToken($token){$this->client_token=$token;}
    public function getClientRefreshToken(){return $this->client_refresh_token;}
    public function setClientRefreshToken($token){$this->client_refresh_token=$token;}
}