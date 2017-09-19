<?php
namespace SRESTO\MIMEs;
use SRESTO\Request\RequestInterface;
use SRESTO\Response\ResponseInterface;
use SRESTO\MIMEs\MIMEType as ContentType;
use SRESTO\DTO\Serializer;

class ContentNegotiator{
    public static function processRequest($contentType,$content){
        $type=new ContentType($contentType);
        $body=$type->parseFrom($content);
        return $body;
    }
    public static function processResponse(RequestInterface $req,ResponseInterface $res){
        $type=new ContentType($req->getAccept());
        $res->setHeader("Content-Type",ContentType::TYPES[$type->getType()]);
        return $type->parseTo(Serializer::serialize($res->getContent()));
    }
}