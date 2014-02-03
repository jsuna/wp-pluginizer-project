<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 1/27/14
 * Time: 12:42 PM
 */
//if(class_exists('thesis_box')){
  class WPPlgnzrThesisBox extends thesis_box {
    public $_html_opts = array('div','section');
    public $_html_def = 'div';
    public function __construct($box = array()) {
      parent::__construct($box);
    }
    protected function translate() { $this->title = $this->name = __($this->_name, $this->_class); }

    protected function html_options() {
      global $thesis;
      $this->_html_opts = (isset($this->hmtl_opts))?
        array_merge($this->_html_opts,$this->html_opts):$this->_html_opts;
      $this->_html_def = (isset($this->html_def))?
        $this->html_def:$this->_html_def;
      $html_opts = $thesis->api->html_options($this->_html_opts, $this->_html_def);
      return $html_opts;
    }
  }
//}
