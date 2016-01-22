<?php
/** Form builder */

class WPPlgnzrForm {
  public $action = '';
  
  public $method = 'get';
  
  public $html = 'form';
  
  public $field_base;
  
  public $values;
  
  public $fld_format_indxs = array(
    '[L]'=>'%1$s',
    '[F]'=>'%2$s',
    '[D]'=>'%3$s',
    '[OFL]'=>'%4$s'
  );
  
  public $fields = array();
  
  public function __construct($args=array()){
    extract($args);
    $this->action = (isset($action)) ? $action : $this->action;
    $this->method = (isset($method)) ? $method : $this->method;
    $this->html   = (isset($html)) ? $html : $this->html;
    $this->fields = (isset($fields)) ? $fields : $this->fields;
    $this->id = (isset($id)) ? sprintf(' id="%s"', $id) : '';
    $this->class = (isset($class)) ? sprintf(' class="%s"', $class) : '';
    $this->field_base = (isset($field_base)) ? $field_base : false;
    $this->values = (isset($values)) ? $values : array();
  }
  
  public function start(){
    $form = '<form'.$this->id.$this->class.' action="'.$this->action.'" method="'.$this->method.'">';
    return apply_filters('wpplnzr_form_start',$form);
  }
  
  public function end(){
    $form = '</form>';
    return apply_filters('wpplnzr_form_end',$form);
  }
  
  public function form_body(){
    if(count($this->fields)){
      foreach($this->fields as $fld=>$data){
        if($data['type'] == 'group'){
          $class = (isset($data['class']) && !empty($data['class']))?' class="'.$data['class'].'"':'';
          $fields[] = '<fieldset id="'.$fld.'"'.$class.'><legend><strong>'.$data['label'].'</strong></legend>';
          
          if(count($data['dependants'])){
            foreach($data['dependants'] as $dep_fld){
              if(isset($this->fields[$dep_fld])){
                $dep_fld_data = $this->fields[$dep_fld];
                $fields[] = $this->field($dep_fld,$dep_fld_data,$this->values,$this->field_base);
              }
            }
          }
          
          $fields[] = '</fieldset>';
          
        }else{
            
          if(!isset($data['parent'])) $fields[] = $this->field($fld,$data,$this->values,$this->field_base);
            
        }
      }
      
      $fields[] = wp_nonce_field( 'wpplgnzr_save_nonce', 'wpplgnzr_nonce', false, false );
      
      return (count($fields))?(count($fields)==1)?$fields[0]:join("\n",$fields):false;
    }
    
    return false;
  }
  
