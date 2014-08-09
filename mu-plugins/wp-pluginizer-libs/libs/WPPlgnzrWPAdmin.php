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
  public $ajax_handler = 'wpplgnzr_handle_admin_ajax';
  public function __construct(){
    $this->page_renderer = array($this,'_render_settings');
    add_action('admin_menu',array($this,'_admin_menu'));
    add_action('wp_ajax_'.$this->ajax_handler,array($this,'handle_ajax'));
    add_action('admin_enqueue_scripts',array($this,'load_scripts'),999);
  }
  public function handle_ajax(){
    if(isset($_POST['method'])){
      if(method_exists($this,$_POST['method'])){
        $args = null;
        if(isset($_POST['post']))
          parse_str($_POST['post'],$args);
        $resp = call_user_func(array($this,$_POST['method']),$args);
        echo json_encode($resp);
      }else{
        $msg = __CLASS__.'::'.$_POST['method'].' not found.';
        echo '{"success":false,"msg":"'.$msg.'"}';
      }
      exit;
    }
  }
  public function load_scripts($hook){ ?>
    <script type="text/javascript">
      window.WPPlgnzrWPAdmin = {
        pageHook:'<?php echo $hook; ?>'
      }
    </script>
    <?php
    do_action('wpplgnzr_admin_assets',$hook);
  }
  public function _admin_menu(){
    //main menu page support here..
    if(isset($this->menu_pages['main']) && count($this->menu_pages['main'])){
      foreach($this->menu_pages['main'] as $page_args){
        extract($page_args);
        $ph = add_menu_page($page_title,$menu_text,$capability,$menu_slug,$this->page_renderer,$icon_url,$position);
        //add js/css to the pages here...
        add_action('admin_print_scripts-'.$ph,array($this,'_load_admin_assets'));
      }
    }
    if(isset($this->menu_pages['sub']) && count($this->menu_pages['sub'])){
      foreach($this->menu_pages['sub'] as $page_args){
        extract($page_args);
        $ph = add_submenu_page($slug,$page_title,$menu_text,$capability,$menu_slug,$this->page_renderer);
        //add js/css to the pages here...
        add_action('admin_print_scripts-'.$ph,array($this,'_load_admin_assets'));
      }
    }
  }
  //@override - override this function in child classes to customize..
  public function _load_admin_assets(){}
  //@override - override this function in child classes to customize..
  public function _render_settings(){ }
} 