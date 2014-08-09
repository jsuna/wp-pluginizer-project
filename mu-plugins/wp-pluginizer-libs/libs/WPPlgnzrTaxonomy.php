<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 1/25/14
 * Time: 8:08 AM
 */
class WPPlgnzrTaxonomy {
  public $settings = array (
    'labels' => array (),
    'hierarchical' =>true,
    'show_ui' => true,
    'show_tagcloud' => true,
    'rewrite' => array(),
    'query_var' => '',
    'public'=>true
  );
  public $tax_name;
  public $taxonomy;
  public $post_type;
  public $post_types = array();
  public function __construct($args = array()){
    if(count($args)){
      extract($args);
      $this->tax_name = $tax_name;
      $this->taxonomy = $taxonomy;
      $this->post_type = $post_type;
    }
    $this->settings['labels'] = array(
      'name' => $this->tax_name,
      'singluar_name' => $this->tax_name,
      'search_items' => "Search $this->tax_name",
      'popular_items' => "Popular $this->tax_name",
      'all_items' => "All $this->tax_name",
      'parent_item' => "Parent $this->tax_name",
      'parent_item_colon' => "Parent $this->tax_name:",
      'edit_item' => "Edit $this->tax_name",
      'update_item' => "Update $this->tax_name",
      'add_new_item' => "Add New $this->tax_name",
      'new_item_name' => "New $this->tax_name"
    );
    $this->settings['rewrite']['slug'] = $this->taxonomy;
    $this->settings['query_var'] = $this->taxonomy;
    add_action('init', array($this,'_register_taxonomy'));
  }
  public function _register_taxonomy(){
    register_taxonomy($this->taxonomy,$this->post_type,$this->settings);
  }
  public function get_terms(){
    global $wpdb;
    $q = "SELECT t.name,t.slug FROM $wpdb->terms t,$wpdb->term_taxonomy tt WHERE ".
      "t.term_id=tt.term_id AND tt.taxonomy='$this->taxonomy'";
    $terms = $wpdb->get_results($q);
    #if($this->taxonomy == 'listing_features') die(print_r($terms));
    $ret = array();
    foreach($terms as $oTerm){
      $ret[$oTerm->slug] = $oTerm->name;
    }
    return $ret;
  }
} 