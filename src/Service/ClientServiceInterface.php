<?php
namespace SRESTO\Service;
interface ClientServiceInterface{
    public function getClient($client_id);
    public function checkClientCredentials($client_id,$client_secret);
}