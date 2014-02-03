<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 1/27/14
 * Time: 10:11 AM
 */
class WPPlgnzrDebugLog {
  public $lg = false;
  public function start($filename=false){
    $filename = ($filename)?$filename:ABSPATH.'/wp_content/uploads/debug_log_'.date('Y-m-d').'.txt';
    $this->lg = @fopen($filename,'a+');
    if(!is_resource($this->lg)) wp_die("$filename could not be opened. Check permissions!");
    fwrite($this->lg,"\n-------------start: ".date('H:i:s')."-----------------\n");
  }
  public function log($what,$quick=false){
    if(!is_resource($this->lg)) return;
    if($quick) $this->start();
    fwrite($this->lg,$what."\n");
    if($quick) $this->end();
  }
  public function end(){
    if(!is_resource($this->lg)) return;
    fwrite($this->lg,"\n--------------------- end -------------------\n");
    fclose($this->lg);
  }
}
$GLOBALS['wpplgnzr_debug_log'] = new WPPlgnzrDebugLog;