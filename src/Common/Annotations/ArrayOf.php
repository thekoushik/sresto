<?php
namespace SRESTO\Common\Annotations;
final class ArrayOf implements Annotation{
    /**
     * class name
     *
     * @var string Class name
     */
    public $name;
    /**
     * array dimention
     *
     * @var int
     */
    public $dimention=1;
}