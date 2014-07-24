<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2/24/14
 * Time: 5:23 PM
 */

class WPPlgnzrForm {
  public $action = '';
  public $method = 'get';
  public $html = 'form';
  public $fld_format_indxs = array(
    '[L]'=>'%1$s',
    '[F]'=>'%2$s',
    '[D]'=>'%3$s',
    '[OFL]'=>'%4$s'
  );
  public $fields = array();
  public function __construct($args=array()){
    extract($args);
    $this->action = (isset($action))?$action:$this->action;
    $this->method = (isset($method))?$method:$this->method;
    $this->html   = (isset($html))?$html:$this->html;
    $this->fields = (isset($fields))?$fields:$this->fields;
    $this->id = (isset($id))?' id="'.$id.'"':'';
  }
  public function start(){
    $form = '<form'.$this->id.' action="'.$this->action.'" method="'.$this->method.'">';
    return apply_filters('wpplnzr_form_start',$form);
  }
  public function end(){
    $form = '</form>';
    return apply_filters('wpplnzr_form_end',$form);
  }
  public function form_body(){
    #echo '<pre>';print_r($this->fields);die;
    if(count($this->fields)){
      foreach($this->fields as $fld=>$data){
        if($data['type'] == 'group'){
          $class = (isset($data['class']) && !empty($data['class']))?' class="'.$data['class'].'"':'';
          $fields[] = '<fieldset id="'.$fld.'"'.$class.'><legend><strong>'.$data['label'].'</strong></legend>';
          if(count($data['dependants'])){
            foreach($data['dependants'] as $dep_fld){
              $dbug[] = $dep_fld.' :: '.(isset($this->fields[$dep_fld]));
              if(isset($this->fields[$dep_fld])){
                $dep_fld_data = $this->fields[$dep_fld];
                $fields[] = $this->field($dep_fld,$dep_fld_data);
              }
            }
          }
          $fields[] = '</fieldset>';
          #echo '<pre>';print_r($dbug);die;
        }else{
          if(!isset($data['parent']))
            $fields[] = $this->field($fld,$data);
        }
      }
      #echo '<pre>';print_r($fields);die;
      return (count($fields))?(count($fields)==1)?$fields[0]:join("\n",$fields):false;
    }
    return false;
  }
  public function form($ret = false){
    if(!$ret){
      echo $this->start();
      #echo '<pre>';print_r($this->fields);die;
      echo $this->form_body();
      echo $this->end();
    }else{
      return $this->start().
             $this->form_body().
             $this->end();
    }

  }
  public function field($fld,$data,$opts=array(),$fld_base=false){
    $opts = apply_filters('wpplgnzr_form_field_vals',$opts);

    global $plugin_page;
    $fldname = (!$plugin_page)?(!$fld_base)?$fld:$fld_base.'['.$fld.']':$plugin_page.'['.$fld.']';
    $fldname = ' name="'.$fldname.'"';
    $readonly = (isset($data['readonly']) && $data['readonly'])?' readonly="readonly"':'';
    $style = (isset($data['width']) && !empty($data['width']))?' style="width:'.$data['width'].'"':'';
    $id = (isset($data['id']) && !empty($data['id']))?' id="'.$data['id'].'"':'';
    $class = (isset($data['class']) && !empty($data['class']))?' class="wpplgnzr-'.$data['type'].'-field '.
      $data['class'].'"':' class="wpplgnzr-'.$data['type'].'-field"';
    if(!isset($data['type']))
      wp_die(__CLASS__.' Error: doing it wrong... no field type defined... for '.$fld);
    $type = ' type="'.$data['type'].'"';
    $desc = (isset($data['description']))?$data['description']:'';
    $tag = (isset($data['tag']))?$data['tag']:'div';
    $pholder = (isset($data['placeholder']) && !empty($data['placeholder']))?
      ' placeholder="'.$data['placeholder'].'"':'';
    switch($data['type']){
      case 'text':
        #echo '<pre>';print_r($opts[$fld]);die;
        $fldval = (isset($opts[$fld]))?$opts[$fld]:'';
        $fldval = (isset($data['value']) && !trim($fldval))?$data['value']:$fldval;
        $value = ' value="'.$fldval.'"';
        $field = array(
          'label'=>'<label class="wpplgnzr-form-label">'.$data['label'].'</label>',
          'field'=>"<input{$id}{$class}{$style}{$readonly}{$type}{$fldname}{$value}{$pholder} />",
          'desc'=>(!empty($desc))?'<div class="wpplgnzr-field-description">'.$desc.'</div>':'',
          'format'=>(isset($data['format']) && !empty($data['format']))?$data['format']:'[L][F]<br>[D]<br>'
        );
        break;
      case 'textarea':
        $fldval = (isset($opts[$fld]))?$opts[$fld]:'';
        $fldval = (isset($data['value']) && !trim($fldval))?$data['value']:'';
        $field = array(
          'label'=>'<label class="wpplgnzr-form-label">'.$data['label'].'</label>',
          'field'=>"<textarea{$id}{$class}{$style}{$readonly}{$type}{$fldname}{$pholder}>{$fldval}</textarea>",
          'desc'=>(!empty($desc))?'<div class="wpplgnzr-field-description">'.$desc.'</div>':'',
          'format'=>(isset($data['format']) && !empty($data['format']))?$data['format']:'[L]<br>[F]<br>[D]<br>'
        );
        break;
      case 'radio_select':
        $type = ' type="radio"';
        if(count($data['options'])){
          foreach($data['options'] as $chkopt=>$label){
            $chked = (isset($opts[$fld][$chkopt]))?' checked="checked"':'';
            $fldname = (!$plugin_page)?$fld:$plugin_page.'['.$fld.']';
            $fldname = ' name="'.$fldname.'"';
            $fldval = ' value="'.$chkopt.'"';
            $field['options_field'][$data['label']]['format'] = (isset($data['format']))?$data['format']:'[OFL]<br>[OFO]<br>[D]<br>';
            $field['options_field'][$data['label']]['desc'] = (isset($data['description']))?$data['description']:'';
            $field['options_field'][$data['label']]['opts'][] = array(
              'label'=>'<label class="wpplgnzr-form-label">'.$label.'</label>',
              'field'=>"<input{$id}{$class}{$style}{$readonly}{$type}{$fldname}{$fldval} />",
              'format'=>(isset($data['option_format']) && !empty($data['option_format']))?$data['option_format']:'[F][L]<br>'
            );
          }
        }
        #die(print_r($field));
        break;
      case 'radio':
      case 'checkbox':
        #die();
        $chked = (isset($opts[$fld]) && $opts[$fld])?' checked="checked"':'';

        $field = array(
          'label'=>'<label class="wpplgnzr-form-label">'.$data['label'].'</label>',
          'field'=>"<input{$id}{$class}{$style}{$readonly}{$type}{$fldname}{$chked} />",
          'desc'=>(!empty($desc))?'<div class="wpplgnzr-field-description">'.$desc.'</div>':'',
          'format'=>(isset($data['format']) && !empty($data['format']))?$data['format']:'[F][L]<br>[D]<br>'
        );
        break;
      case 'checkbox_select':
        $type = ' type="checkbox"';
        if(count($data['options'])){
          foreach($data['options'] as $chkopt=>$label){
            $chked = (isset($opts[$fld][$chkopt]))?' checked="checked"':'';
            $fldname = (!$plugin_page)?$fld:$plugin_page.'['.$fld.']';
            $fldname = ' name="'.$fldname.'"';
            $fldval = ' value="'.$chkopt.'"';
            $field['options_field'][$data['label']]['format'] = (isset($data['format']))?$data['format']:'[OFL]<br>[OFO]<br>[D]<br>';
            $field['options_field'][$data['label']]['desc'] = (isset($data['description']))?$data['description']:'';
            $field['options_field'][$data['label']]['opts'][] = array(
              'label'=>'<label class="wpplgnzr-form-label">'.$label.'</label>',
              'field'=>"<input{$id}{$class}{$style}{$readonly}{$type}{$fldname}{$fldval} />",
              'format'=>(isset($data['option_format']) && !empty($data['option_format']))?$data['option_format']:'[F][L]<br>'
            );
          }
        }
        break;
      case 'select':
        $opts = '';
        if(count($data['options'])){
          foreach($data['options'] as $selopt=>$label){
            $selected = (isset($opts[$fld]) && $opts[$fld] == $selopt)?' selected="selected"':'';
            $opts .= '<option'.$selected.' value="'.$selopt.'">'.$label.'</option>';
          }
        }
        $field = array(
          'label'=>'<label class="wpplgnzr-form-label">'.$data['label'].'</label>',
          'field'=>"<select{$id}{$class}{$style}{$readonly}{$type}{$fldname}>{$opts}</select>",
          'desc'=>(!empty($desc))?'<div class="wpplgnzr-field-description">'.$desc.'</div>':'',
          'format'=>(isset($data['format']) && !empty($data['format']))?$data['format']:'[L][F]<br>[D]<br>'
        );
        break;
      case 'reset':
      case 'submit':
        $readonly = (isset($data['readonly']) && $data['readonly'])?' disabled="disabled"':'';
        $fldname = (isset($data['label']) && !empty($data['label']))?' value="'.$data['label'].'"':'Submit';
        $field = array(
          'label'=>'',
          'field'=>"<input{$id}{$class}{$style}{$readonly}{$type}{$fldname} />",
          'desc'=>'',
          'format'=>(isset($data['format']))?$data['format']:'<br>[F]<br>'
        );
        break;
      case 'hidden':
        $fldval = (isset($data['value']))?$data['value']:'';
        $value = ' value="'.$fldval.'"';
        $field = array(
          'label'=>'',
          'field'=>"<input{$type}{$fldname}{$value} />",
          'desc'=>'',
          'format'=>(isset($data['format']))?$data['format']:'[F]<br>'
        );
        break;
      case 'html':
        #die('here');
        $field = array(
          'label'=>'',
          'field'=>(!trim($tag))?$desc:'<'.$tag.$id.$class.'>'.$desc.'</'.$tag.'>',
          'desc'=>'',
          'format'=>(isset($data['format']))?$data['format']:'[F]'
        );

        break;
    }// end switch $data['type']

    $ff_tokes = array_keys($this->fld_format_indxs);
    $ff_reps = array_values($this->fld_format_indxs);
    if(isset($field['options_field'])){
      $fld = '';
      foreach($field['options_field'] as $label=>$data){
        if(count($data['opts'])){
          foreach($data['opts'] as $d){
            $format = str_replace($ff_tokes,$ff_reps,$d['format']);
            $fld .= sprintf($format, $d['label'],$d['field']);
          }
        }
        $fmt = str_replace('[OFO]',$fld,$data['format']);
        $fmt = str_replace($ff_tokes,$ff_reps,$fmt);
        $ret_field[] = sprintf($fmt, '','',$data['desc'],$label);
      }
    }else{
      $format = str_replace($ff_tokes,$ff_reps,$field['format']);
      #echo '<pre>';die(print_r($format));
      $ret_field = sprintf($format, $field['label'],$field['field'],$field['desc']);
    }
    return (is_array($ret_field) && count($ret_field))?(count($ret_field)==1)?
      $ret_field[0]:join("\n",$ret_field):$ret_field;
  }
}