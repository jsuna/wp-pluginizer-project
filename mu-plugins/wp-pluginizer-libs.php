<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 1/23/14
 * Time: 2:52 PM
 */
/** Testings commits */
define('WPPLGNZR_LIBS_DIR',dirname(__FILE__).'/wp-pluginizer-libs/libs/');
define('WPPLGNZR_THESIS_DIR',dirname(__FILE__).'/wp-pluginizer-libs/themes/thesis/');
class WPPlgnzrPluginizerLibs {
  public function __construct(){
    $thesis = wp_get_theme('thesis');
    if($thesis->exists())
      add_action('after_setup_theme',array($this,'load_thesis_helpers'));
  }
  public function load_libs($ext = 'php'){
    if(!class_exists('WPPlgnzrFileLoader'))
      include(WPPLGNZR_LIBS_DIR.'WPPlgnzrFileLoader.php');
    if(!WPPlgnzrFileLoader::load(array(WPPLGNZR_LIBS_DIR)))
      wp_die('JVC-pluginizer-lib ERROR: could not load lib files... ');

  }
  public function load_thesis_helpers(){
    #die('here');
    if(!class_exists('WPPlgnzrFileLoader'))
      include(WPPLGNZR_LIBS_DIR.'WPPlgnzrFileLoader.php');
    if(!WPPlgnzrFileLoader::load(array(WPPLGNZR_THESIS_DIR)))
      wp_die('JVC-pluginizer-lib ERROR: could not load thesis lib files... ');
  }
}
$wpplgnzr_pluginizer_libs = new WPPlgnzrPluginizerLibs;
$wpplgnzr_pluginizer_libs->load_libs();
