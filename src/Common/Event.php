<?php
namespace SRESTO\Common;
use SRESTO\Exceptions\SRESTOException;
use SRESTO\Tools\Logger;

abstract class Event{
    protected static $events=[];

    public static function addListener($eventName,\Closure $listener){
        if(!isset(self::$events[$eventName]))
            self::$events[$name]=['listeners'=>[]];
        self::$events[$eventName]['listeners'][]=$listener;
    }
    public static function removeListener($eventName,\Closure $listener){
        if(isset(self::$events[$eventName]))
        $len=count(self::$events[$eventName]['listeners']);
        for($i=0;$i<$len;$i++)
            if(self::$events[$eventName]['listeners'][$i]===$listener){
                unset(self::$events[$eventName]['listeners'][$i]);
                return true;
            }
        return false;
    }
    public static function dispatch($eventName,$data=null){
        if(isset(self::$events[$eventName]))
            foreach(self::$events[$eventName]['listeners'] as $listener){
                try{
                    call_user_func($listener,$data);
                }catch(\Exception $ex){
                    Logger::error($ex->getTraceAsString());
                }
            }
    }
}