<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2/2/14
 * Time: 8:28 AM
 */

class WPPlgnzrReWrite {
  public $_add_rules = array();
  public $rem_rules = array();
  public $regex = '(.[^/]+)';
  public function __construct(){
    $this->_add_rules = apply_filters('wpplgnzr_rw_rules',$this->_add_rules);
    $this->rem_rules = apply_filters('wpplgnzr_remove_rw_rules',$this->_add_rules);
    if(count($this->rem_rules)){
      $this->parse_rem_rules();
      $this->remove_rules();
    }
    if(count($this->_add_rules)){
      $this->parse_add_rules();
      add_filter('do_parse_request',array($this,'add_rules'));
      add_action('parse_request',array($this,'route'));
    }
  }
  public function parse_rem_rules(){
    foreach($this->rem_rules as $ep=>$params){
      $mcnt = count($params);
      if(!$mcnt) continue;
      foreach($params as $i=>$param){
        $regex = array();
        $pi = 0;
        for($j=$mcnt;$j>$i;$j--){
          $regex[] = $this->regex;
          $pi++;
        }
        $regex = (count($regex)==1)?$regex[0]:join('/',$regex);
        $parsed_ep = str_replace('[REGEX]',$regex,$ep);
        $this->rem_rules[] = $parsed_ep;
      }
      unset($this->rem_rules[$ep]);
    }
    #echo '<pre>';print_r($this->rem_rules);die;
  }
  public function parse_add_rules(){
    //build a pattern for each set of params
    //ep/{REGEX}/{REGEX} //2 params
    //ep/{REGEX} //1 param
    foreach($this->_add_rules as $ep=>$params){
      $mcnt = count($params);
      if(!$mcnt) continue;
      $rules = array();
      foreach($params as $i=>$param){
        $epqry = array();
        $regex = array();
        $pi = 0;
        for($j=$mcnt;$j>$i;$j--){
          $epqry[] = $params[$pi].'=$matches['.($pi+1).']';
          $regex[] = $this->regex;
          $pi++;
        }
        $regex = (count($regex)==1)?$regex[0]:join('/',$regex);
        $parsed_ep = str_replace('[REGEX]',$regex,$ep);
        $parsed_qry = (count($epqry)==1)?$epqry[0]:join('&',$epqry);
        $rules[$parsed_ep] = 'index.php?'.$parsed_qry;
      }
      #echo '<pre>';print_r($rules);die;
      #unset($this->_add_rules[$ep]);
      $this->_add_rules = $rules;
    }
    #echo '<pre>';print_r($this->_add_rules);die;
  }
  public function remove_rules(){
    $rules = get_option('rewrite_rules');
    foreach($this->rem_rules as $regex)
      if(isset($rules[$regex]))
        unset($rules[$regex]);
    #echo '<pre>';print_r($rules);die;
    update_option('rewrite_rules',$rules);
  }
  public function add_rules(){

    $rules = get_option('rewrite_rules');
    $current_rules_regex = array_keys($rules);
    if(!$rules){
      $rules = $this->_add_rules;
    }else{
      $rules = $this->_add_rules+$rules;
    }
    global $wp_rewrite;
    $crCnt = count($current_rules_regex);
    $default_rules = $wp_rewrite->rewrite_rules();
    $merged_rules_regex = array_keys($this->_add_rules+$default_rules);
    $mrgCnt = count($merged_rules_regex);
    //are we adding or removing rules..
    $adding = ($crCnt < $mrgCnt);
    $diff = ($adding)?array_diff($merged_rules_regex,$current_rules_regex):array_diff($current_rules_regex,$merged_rules_regex);
    if(!$adding && count($diff))
      foreach($diff as $regex)
        unset($rules[$regex]);
    #echo '<pre>Adding: '.$adding.'<br>';die(print_r($rules));
    update_option('rewrite_rules',$rules);
    return true;
  }
  public function route($wp_query){
    if(isset($_REQUEST['wpplgnzr_rule_editor'])){
      $this->rule_editor();
      exit;
    }

    if(method_exists($this,'_route')){
      parse_str($wp_query->matched_query,$qry);
      $this->_route($qry);
    }else{
      wp_die(__CLASS__.' needs to extended and the child class must define a _route methed..');
    }
  }
  public function rule_editor(){
    global $wp_rewrite;
    //do we need this??
    #echo '<pre>';die(print_r($current_rules));
  }
}