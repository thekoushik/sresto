<?php
namespace SRESTO\Request;
interface RequestInterface{
    public function getMethod();
    public function getBody();
    public function setBody($body);
    public function getQuery($name);
    public function getFragment();
    public function getPath();
    public function getContentType();
    public function getContentLength();
    public function getParam($name);
    public function setParam($array);
    public function getAccept();
    public function getHeader($name);
    public function isAJAX();
    public function getData($key=null);
    public function setData($key,$data=null);
}