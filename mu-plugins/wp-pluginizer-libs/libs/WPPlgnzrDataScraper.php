<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 1/31/14
 * Time: 5:15 PM
 */
if(!session_id()) session_start();
class WPPlgnzrMultiCurl {
  public $batches = array(); //contains our urls to process
  public $batch_size = 10; //number of urls to process at once..NOTE: still governed by $max_reqs
  public $max_reqs = 5; //max number of connections at any given time..
  public $req_urls = array(); //all our urls
  public $requrls = array();  //current batch
  public $chandles = array(); //curl handles to init/exec..
  public $req_map = array();  //chandle/url relationship map...
  public function set_batches(){
    unset($_SESSION['wpplginzr_mcurl_batches']);
    if(!isset($_SESSION['wpplginzr_mcurl_batches']))
      $_SESSION['wpplginzr_mcurl_batches'] = (count($this->req_urls) > $this->batch_size)?
        array_chunk($this->req_urls,$this->batch_size):array($this->req_urls);
    #echo '<pre>';die(print_r($_SESSION['wpplginzr_mcurl_batches']));
  }
  public function set_batch(){
    if(count($_SESSION['wpplginzr_mcurl_batches']))
      $this->requrls = array_shift($_SESSION['wpplginzr_mcurl_batches']);
    #echo '<pre>';print_r($this->requrls);die(print_r($_SESSION['wpplginzr_mcurl_batches']));
  }
  function create_curl_handles(){
    $this->set_batch();
    if(count($this->requrls)){
      foreach($this->requrls as $ui=>$requrl){
        $ch = curl_init();
        if(is_resource($ch)){
          curl_setopt($ch,CURLOPT_URL,$requrl);
          curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
          #curl_setopt($ch, CURLOPT_WRITEFUNCTION,array($this,"progress_function"));
          $this->chandles[] = $ch;
          $key = (string) $ch;
          $this->reqmap[$key] = $ui;
        }
      }
    }
    unset($ch);
    #echo '<pre>';print_r($this->chandles);die(print_r($this->reqmap));
  }
  function multi_curl(){
    /*
			Initialize curl_multi handle -
			loop up to '$winsize' times, and add a curl handle from $chandles
			- this is our 'rolling window' meaning
			 only $winsize parrallel requests at a time are ever active.
			*/
    $mh = curl_multi_init();
    for($i=0;$i<$this->max_reqs;$i++){
      $chandle = array_shift($this->chandles);
      if(is_resource($chandle))
        curl_multi_add_handle($mh, $chandle);
      if($i == count($this->chandles)) break;
    }
    /** Start curling.. */
    $running = null;
    do{
      while(($execrun = curl_multi_exec($mh, $running)) == CURLM_CALL_MULTI_PERFORM);
      if($execrun != CURLM_OK)
        break;
      usleep(1000);
      // check for finished requests.. call the callback function and que up another request if we have any left..
      while ($done = curl_multi_info_read($mh)) {
        if(is_resource($done['handle'])){
          // get the info and content returned on the request
          $info = curl_getinfo($done['handle']);
          $output = curl_multi_getcontent($done['handle']);
          // send the return values to the callback function.
          $callback = array($this,'mcurl_callback');
          $this->finishcnt++;
          if(is_callable($callback)) {
            $key = (string) $done['handle'];
            $key = $this->reqmap[$key];
            $request = $this->requrls[$key];
            unset($this->requrls[$key]);
            unset($this->reqmap[$key]);
            call_user_func($callback, $output, $info, $request, count($this->requrls),
                           count($_SESSION['wpplginzr_mcurl_batches']));
          }
          // remove the curl handle that just completed
          curl_multi_remove_handle($mh, $done['handle']);
          // add the next curl handle if any are left..
          if (count($this->chandles)){
            $chandle = array_shift($this->chandles);
            if(is_resource($chandle)){
              curl_multi_add_handle($mh, $chandle);
              $key = (string) $chandle;
              $this->reqmap[$key] = $i;
              $i++;
            }
          }
        }
      }
    }while($running > 0);
    curl_multi_close($mh);
  }
  public function mcurl_callback($output, $info, $request, $finishcnt, $batchcnt){
    if(method_exists($this,'process_mcurl_resp'))
      $this->process_mcurl_resp(compact('output', 'info', 'request', 'finishcnt', 'batchcnt'));
  }
}
class WPPlgnzrDataScraper extends WPPlgnzrMultiCurl {
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
