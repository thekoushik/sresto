<?php
namespace SRESTO\MIMEs;
use SRESTO\Request\RequestInterface;
use SRESTO\Response\ResponseInterface;
use SRESTO\MIMEs\MIMEType as ContentType;
use SRESTO\DTO\Normalizer;
use SRESTO\Common\Event;

class ContentNegotiator{
    const PreProcessResponse="PreProcessResponse";
    public static function processRequest($contentType,$content){//deserialize
        $type=new ContentType($contentType);
        $body=$type->parseFrom($content);
        return $body;
    }
    public static function processResponse(RequestInterface $req,ResponseInterface $res){//serialize
        Event::dispatch(self::PreProcessResponse,$res);
        $type=new ContentType($req->getAccept());
        $res->setHeader("Content-Type",ContentType::TYPES[$type->getType()]);
        return $type->parseTo(Normalizer::normalize($res->getContent()));
    }
}