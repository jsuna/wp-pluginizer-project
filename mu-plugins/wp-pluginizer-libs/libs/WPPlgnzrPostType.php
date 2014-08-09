<?php
class WPPlgnzrPostType {
  public function __construct(){
    add_action( 'init', array($this,'_post_type') );
    add_filter( 'post_updated_messages', array($this,'_post_type_messages') );
  }
  public function _post_type(){
    $sing_name = $this->post_type;
    $plur_name = (isset($this->plural_post_type))?$this->plural_post_type:$this->post_type.'s';
    $labels = array(
      'name'=>(isset($this->pt_name))?$this->pt_name:ucwords($plur_name),
      'singular_name'=>ucwords($sing_name),
      'add_new'=>'Add New '.ucwords($sing_name),
      'add_new_item'=>'Add New '.ucwords($sing_name),
      'edit_item'=>'Edit '.ucwords($sing_name),
      'new_item'=>'New '.ucwords($sing_name),
      'all_items'=>'All '.ucwords($plur_name),
      'view_item'=>'View '.ucwords($sing_name),
      'search_items'=>'Search '.ucwords($plur_name),
      'not_found'=>'No '.ucwords($plur_name).' found',
      'not_found_in_trash'=>'No '.ucwords($plur_name).' found in the Trash',
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
  public function _post_type_messages($messages){
    global $post, $post_ID;
    $sing_name = ucwords($this->post_type);
    $messages[$this->post_type] = array(
        0 => '',
        1 => $sing_name.' updated. <a href="'.esc_url( get_permalink($post_ID) ).'">View '.$sing_name.'</a>',
        2 => 'Custom field updated.',
        3 => 'Custom field deleted.',
        4 => $sing_name.' updated.',
        5 => isset($_GET['revision']) ? $sing_name.' restored to revision from '.wp_post_revision_title( (int) $_GET['revision'], false ) : false,
        6 => $sing_name.' published. <a href="'.esc_url( get_permalink($post_ID) ).'">View '.$sing_name.'</a>',
        7 => $sing_name.' saved.',
        8 => $sing_name.' submitted. <a target="_blank" href="'.esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) .'">Preview '.$sing_name.'</a>',
        9 => $sing_name.' scheduled for: <strong>'.date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)).'</strong>. <a target="_blank" href="'.esc_url(get_permalink($post_ID)).'">Preview '.$sing_name.'</a>',
        10 => $sing_name.' draft updated. <a target="_blank" href="'.esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ).'">Preview '.$sing_name.'</a>'
    );
    return apply_filters('wpplgnzr_post_type_messages',$messages);
  }
} 