<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 1/23/14
 * Time: 5:15 PM
 */

class WPPlgnzrWPAdmin {
  /**
   * @var array $menu_pages = array(
   *  This will create 'Main Menu' pages,
   *   who's link/s will show in the dashboard sidebar
      'main'=>array(
        array(
          'page_title'=>'Testing',
          'menu_text'=>'Testing',
          'capability'=>'manage_options',
          'menu_slug'=>'testing',
          'icon_url'=>'',
          'position'=>101
        ),
        etc...repeat above for each page you need to add
      ),
   * This will create sub menu page/s...
      'sub'=>array(
        array(
          'slug'=>'edit.php?post_type=some-post-type',
          'page_title'=>'Some Post Type Settings',
          'menu_text'=>'Some Settings',
          'capability'=>'edit_posts',
          'menu_slug'=>'some-post-type-settings'
        ),
        etc...repeat above for each page you need to add..
      )
    );
   */
  public $menu_pages = array();
  public $page_renderer = array();
  public function __construct(){
    $this->page_renderer = array($this,'_render_settings');
    add_action('admin_menu',array($this,'_admin_menu'));
  }
  public function _admin_menu(){
    //main menu page support here..
    if(isset($this->menu_pages['main']) && count($this->menu_pages['main'])){
      foreach($this->menu_pages['main'] as $page_args){
        extract($page_args);
        $ph = add_menu_page($page_title,$menu_text,$capability,$menu_slug,$this->page_renderer,$icon_url,$position);
        //add js/css to the pages here...??
      }
    }
    if(isset($this->menu_pages['sub']) && count($this->menu_pages['sub'])){
      foreach($this->menu_pages['sub'] as $page_args){
        extract($page_args);
        $ph = add_submenu_page($slug,$page_title,$menu_text,$capability,$menu_slug,$this->page_renderer);
        //add js/css to the pages here...??
      }
    }
  }
  //@override - override this function in child classes to customize..
  public function _render_settings(){
    $jvcen_save_msg = $this->_maybe_save_opts();
    do_action('jvcwpa_pre_admin_page');
    global $title; ?>
    <div class="wrap">
      <h1><?php echo $title; ?></h1>
      <?php echo $jvcen_save_msg; ?>
      <form action="" method="post">
        <?php $this->_form(); ?>
      </form>
    </div>
    <?php
    do_action('jvcwpa_after_admin_page');
  }
  public function get_options(){
    global $plugin_page;
    //@define $options_name in child classes to use..
    $this->options_name = (!isset($this->options_name))?
      'jvcwpa_'.$plugin_page.'_options':$this->options_name;
    $opts = get_option($this->options_name);
    if(is_array($opts) && isset($opts[$plugin_page]))
      $opts = $opts[$plugin_page];
    return (!$opts)?array():$opts;
  }
  public function _maybe_save_opts(){
    global $plugin_page;
    #die(print_r($_POST));
    if(isset($_POST['jvcwpa_form_submit'])){
      $opts = get_option($this->options_name);
      $new_opts = (isset($opts[$plugin_page]))?$opts[$plugin_page]:array();
      $post = (isset($_POST[$plugin_page]))?$_POST[$plugin_page]:array();
      #die(print_r($opts));
      $del_dtypes = array(
        'checkbox',
        'checkbox_select',
        'radio'
      );
      foreach($this->fields as $fld=>$data){
        if(isset($new_opts[$fld]) && in_array($data['type'],$del_dtypes)){
          unset($new_opts[$fld]);
        }
        if(isset($post[$fld])){
          $new_opts[$fld] = $post[$fld];
        }
      }
      $opts[$plugin_page] = $new_opts;
      #die(print_r($opts));
      update_option($this->options_name,$opts);
      return '<div id="jvcwpa-settings-msgs" class="updated">Options Saved!</div>'.
      '<script type="text/javascript">setTimeout(function(){
        var msg = document.getElementById("jvcwpa-settings-msgs");
        msg.remove();
      },2500);</script>';
    }
  }
  public function get_field($fld,$data,$opts){
    global $plugin_page;
    $fldname = $plugin_page.'['.$fld.']';
    $readonly = (isset($data['readonly']) && $data['readonly'])?' readonly="readonly"':'';
    $style = (isset($data['width']) && !empty($data['width']))?' style="width:'.$data['width'].'"':'';
    $id = (isset($data['id']) && !empty($data['id']))?' id="'.$data['id'].'"':'';
    $class = (isset($data['class']) && !empty($data['class']))?' class="'.$data['class'].'"':'';
    switch($data['type']){
      case 'text':
        $fldval = (isset($opts[$fld]))?$opts[$fld]:'';
        echo '<tr>'.
          '<td style="width:22%"><label style="font-weight:bold">'.$data['label'].'</label></td>'.
          '<td style="width:78%">'.
          '<input'.$id.$class.$style.$readonly.' type="text" value="'.$fldval.'" name="'.$fldname.'" /></td>'.
          '</tr>';
        break;
      case 'textarea':
        $fldval = (isset($opts[$fld]))?$opts[$fld]:'';
        echo '<tr><td colspan="2"><label style="font-weight:bold">'.$data['label'].'</label></td></tr>'.
          '<tr>'.
          '<td colspan="2">'.
          '<textarea'.$id.$class.$style.$readonly.' name="'.$fldname.'">'.$fldval.'</textarea></td>'.
          '</tr>';
        break;
      case 'checkbox':
        $chked = (isset($opts[$fld]))?' checked="checked"':'';
        echo '<tr>'.
          '<td colspan="2">'.
          '<input type="checkbox"'.$id.$class.$style.$readonly.$chked.' name="'.$fldname.'" /> '.
          '<label>'.$data['label'].'</label></td>'.
          '</tr>';
        break;
      case 'checkbox_select':
        echo '<tr><td colspan="2"><label style="font-weight:bold">'.$data['label'].'</label></td></tr>';
        if(count($data['options'])){
          foreach($data['options'] as $chkopt=>$label){
            $chked = (isset($opts[$fld][$chkopt]))?' checked="checked"':'';
            $fldname = $plugin_page.'['.$fld.']['.$chkopt.']';
            echo '<tr>'.
              '<td colspan="2">'.
              '<input type="checkbox"'.$id.$class.$style.$readonly.$chked.' name="'.$fldname.'" /> '.
              '<label>'.$label.'</label></td>'.
              '</tr>';
          }
        }
        break;
      case 'select':
        echo '<tr><td colspan="2"><label style="font-weight:bold">'.$data['label'].'</label></td></tr>';
        echo '<tr>'.
          '<td colspan="2">';
        echo '<select'.$id.$class.$style.$readonly.' name="'.$fldname.'">';
        if(count($data['options'])){
          foreach($data['options'] as $selopt=>$label){
            $selected = (isset($opts[$fld]) && $opts[$fld] == $selopt)?' selected="selected"':'';
              echo '<option'.$selected.' value="'.$selopt.'">'.$label.'</option>';
          }
        }
        echo '</select></td></tr>';
        break;
    }
    $padding = ($data['type'] == 'textarea' || $data['type'] == 'checkbox_select')?'':'padding-left:22%';
    echo (isset($data['description']) && $data['type'] != 'checkbox')?
      '<tr><td style="text-align:left;font-size:12px;padding-bottom:10px;'.$padding.'" colspan="2"><address>'
      .$data['description']
      .'</address></td></tr>':'';
  }
  public function _form(){
    global $plugin_page;
    $opts = $this->get_options();
    #die(print_r($opts));
    if(isset($this->fields) && count($this->fields)){
      echo '<table cellspacing="0" cellpadding="0" style="width:60%;margin:0 20px">';
      foreach($this->fields as $fld=>$data){
        if($data['type'] == 'group'){
          echo '<tr><td colspan="2">';
          echo '<fieldset><legend><strong>'.$data['label'].'</strong></legend>';
          echo '<table style="width:100%" cellspacing="0">';
          if(count($data['dependants'])){
            foreach($data['dependants'] as $dep_fld){
              if(isset($this->fields[$dep_fld])){
                $dep_fld_data = $this->fields[$dep_fld];
                $this->get_field($dep_fld,$dep_fld_data,$opts);
              }
            }
          }
          echo '</table>';
          echo '</fieldset>';
          echo '</td></tr>';
        }else{
          $this->get_field($fld,$data,$opts);
        }
      }

      echo '<tr><td style="text-align:left;font-size:12px;padding-bottom:10px;" colspan="2">'
        .'<input class="button button-primary" type="submit" name="jvcwpa_form_submit" value="Save Options" />'
        .'</td></tr>';
      echo '</table>';
    }else{
      echo 'No fields defined in '.get_class($this);
    }
  }
} 