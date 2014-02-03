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
    #if($this->metabox == 'jvc_listing_metabox_info') { echo '<pre>';print_r($this->metabox_vars);die; }
    if(method_exists($this,'render_metabox')){
      $this->render_metabox();
    }else{
      echo '<table cellspacing="0" cellpadding="0" style="width:100%">';
      foreach($this->fields as $fld=>$data){
        $mbvfld = $post->post_type.'_'.$fld;
        $readonly = (isset($data['readonly']) && $data['readonly'])?' readonly="readonly"':'';
        $style = (isset($data['width']) && !empty($data['width']))?' style="width:'.$data['width'].'"':'';
        switch($data['type']){
          case 'text':
            echo '<tr>'.
              '<td style="width:22%"><label style="font-weight:bold">'.$data['label'].'</label></td>'.
              '<td style="width:78%">'.
              '<input'.$style.$readonly.' type="text" value="'.$this->metabox_vars[$mbvfld].'"
            name="jvc_pt_fields[metabox]['.$post->post_type.']['.$fld.']'.'" /></td>'.
              '</tr>';
            break;
          case 'select':

            echo '<tr>'.
              '<td style="width:22%"><label style="font-weight:bold">'.$data['label'].'</label></td>'.
              '<td style="width:78%">'.
              '<select'.$style.$readonly.' name="jvc_pt_fields[metabox]['.$post->post_type.']['.$fld.']'.'">';
            if(count($data['options']))
              foreach($data['options'] as $v=>$opt){
                $selected = (isset($this->metabox_vars[$mbvfld]) && $this->metabox_vars[$mbvfld] == $v)?'
                selected="selected"':'';
                echo '<option'.$selected.' value="'.$v.'">'.$opt.'</option>';
              }
            echo '</select></td>'.
              '</tr>';
            break;
          case 'checkbox':
            $chked = (isset($this->metabox_vars[$mbvfld]) && $this->metabox_vars[$mbvfld])?' checked="checked"':'';
            echo '<tr>'.
              '<td style="width:22%"><label style="padding-bottom:10px;font-weight:bold">'.$data['label']
              .'</label></td>'.
              '<td style="width:78%">'.
              '<div style="padding-bottom:10px"><input type="checkbox"'.$style.$readonly.$chked.'
              name="jvc_pt_fields[metabox]['
              .$post->post_type.']['
          .$fld.']'
              .'"> '.$data['description'].'</div></td>';
              '</tr>';
            break;
          case 'textarea':
            echo '<tr><td colspan="2"><label style="font-weight:bold">'.$data['label'].'</label></td></tr>'.
              '<tr>'.
              '<td colspan="2">'.
              '<textarea'.$style.$readonly.' name="jvc_pt_fields[metabox]['.$post->post_type.']['.$fld.']'.'">'.
              $this->metabox_vars[$mbvfld].
              '</textarea></td>'.
              '</tr>';
            break;
        }
        echo (isset($data['description']) && $data['type'] != 'checkbox')?
          '<tr><td style="text-align:left;font-size:12px;padding-bottom:10px;padding-left:22%" colspan="2"><address>'
          .$data['description']
          .'</address></td></tr>':'';
      }
      echo '</table>';
    }
    #exit;
  }
  protected function load_metabox_vars(){
    global $post;
    if(count($this->fields)){
      foreach($this->fields as $fld=>$data){
        $val = get_post_meta($post->ID,$post->post_type.'_'.$fld,true);
        if(!trim($val)) continue;
        $pm[$post->post_type.'_'.$fld] = $val;
      }
      $this->metabox_vars = (isset($pm) && is_array($pm))?array_merge($this->metabox_vars,$pm):$this->metabox_vars;
      #if($this->metabox == 'jvc_listing_metabox_info') { echo '<pre>';print_r($this->metabox_vars);die; }
    }
  }
  public function _save_metabox($post_id){
    if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
      ( defined( 'DOING_AJAX' ) && DOING_AJAX ))
      return;
    global $post;
    $to_save = $_POST['jvc_pt_fields']['metabox'][$post->post_type];
    foreach($this->fields as $fld=>$data){
      $val = (isset($to_save[$fld]) && trim($to_save[$fld]))?$to_save[$fld]:'';
      if($data['type'] == 'checkbox')
        $val = true;
      $pm[$post->post_type.'_'.$fld] = $val;
    }

    $pm = array_filter($pm);
    //if($this->metabox == 'jvc_listing_metabox_info') { echo '<pre>';print_r($pm);die; }
    if(count($pm)){
      foreach($pm as $key=>$val)
        update_post_meta($post->ID,$key,$val);
    }
  }
} 