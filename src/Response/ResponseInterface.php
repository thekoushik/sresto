<?php
namespace SRESTO\Response;
interface ResponseInterface{
    public function getHeaders();
    public function setHeaders($array);
    public function getHeader($name);
    public function setHeader($name,$value);
    public function hasHeader($name);
    public function removeHeader($name);
    public function getContent();
    public function setContent($val);
    public function getStatus();
    public function setStatus($val);
    public function abort($status,$reason);
}