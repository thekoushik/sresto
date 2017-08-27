<?php
namespace SRESTO\Middleware;
interface Middleware{
    public function run($req,$res);
}