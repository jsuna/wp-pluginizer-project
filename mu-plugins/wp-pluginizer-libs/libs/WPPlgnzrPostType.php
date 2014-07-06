<?php
class WPPlgnzrPostType {
  public function __construct(){
    add_action( 'init', array($this,'_post_type') );
  }
  public function _post_type(){
    $sing_name = $this->post_type;
    $plur_name = (isset($this->plural_post_type))?$this->plural_post_type:$this->post_type.'s';
    $labels = array(
      'name'=>(isset($this->pt_name))?$this->pt_name:ucwords($plur_name),
      'singular_name'=>$sing_name,
      'add_new'=>'Add New '.$sing_name,
      'add_new_item'=>'Add New '.$sing_name,
      'edit_item'=>'Edit '.$sing_name,
      'new_item'=>'New '.$sing_name,
      'all_items'=>'All '.$plur_name,
      'view_item'=>'View '.$sing_name,
      'search_items'=>'Search '.$plur_name,
      'not_found'=>'No '.$plur_name.' found',
      'not_found_in_trash'=>'No '.$plur_name.' found in the Trash',
      'parent_item_colon'=>'',
      'menu_name'=>(isset($this->pt_name))?$this->pt_name:ucwords($plur_name)
    );
    //allow child classes/plugins to modify...
    $labels = apply_filters('wpplgnzr_post_type_labels',$labels);
    $args = array(
      'labels'=>$labels,
      'description'=>$this->description,
      'public'=>true,
      'menu_position'=>5,
      'capability_type' => 'post',
      'hierarchical' => false,
      'has_archive' => true,
      'rewrite' => array( 'slug' => $sing_name, 'with_front' => false ),
      'supports'=>array( 'title', 'editor', 'thumbnail', 'excerpt' )
    );
    //allow child classes/plugins to modify...
    $args = apply_filters('wpplgnzr_post_type_args',$args);
    register_post_type( strtolower($sing_name), $args );
  }
} 