  public function form($ret = false){
    if(!$ret){
      echo $this->start();
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

    $fldname = (!$fld_base)?(!$plugin_page)?$fld:$plugin_page.'['.$fld.']':$fld_base.'['.$fld.']';
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
    $tag = (isset($data['tag']))?$data['tag']:'';
    $pholder = (isset($data['placeholder']) && !empty($data['placeholder']))?
      ' placeholder="'.$data['placeholder'].'"':'';
    $label_class = (isset($data['label_class']))?' '.$data['label_class']:'';
    $label_for = ' for="'.$fld.'"';
    $lbl_start = sprintf('<label class="wpplgnzr-form-label%s"%s>',$label_class,$label_for);
    $lbl_end = '</label>';
    $_data = array();

    if(isset($data['data'])){
      foreach($data['data'] as $dk=>$dv){
        $_data[] = 'data-'.$dk.'="'.$dv.'"';
      }
    }
    
    $_data = (count($_data))?(count($_data) == 1)?$_data[0]:join(' ',$_data):'';
    
    switch($data['type']){
      case 'text':
        $fldval = (isset($opts[$fld]))?$opts[$fld]:'';
        $fldval = (isset($data['value']) && !trim($fldval))?$data['value']:$fldval;
        $value = ' value="'.$fldval.'"';
        $label = sprintf('%s%s%s',$lbl_start,$data['label'],$lbl_end);
        $inp_attrs = $id.$class.$style.$readonly.$type.$fldname.$value.$pholder.$_data;
        $inp = sprintf('<input%s />',$inp_attrs);
        
        $field = array(
          'label'=>$label,
          'field'=>$inp,
          'desc'=>(!empty($desc))?'<div class="wpplgnzr-field-description">'.$desc.'</div>':'',
          'format'=>(isset($data['format']) && !empty($data['format']))?$data['format']:'[L][F]<br>[D]<br>'
        );
        
        break;
      case 'textarea':
        $fldval = (isset($opts[$fld]))?$opts[$fld]:'';

        $fldval = (isset($data['value']) && !trim($fldval))?$data['value']:$fldval;
        $label = sprintf('%s%s%s',$lbl_start,$data['label'],$lbl_end);
        $rows = (isset($data['rows']))?$data['rows']:6;
        $rows = ' rows="'.$rows.'"';
        $inp_attrs = $id.$class.$style.$readonly.$type.$fldname.$rows.$pholder.$_data;
        $inp = sprintf('<textarea%s>%s</textarea>',$inp_attrs,$fldval);
        
        $field = array(
          'label'=>$label,
          'field'=>$inp,
          'desc'=>(!empty($desc))?'<div class="wpplgnzr-field-description">'.$desc.'</div>':'',
          'format'=>(isset($data['format']) && !empty($data['format']))?$data['format']:'[L]<br>[F]<br>[D]<br>'
        );
        
        break;
      case 'radio':
      case 'checkbox':
        $chked = (isset($opts[$fld]) && $opts[$fld])?' checked="checked"':'';
        $inp_attrs = $id.$class.$style.$readonly.$type.$fldname.$chked.$_data;
        $inp = sprintf('%s<input%s />%s%s',$lbl_start,$inp_attrs,$data['label'],$lbl_end);
        
        $field = array(
          'label'=>'',
          'field'=>$inp,
          'desc'=>(!empty($desc))?'<div class="wpplgnzr-field-description">'.$desc.'</div>':'',
          'format'=>(isset($data['format']) && !empty($data['format']))?$data['format']:'[F]<br>[D]<br>'
        );
        
        break;
      case 'radio_select':
      case 'checkbox_select':
        $type = ' type="'.(($data['type'] == 'radio_select')?'radio':'checkbox').'"';
        $notUnique = ($data['type'] != 'radio_select' && isset($data['not_unique']))?true:false;
        
        if(count($data['options'])){
          $inc = 0;
          
          foreach($data['options'] as $chkopt=>$label){
            $chked = (isset($opts[$fld][$chkopt]))?' checked="checked"':'';
            $chked = ($notUnique && isset($opts[$fld][$inc]))?' checked="checked"':$chked;
            $fldname = (!$fld_base)?(!$plugin_page)?$fld:$plugin_page.'['.$fld.']':$fld_base.'['.$fld.']';
            $fldname = ($notUnique)?$fldname.'['.$inc.']':$fldname;
            $fldname = ' name="'.$fldname.'"';
            $fldval = ' value="'.$chkopt.'"';
            $opt_data = array();
            
            if(isset($data['options_data']) && isset($data['options_data'][$chkopt])){
              foreach($data['options_data'][$chkopt] as $dk=>$dv){
                $opt_data[] = 'data-'.$dk.'="'.$dv.'"';
              }
            }
            
            $opt_data = (count($opt_data))?(count($opt_data) == 1)?$opt_data[0]:join(' ',$opt_data):'';
            $inp_attrs = $id.$class.$style.$readonly.$type.$fldname.$fldval.$chked.$opt_data;
            $inp = sprintf('%s<input%s />%s%s',$lbl_start,$inp_attrs,$label,$lbl_end);
            
            $field['options_field'][$data['label']]['format'] = (isset($data['format']))?$data['format']:'[OFL]<br>[OFO]<br>[D]<br>';
            $field['options_field'][$data['label']]['desc'] = (isset($data['description']))?$data['description']:'';
            $field['options_field'][$data['label']]['option_columns'] = (isset($data['option_columns']) &&
              !empty($data['option_columns']))?$data['option_columns']:1;
            $field['options_field'][$data['label']]['option_columns_wrap'] = (isset($data['option_columns_wrap']) &&
              !empty($data['option_columns_wrap']))?$data['option_columns_wrap']:'div';
            $field['options_field'][$data['label']]['option_columns_wrap_class'] = (isset($data['option_columns_wrap_class']) &&
              !empty($data['option_columns_wrap_class']))?$data['option_columns_wrap_class']:'opt-col-[INDX]';
            $field['options_field'][$data['label']]['opts'][] = array(
              'label'=>'',
              'field'=>$inp,
              'format'=>(isset($data['option_format']) && !empty($data['option_format']))?$data['option_format']:'[F][L]<br>'
            );
            
            $inc++;
          }
        }
        
        break;
      case 'select':
        $sel_opts = '';

        if(count($data['options'])){
          $option = '<option%s value="%s">%s</option>';
          $optgroup = '<optgroup label="%s">%s</optgroup>';
          
          foreach($data['options'] as $selopt=>$label){
            $opt_data = array();
            
            if(is_array($label)){
              $tmp = '';
              
              foreach($label as $optval=>$optlabel){
                $selected = (isset($opts[$fld]) && $opts[$fld] == $optval)?' selected="selected"':'';
                if(isset($data['options_data']) && isset($data['options_data'][$optval])){
                  foreach($data['options_data'][$optval] as $dk=>$dv){
                    $opt_data[] = 'data-'.$dk.'="'.$dv.'"';
                  }
                }
                $opt_data = (count($opt_data))?(count($opt_data) == 1)?' '.$opt_data[0]:' '.join(' ',$opt_data):'';
                $attrs = $selected.$opt_data;
                $tmp .= sprintf($option,$attrs,$optval,$optlabel);
              }
              
              $sel_opts .= sprintf($optgroup,ucfirst($selopt),$tmp);
            }else{
              $selected = (isset($opts[$fld]) && trim($opts[$fld]) == trim($selopt))?' selected="selected"':'';
              
              if(isset($data['options_data']) && isset($data['options_data'][$selopt])){
                foreach($data['options_data'][$selopt] as $dk=>$dv){
                  $opt_data[] = 'data-'.$dk.'="'.$dv.'"';
                }
              }
              
              $opt_data = (count($opt_data))?(count($opt_data) == 1)?' '.$opt_data[0]:' '.join(' ',$opt_data):'';
              $dbug[] = $selopt;
              $attrs = $selected.$opt_data;
              $sel_opts .= sprintf($option,$attrs,$selopt,$label);
            }
          }
        }
        
        $field = array(
          'label'=>$lbl_start.$data['label'].$lbl_end,
          'field'=>"<select{$id}{$class}{$style}{$readonly}{$type}{$fldname}{$_data}>{$sel_opts}</select>",
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
          'field'=>"<input{$id}{$class}{$style}{$readonly}{$type}{$fldname}{$_data} />",
          'desc'=>'',
          'format'=>(isset($data['format']))?$data['format']:'<br>[F]<br>'
        );
        
        break;
      case 'hidden':
        $fldval = (isset($data['value']))?$data['value']:'';
        $fldval = (isset($opts[$fld]))?$opts[$fld]:$fldval;
        $value = ' value="'.$fldval.'"';
        
        $field = array(
          'label'=>'',
          'field'=>"<input{$class}{$type}{$fldname}{$value}{$_data} />",
          'desc'=>'',
          'format'=>(isset($data['format']))?$data['format']:'[F]<br>'
        );
        
        break;
      case 'html':
      
        $field = array(
          'label'=>'',
          'field'=>(!trim($tag))?$desc:'<'.$tag.$id.$class.$_data.'>'.$desc.'</'.$tag.'>',
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
          if(intval($data['option_columns']) > 1) {
            $chunk_size = ceil(count($data['opts'])/intval($data['option_columns']));
            $data_opts = array_chunk($data['opts'],$chunk_size);
            
            foreach($data_opts as $col_i=>$dchnk) {
              $tag = $data['option_columns_wrap'];
              $class = str_replace('[INDX]',$col_i,$data['option_columns_wrap_class']);
              $fld .= sprintf('<%s class="%s">',$tag,$class);
              
              foreach($dchnk as $d) {
                $format = str_replace($ff_tokes,$ff_reps,$d['format']);
                $fld .= sprintf($format, $d['label'],$d['field']);
              }
              
              $fld .= sprintf('</%s>',$tag);
            }
          } else {
              
            foreach($data['opts'] as $d){
              $format = str_replace($ff_tokes,$ff_reps,$d['format']);
              $fld .= sprintf($format, $d['label'],$d['field']);
            }
          }
        }
        
        $label = sprintf('%s%s%s',$lbl_start,$label,$lbl_end);
        $fmt = str_replace('[OFO]',$fld,$data['format']);
        $fmt = str_replace($ff_tokes,$ff_reps,$fmt);
        $ret_field[] = sprintf($fmt, '','',$data['desc'],$label);
      }
    }else{
      $format = str_replace($ff_tokes,$ff_reps,$field['format']);
      $ret_field = sprintf($format, $field['label'],$field['field'],$field['desc']);
    }
    
    return (is_array($ret_field) && count($ret_field))?(count($ret_field)==1)?
      $ret_field[0]:join("\n",$ret_field):$ret_field;
  }
}