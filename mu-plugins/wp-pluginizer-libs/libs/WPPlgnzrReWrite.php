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
  public function __construct(){
    if(count($this->_add_rules)){
      add_action('parse_request',array($this,'route'));
      add_filter('generate_rewrite_rules', array($this,'add_rules'));
    }
  }
  public function add_rules($wp_rewrite){
    $wp_rewrite->rules = $this->_add_rules + $wp_rewrite->rules;
  }
  public function route($wp_query){
    if(method_exists($this,'_route')){
      parse_str($wp_query->matched_query,$qry);
      $this->_route($qry);
    }else{
      wp_die(__CLASS__.' needs to extended and the child class must define a _route methed..');
    }
  }
} 