<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 1/22/14
 * Time: 9:20 AM
 */

class WPPlgnzrMetaBox {
  public $post_types = array('page'=>array('context'=>'normal','priority'=>'core'));
  public $metabox = '';
  public $metabox_label = '';
  public $metabox_vars = array();
  public $fields = array();
  public $post = false;
  public function __construct(){

    add_action('add_meta_boxes',array($this,'_add_meta_box'),0);
    if(method_exists($this,'get_post_data'))
      add_action('admin_head',array($this,'get_post_data'));
    add_action('save_post',array($this,'_save_metabox'),1,2 );
  }
  public function _add_meta_box(){
    //if($this->metabox == 'jvc_listing_metabox_info') die(method_exists($this,'_render_metabox'));
    #die(print_r($this->post_types));
    foreach($this->post_types as $post_type=>$data){
      add_meta_box(
        $this->metabox
        ,$this->metabox_label
        ,array( $this, '_render_metabox' )
        ,$post_type
        ,$data['context']
        ,$data['priority']
      );
    }
  }

  public function _render_metabox(){
    global $post;
    $this->load_metabox_vars();
    if(method_exists($this,'render_metabox'))
      $this->render_metabox();
  }
  protected function load_metabox_vars(){
    global $post;
    $fields = apply_filters('wppglgnzr_save_fields',$this->fields);
    if(count($fields)){
      $pc = get_post_custom($post->ID);
      foreach($fields as $fld=>$data){
        $val = $pc[$fld][0];
        $val = (!unserialize($val))?$val:unserialize($val);
        if(!trim($val)) continue;
        $pm[$fld] = $val;
      }
      if(isset($pc['wpplgnzr_dynamic'])){
        $val = $pc['wpplgnzr_dynamic'][0];
        $val = (!unserialize($val))?$val:unserialize($val);
        $pm['wpplgnzr_dynamic'] = $val;
      }
      $this->metabox_vars = (isset($pm) && is_array($pm))?array_merge($this->metabox_vars,$pm):$this->metabox_vars;
      #echo '<pre>';print_r($this->metabox_vars);die;
      #if($this->metabox == 'jvc_listing_metabox_info') { echo '<pre>';print_r($this->metabox_vars);die; }
    }
  }
  public function _save_metabox($post_id){
    if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
      ( defined( 'DOING_AJAX' ) && DOING_AJAX ))
      return;
    global $post;
    $skip_types = array(
      'group',
      'submit',
      'reset',
      'button',
      'html'
    );
    if(isset($_POST)){
      $to_save = $_POST;
      $fields = apply_filters('wppglgnzr_save_fields',$this->fields);
      #echo '<pre>';print_r($fields);die;
      foreach($fields as $fld=>$data){
        if(in_array($data['type'],$skip_types) || (isset($data['nosave']) && $data['nosave'])){
          if(isset($to_save[$fld]))
            unset($to_save[$fld]);
          continue;
        }

        $val = (isset($to_save[$fld]) && trim($to_save[$fld]))?$to_save[$fld]:'';
        if(isset($to_save[$fld]) && ($data['type'] == 'checkbox' || $data['type'] == 'radio'))
          $val = true;
        $pm[$fld] = $val;
        unset($to_save[$fld]);
      }
      #echo '<pre>';print_r($to_save);die;
      $dynamic_pm = get_post_meta($post->ID,'wpplgnzr_dynamic',true);
      if(isset($to_save['wpplgnzr_dynamic']) && count($to_save['wpplgnzr_dynamic'])){
        //fields that may have been added dynamically..name="dyn[type][fldname]"
        foreach($to_save['wpplgnzr_dynamic'] as $type=>$fldVal){
          if($type == 'checkbox' || $type == 'radio')
            $val = true;
          $fld = array_keys($fldVal);
          $fld = $fld[0];
          $pm['wpplgnzr_dynamic'][$type] = $fldVal;
        }
      }else{
        //no dynamic fields to save, if there are previously saved dynamic fields remove the post meta
        if(is_array($dynamic_pm))
          delete_post_meta($post->ID,'wpplgnzr_dynamic');
      }
      $pm = (is_array($pm) && count($pm))?array_filter($pm):array();
      #echo '<pre>';print_r($pm);die;
      if(count($pm)){
        foreach($pm as $key=>$val)
          update_post_meta($post->ID,$key,$val);
      }
      //do extra saving stuff...maybe save stuff elsewhere as well??
      do_action('wppglgnzr_save_mb',$post->ID,$_POST);
    }

  }
} 