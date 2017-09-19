<?php
namespace SRESTO\Processors;
interface RequestProcessor{
    public function process($req,$res);
}