<?php
namespace SRESTO\Common\Annotations;
final class RequestMapping implements Annotation{
    /**
     * route name
     *
     * @var string
     */
    public $name=null;
    /**
     * request method
     *
     * @var string
     */
    public $method=null;

    /**
     * request path
     *
     * @var string
     */
    public $path="";

    /**
     * request domain
     *
     * @var string
     */
    public $domain=null;
}