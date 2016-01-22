<?php
/** Metabox helper */

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
  
  public function load_metabox_vars(){
    global $post;
    if(method_exists($this,'get_save_fields')) $fields = $this->get_save_fields();
    
    //do not use filter in child classes
    $fields = apply_filters('wppglgnzr_save_fields',$fields);
    
    if(count($fields)){
      $pc = get_post_custom($post->ID);
      
      foreach($fields as $fld=>$data){
        $val = $pc[$fld][0];
        $val = (!unserialize($val))?$val:unserialize($val);
        if(!is_array($val) && !trim($val)) continue;
        $pm[$fld] = $val;
      }
      
      if(isset($pc['wpplgnzr_dynamic'])){
        $val = $pc['wpplgnzr_dynamic'][0];
        $val = (!unserialize($val))?$val:unserialize($val);
        $pm['wpplgnzr_dynamic'] = $val;
      }
      
      $this->metabox_vars = (isset($pm) && is_array($pm)) ? array_merge($this->metabox_vars,$pm) : $this->metabox_vars;
    }
  }
  
  public function _save_metabox($post_id){
    if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
      ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || !count($this->fields)) return;
      
    if ( !isset($_POST['wpplgnzr_nonce']) || !wp_verify_nonce($_POST['wpplgnzr_nonce'],'wpplgnzr_save_nonce')) return;
    global $post;
    $skip_types = array(
      'group',
      'submit',
      'reset',
      'button',
      'html'
    );
    if(isset($_POST) && isset($this->post_types[$post->post_type])){
        $to_save = $_POST;
        
        if(method_exists($this,'get_save_fields')) $fields = $this->get_save_fields();
        
        //do not use filter in child classes
        $fields = apply_filters('wppglgnzr_save_fields',$fields);
        
        if(count($fields)){
            foreach($fields as $fld=>$data){
                if(in_array($data['type'],$skip_types) || (isset($data['nosave']) && $data['nosave'])){
                    if(isset($to_save[$fld])) unset($to_save[$fld]);
                    continue;
                }
                $val = (isset($to_save[$fld]) && $to_save[$fld])?$to_save[$fld]:'';
                
                if($data['type'] == 'checkbox' || $data['type'] == 'radio'){
                    if(isset($to_save[$fld]) && $to_save[$fld] == 'on'){
                        $val = true;
                    }else{
                        delete_post_meta($post->ID,$fld);
                    }
                }
                $pm[$fld] = $val;
                unset($to_save[$fld]);
            }
            
            /** @var $dynamic_pm - Deprecated, will leave for now for back compat */
            $dynamic_pm = get_post_meta($post->ID,'wpplgnzr_dynamic',true);
            if(isset($to_save['wpplgnzr_dynamic']) && count($to_save['wpplgnzr_dynamic'])){
                //fields that may have been added dynamically..name="dyn[type][fldname]"
                foreach($to_save['wpplgnzr_dynamic'] as $type=>$fldVal){
                    if($type == 'checkbox' || $type == 'radio') $val = true;
                    $fld = array_keys($fldVal);
                    $fld = $fld[0];
                    $pm['wpplgnzr_dynamic'][$type] = $fldVal;
                }
            }else{
                //no dynamic fields to save, if there are previously saved dynamic fields remove the post meta
                if(is_array($dynamic_pm)) delete_post_meta($post->ID,'wpplgnzr_dynamic');
            }
            /** End deprecated dynamic fields */
            $pm = (is_array($pm) && count($pm))?$pm:array();
            if(count($pm)){
                foreach($pm as $key=>$val)
                    update_post_meta($post->ID,$key,$val);
            }
        }
        //do extra saving stuff...maybe save stuff elsewhere as well??
        do_action('wppglgnzr_save_mb',$post->ID,$_POST);
    }
  }
} 