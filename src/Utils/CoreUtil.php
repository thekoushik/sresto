<?php
namespace SRESTO\Utils;
use Symfony\Component\Yaml\Yaml;
//use Symfony\Component\Yaml\Exception\ParseException;

class CoreUtil{
    public static function isObject($obj){
        return is_array($val)?TRUE:(is_scalar($val)?FALSE:TRUE);
    }
    public static function isAssociativeArray($array){
        $keys=array_keys($array);
        return array_keys($keys)!==$keys;
    }
    public static function scanClasses($root,$path){
        $root=rtrim($root,"/");
        if($path[0]!='/') $path='/'.$path;
        $l=strlen($root)+1;
        return array_map(function($n) use($l){
            $s=explode('.',str_replace('/','\\',substr($n,$l)),2);
            if((count($s)==2) & $s[1]=='php')
            return $s[0];
        },self::scanDirectories($root.$path));
    }
    public static function scanDirectories($rootDir, $allData=[]) {
        // set filenames invisible if you want
        $invisibleFileNames = [".", ".."];//, ".htaccess", ".htpasswd");
        // run through content of root directory
        $dirContent = scandir($rootDir);
        foreach($dirContent as $key => $content) {
            // filter all files not accessible
            $path = $rootDir.'/'.$content;
            if(!in_array($content, $invisibleFileNames)) {
                // if content is file & readable, add to array
                if(is_file($path) && is_readable($path)) {
                    // save file name with path
                    $allData[] = $path;
                // if content is a directory and readable, add path and name
                }elseif(is_dir($path) && is_readable($path)) {
                    // recursive callback to open new directory
                    $allData = self::scanDirectories($path, $allData);
                }
            }
        }
        return $allData;
    }
    public static function parseYML($file){
        return Yaml::parse(file_get_contents($file));
    }
    public static function parseENV($file){
        $lines=explode("\n",file_get_contents($file));
        $envArray=[];
        foreach($lines as $line){
            $line=trim($line);
            if(strlen($line)==0) continue;
            if($line[0]=="#") continue;
            $var=explode("=",$line,2);
            if(count($var)<2) continue;//ignore error
            $envArray[$var[0]]=$var[1];
        }
        return $envArray;
    }
    public static function concatURLs($array){
        $path=[];
        $lastSlash=false;
        foreach($array as $uri){
            if(empty($uri)) $uri="/";
            if($uri=="/"){
                if($lastSlash) continue;
                $lastSlash=true;
            }else{
                $lastSlash=false;
                $path[]=$uri;
            }
        }
        return implode("/",$path);
    }
    public static function parseTemplateString($template,$values){
        foreach($values as $key=>$value)
            $template=str_replace("{".$key."}",$value,$template);
        return $template;
    }
}