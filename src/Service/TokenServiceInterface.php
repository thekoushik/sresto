<?php
namespace SRESTO\Service;
interface TokenServiceInterface{
    public function getAccessToken($token);
    public function createAccessToken();
    public function createRefreshToken();
    public function createAuthorizationCode();
    public function revokeAccessToken($token);
    public function revokeRefreshToken($token);
}