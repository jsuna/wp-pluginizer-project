<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 1/31/14
 * Time: 9:08 AM
 */
/** testing */
class WPPlgnzrFileLoader {
  public static function load($files){
    #die(print_r($files));
    if(count($files)){
      foreach($files as $loadFilePath){
        $the_files = glob($loadFilePath.'*.php');
        if(count($the_files)){
          foreach($the_files as $loadFile){
            if(class_exists(str_replace('.php','',basename($loadFile)))) continue;
            #die(print_r(file_exists($loadFile)));
            if(file_exists($loadFile)){
              include($loadFile);
            }else{
              return false;
            }
          }
        }
      }
    }
    return true;
  }
}
