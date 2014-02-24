<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 1/31/14
 * Time: 5:15 PM
 */

class WPPlgnzrDataScraper {
  public function __construct(){
    if(isset($this->cookie_jar_path)){
      if(!is_dir($this->cookie_jar_path))
        mkdir($this->cookie_jar_path,0777);
      define('WPPLGNZR_'.strtoupper($this->network).'_COOKIE_FILE',$this->cookie_jar_path.'cookie.txt');
    }
    if(!defined('WPPLGNZR_'.strtoupper($this->network).'_COOKIE_FILE'))
      define('WPPLGNZR_'.strtoupper($this->network).'_COOKIE_FILE',dirname(__FILE__));
    #die(WPPLGNZR_ADDON_COOKIE_FILE);
  }
  public function add_network($apis){
    $apis = array_merge($apis,array($this->network=>array(
                               'label'=>$this->network_name,
                               'fetch_method'=>$this->fetch_method,
                               'url'=>$this->url,
                               'user_methods'=>$this->user_methods,
                               'methods'=>$this->methods,
                               'instance'=>$this
                              )
                            ));
    return $apis;
  }
  public function parse_html($html,$path){
    //not needed yet...
  }
  public function set_url($method,$args=array(),$post=false){
    $req_method = ($post)?'post':'get';
    if(isset($this->methods[$req_method][$method])){
      $method = $this->methods[$req_method][$method];
      $url_base = (isset($method['base']))?
        $method['base']:$this->url_base;
      $this->url = $url_base.$method['m'];
      if(count($args) && $req_method == 'get' && isset($method['r']))
        $this->url = str_replace($method['r'],$args,$this->url);
    }
  }
  public function do_curl($ret_out=true){
    if(isset($this->url)){
      $curl_opts = array(
        CURLOPT_URL=>$this->url,
        CURLOPT_RETURNTRANSFER=>$ret_out
      );
      if(isset($this->curl_headers))
        $curl_opts[CURLOPT_HHTPHEADER] = $this->curl_headers;

      $curl_opts = (isset($this->curl_opts))?
        $curl_opts+$this->curl_opts:$curl_opts;
      $ch = curl_init();
      curl_setopt_array($ch,$curl_opts);
      $resp = curl_exec($ch);
      $info = curl_getinfo($ch);
      $curl_err = curl_error($ch);
      curl_close($ch);
      if($ret_out)
        return array('resp'=>$resp,'info'=>$info,'curl_error'=>$curl_err);
    }
  }
}