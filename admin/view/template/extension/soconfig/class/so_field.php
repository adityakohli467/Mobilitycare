<?php
class So_Fields {
	public $config;
	public $registry;
	public function __construct($config,$registry = null) {
		$this->config =  $config;
		if($registry!= null){
			$this->registry =  $registry;
			$this->request 		= $registry->get('request');
		}
		
		$module_row = 0;
		
	}
	
	
	/**
	 * Function field_onOff
	 * Create Field Button
	 *
	 * Parameters:
	 *     (key)  - array value of Parent level 
	 *     (name) - string value of the name
	*/
	
	public function fieldsetHeader($fieldName,$icon,$subHeader=null) {
		if($subHeader){
			$fieldsetHeader[] = '<div class="so-ultimate-group-list__header flex items-center">';
			$fieldsetHeader[] = '	<div class="so-ultimate-fieldset-toggle-icon"><i class="fa fa-chevron-left"></i></div>';
			$fieldsetHeader[] = '	<div class="so-ultimate-fieldset-header-inner" >'.$fieldName.'</div>';
			$fieldsetHeader[] = '</div>';
		}else{
			$fieldsetHeader[] = '<div class="so-ultimate-fieldset-header">';
			$fieldsetHeader[] = '	<div class="so-ultimate-fieldset-header-inner" >';
			$fieldsetHeader[] = '		<div class="so-ultimate-fieldset-title flex items-center"> ';
			$fieldsetHeader[] = '			<div class="stacked-menu__item-icon" aria-hidden="true">';
			$fieldsetHeader[] = '				<svg class="next-icon"><use xlink:href="#'.$icon.'"/></svg>';
			$fieldsetHeader[] = '			</div>';
			$fieldsetHeader[] = '			<div class="stacked-menu__item-text">'.$fieldName.'</div>';
			$fieldsetHeader[] = '		</div>';
			$fieldsetHeader[] = '	</div>';
			$fieldsetHeader[] = '</div>';
		}
		
		
		$fieldsetHeader = implode('', $fieldsetHeader);
		return $fieldsetHeader;
	}
	
	public function groupHeaderBox($fieldName) {
		
		$groupHeaderBox[] = '<div class="so-ultimate-group-header-box">';
		$groupHeaderBox[] = '	<span class="so-ultimate-group-toggle-icon"><i class="fa fa-angle-down" aria-hidden="true"></i><i class="fa fa-angle-up" aria-hidden="true"></i></span>';
		$groupHeaderBox[] = '	<span class="so-ultimate-group-title">'.$fieldName.'</span>';
		$groupHeaderBox[] = '</div>';
		$groupHeaderBox = implode('', $groupHeaderBox);
		return $groupHeaderBox;
	}
	
	
	public function onOff($config_theme,$name,$label=null,$class_sfx=null,$desc=null) {
		$field_radio = '';
		$radios = array();
		
		$options = array(1=>'ON',0 => 'OFF');
		
		$names	= $config_theme.'['.$name.']';
		$selected	= isset($this->config[$config_theme][$name]) ? $this->config[$config_theme][$name] : 1 ;
		
		$radios[] = '<div class="control-group control-group-checkbox" >';
		$radios[] = '<div class="control-group-inner flex justify-between">';
			$radios[] = '<label class="control-label col-sm-7">'.$label.'</label>';
			$radios[] = '<fieldset class="btn-group btn-toggle '.$class_sfx.'" data-toggle="buttons">';
				foreach ($options as $value => $description) {
					if($value ==  $selected){
						$radios[] = '<label class="btn btn-default btn-sm btn-success active"><input class="field-'.$name.'" type="radio" name="'.$names.'" value="'.$value.'" checked="checked" /> ' . $description . '</label>';
					}else{
						$radios[] = '<label class="btn btn-default btn-sm "><input class="field-'.$name.'" type="radio" name="'.$names.'" value="'.$value.'" /> ' . $description . '</label>';
					}
				}
			$radios[]=	'</fieldset>';
		$radios[]=	'</div>'; 
		if(!empty($desc)) $radios[]=	'<div class="control-help">'.$desc.'</div>';
		$radios[]=	'</div>';
		
		$field_radio = implode('', $radios);
		return $field_radio;
	}
	
	function textarea($config_theme,$name,$label=null,$desc=null,$class_sfx=null){
	
		$names	= $config_theme.'['.$name.']';
		$values	= isset($this->config[$config_theme][$name]) ? $this->config[$config_theme][$name] : '';
		
		$fieldTextarea  = '<div class="control-group control-group-textarea ">';
		$fieldTextarea .= '<div class="control-group-inner ">';
		$fieldTextarea .= '<label class="control-label">'.$label.'</label>';
		$fieldTextarea .= '<div class="controls '.$class_sfx.'">';
		
		$fieldTextarea  .= '<textarea name="'.$names.'" rows="5" placeholder="'.$values.'"  class="form-control">'.$values.'</textarea>';
		
		$fieldTextarea .= '</div>';
		$fieldTextarea .= '</div>';
		if(!empty($desc)) $fieldTextarea.=	'<div class="control-help">'.$desc.'</div>';
		$fieldTextarea .= '</div>';
		return $fieldTextarea;
		
	}
	
	public function select($config_theme,$element,$options_array,$label=null,$class_sfx=null,$desc=null){
		
		$names	= $config_theme.'['.$element.']';
		$values	= isset($this-> config[$config_theme][$element]) ? $this-> config[$config_theme][$element] : '';
		
		$fieldSelect  = '<div class="control-group control-group-select ">';
		$fieldSelect .= '<div class="control-group-inner ">';
		$fieldSelect .= '<label class="control-label">'.$label.'</label>';
		$fieldSelect .= '<div class="controls '.$class_sfx.'">';
		
		$fieldSelect  .= '<select name="'.$names.'"  class="form-control select2-input">';
		foreach ($options_array as $fv => $fc){
			($fv == $values) ? $current = 'selected' : $current='';
			$fieldSelect .= '<option value="'.$fv.'" '.$current.' >'.$fc.'</option>	';
		}
		
		$fieldSelect .= '</select>';
		$fieldSelect .= '</div>';
		$fieldSelect .= '</div>';
		if(!empty($desc)) $fieldSelect.=	'<div class="control-help">'.$desc.'</div>';
		$fieldSelect .= '</div>';
		return $fieldSelect;
	}
	
	public function checkbox($key,$name,$valueDefault=null,$className=null) {
		$field = '';
		$checkbox = array();
		$names	= $key.'['.$name.']';
		
		$value	= isset($this->config[$key][$name]) ? $this->config[$key][$name] : $valueDefault;
		
		$checked = $value == 1 ? 'checked="checked"' : '';
		$checkbox[] = '<label class="control-label"><input  class="field-'.$name.'" type="checkbox" name="'.$names.'"  value="1"'.$checked.' /></label>';

		$field = implode('', $checkbox);
		return $field;
	}
	
	
	function text($config_theme,$name,$label=null,$class_sfx=null,$desc=null){
		
		$names	= $config_theme.'['.$name.']';
		$values	= isset($this->config[$config_theme][$name]) ? $this->config[$config_theme][$name] : ' ';
		
		$fieldTextarea  = '<div class="control-group control-group-text">';
		$fieldTextarea .= '<div class="control-group-inner ">';
		if($label && $label !=' '){
			$fieldTextarea  .= '<label class="control-label">'.$label.'</label>';
		}
		$fieldTextarea .= '<div class="controls '.$class_sfx.'">';
		$fieldTextarea  .= '<input type="text" name="'.$names.'" value="'.$values.'" placeholder="'.$values.'"  class="form-control" />';
		$fieldTextarea .= '</div>';
		$fieldTextarea .= '</div>';
		if(!empty($desc)) $fieldTextarea.=	'<div class="control-help">'.$desc.'</div>';
		$fieldTextarea .= '</div>';
		return $fieldTextarea;
		
	}
	
	function goLink($label=null,$url=null,$desc=null){
		$user_token = isset($this->request->get['user_token']) ? $this->request->get['user_token'] : '';
	
		$goLink  = '<div class="control-group control-group-link">';
		$goLink .= '<div class="control-group-inner flex justify-between">';
		if($label && $label !=' '){
			$goLink  .= '<label class="control-label">'.$label.'</label>';
		}
		$goLink .= '<a class="btn btn-default btn-sm btn-success active" href="'.$url.'&user_token='.$user_token.'" target="_blank"> Click Here</a>';
		$goLink .= '</div>';
		$goLink .= '</div>';
		return $goLink;
		
	}
	
	function langTextarea($languages ,$nameconfig,$element,$label=null,$class_sfx=null){
		
		
		$fieldLangTextarea  = '<div class="control-group control-group-langTextarea">';
		$fieldLangTextarea .= '<div class="control-group-inner ">';
		$fieldLangTextarea .= '<label class="control-label">'.$label.'</label>';
		
		$fieldLangTextarea  .= '<ul class="nav nav-tabs">';
		foreach ($languages as $language){
			$active = $language['language_id'] == 1 ? 'active' : ' ';
			
			$fieldLangTextarea .= '<li class="'.$active.'">
			<a href="#language-'.$element.$language['language_id'].'" data-toggle="tab"><img src="language/'.$language['code'].'/'.$language['code'].'.png" title="'.$language['name'].'" /> '.$language['name'].'</a>
			</li>';
		}
		$fieldLangTextarea .= '</ul>';
		$fieldLangTextarea .= '<div class="tab-content '.$class_sfx.'">';
		foreach ($languages as $language){
			$active = $language['language_id'] == 1 ? 'active' : ' ';
			$names	= $nameconfig.'['.$element.']['.$language['language_id'].']';
			$values	= isset($this->config[$nameconfig][$element][$language['language_id']])? $this->config[$nameconfig][$element][$language['language_id']]: '';
		
			$fieldLangTextarea .= '<div class="tab-pane '.$active.'" id="language-'.$element.$language['language_id'].'">';
			$fieldLangTextarea .='<textarea name="'.$names.'" data-toggle="summernote" id="input-description-'.$element.$language['language_id'].'">';
			$fieldLangTextarea .=  $values;
			$fieldLangTextarea .= '</textarea>';
			$fieldLangTextarea .= '</div>';
		}
		
		$fieldLangTextarea .= '</div>';
		$fieldLangTextarea .= '</div>';
		$fieldLangTextarea .= '</div>';
		
		
		return $fieldLangTextarea;
	}
	
	private function generateSelectOptions( $items = array(), $selected = '' )
    {
        $html = '';
        foreach ($items as $item)
        {
            $html .= '<option ' . (($selected !== 'no-selection' && $item == $selected) ? 'selected="selected"' : '') . ' value="'. $item .'">'. $item .'</option>';
        }
        return $html;
    }

    // Get current font
    private static function filterArray($items, $key)
    {
        foreach ($items as $item)
        {
            if ($item->family == $key) return $item;
        }
        return false;
    }
	
	public function gfonts($config_theme,$name,$classes=null){
		$names	= $config_theme.'['.$name.']';
		
		if(isset($this->config[$config_theme][$name]) ){
			$values = htmlentities($this->config[$config_theme][$name]);
		}else{
			$values	= ' ' ;
		}
		
		$plugin_path   =  DIR_TEMPLATE.'extension/soconfig/asset/webfonts/webfonts.json'; 
		if(file_exists( $plugin_path )){
            $json = file_get_contents( $plugin_path );
        }
		
		$webfonts   = json_decode($json);
        $items      = $webfonts->items;
        $value      = isset($this->config[$config_theme][$name]) ? json_decode($this->config[$config_theme][$name]): '';
		
        if (isset($value->fontFamily))
        {
            $font = self::filterArray($items, $value->fontFamily);
        }
		
		$html  = '';

        $systemFonts = array(
            'Arial',
            'Tahoma',
            'Verdana',
            'Helvetica',
            'Times New Roman',
            'Trebuchet MS',
            'Georgia'
        );

        $fontWeights = array(
            '300'=>'Light',
            '400'=>'Normal',
            '500'=>'Medium',
            '600'=>'Semi Bold',
            '700'=>'Bold',
            '800'=>'Extra Bold',
            '900'=>'Black'
        );

        $fontStyles = array(
            'uppercase'=>'Uppercase',
            'lowercase'=>'Lowercase',
            'capitalize'=>'Capitalize'
        );
		
		
		
		//Font Family
        $html .= '<div class="control-group so-ultimate-field-webfont ">';
        $html .= '<div class="row '. $classes .'">';
		
        $html .= '<div class="col-md-12 so-ultimate-webfont-family">';
        $html .= '<label><small>Family</small></label>';
        $html .= '<select class="so-ultimate-webfont-list form-control select2-input">';

        $html .= '<optgroup label="System Font">';

        foreach ($systemFonts as $systemFont)
        {
            $html .= '<option '. ((isset($value->fontFamily) && $systemFont == $value->fontFamily)?'selected="selected"':'') .' value="'. $systemFont .'">'. $systemFont .'</option>';
        }

        $html .= '</optgroup>';

        $html .= '<optgroup label="Google Font">';

        foreach ($items as $item)
        {
            $html .= '<option '. ((isset($value->fontFamily) && $item->family == $value->fontFamily)?'selected="selected"':'') .' value="'. $item->family .'">'. $item->family .'</option>';
        }

        $html .= '</optgroup>';

        $html .= '</select>';
		 $html .='<p class="control-help">If you want to speed up your site use one of the common fonts instead of the fonts from Google.</p>';
        $html .= '</div>';
		
		 //Font Weight
		
        $html .= '<div class="col-md-6 so-ultimate-webfont-weight">';
        $html .= '<label><small>Weight</small></label>';
        $html .= '<select class="form-control so-ultimate-webfont-weight-list">';
        $html .= '<option value="">Select</option>';

        foreach($fontWeights as $key=>$fontWeight)
        {
            if(isset($value->fontWeight) && $value->fontWeight == $key)
            {
                $html .= '<option value="'. $key .'" selected>'. $fontWeight .'</option>';
            }
            else
            {
                $html .= '<option value="'. $key .'">'. $fontWeight .'</option>';
            }
        }

        $html .= '</select>';
        $html .= '</div>';
		
		//Font Subsets
        $html .= '<div class="col-md-6 so-ultimate-webfont-subset">';
        $html .= '<label><small>Subset</small></label>';
        $html .= '<select class="form-control so-ultimate-webfont-subset-list">';
        $html .= '<option value="">Select</option>';
        
        if(isset($value->fontFamily) && $value->fontFamily)
        {
            if(!in_array($value->fontFamily, $systemFonts))
            {
                $html .= $this->generateSelectOptions($font->subsets, $value->fontSubset);
            }
        }

        $html .= '</select>';
        $html .= '</div>';

        
		
        //Font Size
		if($name !='custom_font'){
        $fontSize = (isset($value->fontSize))? $value->fontSize :'14';
        $html .= '<div class="col-md-6 so-ultimate-webfont-size">';
        $html .= '<label><small>Size</small></label>';
        $html .= '<input type="number" value="'. $fontSize .'" class="form-control so-ultimate-webfont-size-input" min="6">';
        $html .= '</div>';
		}
       

        //Font Transform
        $html .= '<div class="col-md-6 so-ultimate-webfont-style">';
        $html .= '<label><small>Transform</small></label>';
        $html .= '<select class="form-control so-ultimate-webfont-style-list">';
        $html .= '<option value="">Select</option>';

        foreach($fontStyles as $key=>$fontStyle)
        {
            if(isset($value->fontStyle) && $value->fontStyle == $key)
            {
                $html .= '<option value="'. $key .'" selected>'. $fontStyle .'</option>';
            }
            else
            {
                $html .= '<option value="'. $key .'">'. $fontStyle .'</option>';
            }
        }

        $html .= '</select>';
        $html .= '</div>';
		
	
		
        $html .= '</div>';

        //Preview
		
        $html .= '<p style="display:none" class="so-ultimate-webfont-preview">1 2 3 4 5 6 7 8 9 0 Grumpy wizards make toxic brew for the evil Queen and Jack.</p>';
        $html .= '<input type="hidden" name="'. $names.'" class="so-ultimate-webfont-input" id="'. $name .'" value="'.$values.'">';
        $html .= '</div>';

        return $html;
	}
	
	
	function shoppingIcon($config_theme,$name,$options_array,$label= null,$class_sfx=null){
	
		$names	= $config_theme.'['.$name.']';
		$values	= isset($this-> config[$config_theme][$name]) ? $this-> config[$config_theme][$name] : '';
		
		$fieldSelect  = '<div class="control-group control-group-shopping ">';
		$fieldSelect .= '<div class="control-group-inner ">';
		$fieldSelect .= '<label class="control-label">'.$label.'</label>';
		$fieldSelect .= '<div class="controls flex justify-between'.$class_sfx.'">';
		
		foreach ($options_array as $icon => $iconName){
			$fieldSelect  .= '<label class="image-btn">';
			if($icon == $values){
				$fieldSelect  .= '<input type="radio" name="'.$names.'" value="'.$icon.'" checked  >';
				$fieldSelect  .= '<div class="shopping-icon active"><img src="'.DIR_SOCONFIG.'images/shopping/'.$icon.'.png" alt=""></div>';
			}else{
				$fieldSelect  .= '<input type="radio" name="'.$names.'" value="'.$icon.'" >';
				$fieldSelect  .= '<div class="shopping-icon "><img src="'.DIR_SOCONFIG.'images/shopping/'.$icon.'.png" alt=""></div>';
			}
			$fieldSelect  .= '</label>';
		}
		
		$fieldSelect .= '</div>';
		$fieldSelect .= '</div>';
		$fieldSelect .= '</div>';
		return $fieldSelect;
	}
	
	/**
	 * Function New Code
	*/
	function homepageSelect($config_theme,$element,$options_array,$label=null,$desc=null,$class_sfx=null){
		$user_token = isset($this->request->get['user_token']) ? $this->request->get['user_token'] : '';
		$megaLink = 'index.php?route=extension/module/so_page_builder&user_token='.$user_token;
		$names	= $config_theme.'['.$element.']';
		$values	= isset($this->config[$config_theme][$element]) ? $this-> config[$config_theme][$element] : '';
		
		
		$fieldSelect  = '<div class="control-group control-group-select ">';
		$fieldSelect .= '<div class="control-group-inner ">';
		$fieldSelect .= '<label class="control-label">'.$label.'</label>';
		$fieldSelect .= '<div class="controls '.$class_sfx.'">';
			$fieldSelect  .= '<select name="'.$names.'"  class="homepage-seleclist form-control select2-input">';
			foreach ($options_array as $count => $value){
				$layoutID = $value['key'];
				$layoutName = $value['typelayout'];
				
				
				($layoutID == $values) ? $current = 'selected' : $current='';
				$fieldSelect .= '<option value="'.$layoutID.'" '.$current.' >'.$layoutName.'</option>	';
			}
			$fieldSelect .= '</select>';
			
			$fieldSelect .= '<div id="homepage-preview" class="preview-holder">';
			
			foreach ($options_array as $count => $value){
				$layoutID = $value['key'];
				$headerID = $value['typeheader']['key'];
				$footerID = $value['typefooter']['key'];
				$layoutName = $value['typelayout'];
				
				if($layoutID == $values){
					$fieldSelect  .= '<a href="'.$megaLink.'" class="btn-editLayout btn btn-sm btn-link" target="_blank"> <i class="fa fa-pencil-square-o" ></i> Edit The Layout</a>';
					if(file_exists(PATH_SOCONFIG.'images/homepage/home1.jpg'))$fieldSelect  .='<img src="'.DIR_SOCONFIG.'images/homepage/home'.$layoutID.'.jpg" alt="homepage"/>';
					else $fieldSelect  .='	<img src="http://via.placeholder.com/150x180/ddd/3498db" alt="homepage"/>';
					
					$fieldSelect  .= '<p class="applyHome btn btn-sm btn-primary"> Apply Default Setting</p>';
					$fieldSelect  .= '<input class="layoutFormID" name="layoutForm" type="hidden"  value="'.$layoutID.'" data-header="'.$headerID.'" data-footer="'.$footerID.'"/>';
				}
			}
			$fieldSelect .= '</div>';
			
		$fieldSelect .= '</div>';
		$fieldSelect .= '</div>';
		$fieldSelect .= '</div>';
		return $fieldSelect;
		
		
	}
	
	function blogArticle($config_theme,$element,$options_array,$label=null,$desc=null,$class_sfx=null){
		
		$names	= $config_theme.'['.$element.']';
		$values	= isset($this->config[$config_theme][$element]) ? $this-> config[$config_theme][$element] : '';
		
		$fieldSelect  = '<div class="control-group control-group-select ">';
		$fieldSelect .= '<div class="control-group-inner ">';
		$fieldSelect .= '<label class="control-label">'.$label.'</label>';
		$fieldSelect .= '<div class="controls '.$class_sfx.'">';
			$fieldSelect  .= '<select name="'.$names.'"  class="article-seleclist form-control select2-input">';
			foreach ($options_array as $count => $fc){
				($count == $values) ? $current = 'selected' : $current='';
				$fieldSelect .= '<option value="'.$count.'" '.$current.' >'.$fc.'</option>	';
			}
			$fieldSelect .= '</select>';
			
			$fieldSelect .= '<div id="article-preview" class="preview-holder">';
			foreach ($options_array as $count => $fc){
				if($count == $values){
					
					if(file_exists(PATH_SOCONFIG.'images/blogs/default.png'))$fieldSelect  .='<img src="'.DIR_SOCONFIG.'images/blogs/'.$count.'.png" alt="homepage"/>';
					else $fieldSelect  .='	<img src="http://via.placeholder.com/150x180/ddd/3498db" alt="homepage"/>';
				}
			}
			$fieldSelect .= '</div>';
		$fieldSelect .= '</div>';
		$fieldSelect .= '</div>';
		$fieldSelect .= '</div>';
		return $fieldSelect;
		
		
	}
	
	
	function effectBanner($config_theme,$element,$options_array,$label=null,$desc=null,$class_sfx=null){
		
		$names	= $config_theme.'['.$element.']';
		$values	= isset($this->config[$config_theme][$element]) ? $this-> config[$config_theme][$element] : '';
		
		$fieldSelect  = '<div class="control-group control-group-select ">';
		$fieldSelect .= '<div class="control-group-inner ">';
		$fieldSelect .= '<label class="control-label">'.$label.'</label>';
		$fieldSelect .= '<div class="controls '.$class_sfx.'">';
			$fieldSelect .= '<div id="banner-preview" >';
			$fieldSelect .= '<div class="scrollDiv"><div class="scrollContent">';	
			foreach ($options_array as $count => $fc){
				$fieldSelect .= '<div class="preview-holder preview-holder--banners banners-effect-'.$count.'">';	
				if($count == $values){
					$fieldSelect  .= '<input type="radio" name="'.$names.'" value="'.$count.'" checked >';
					$fieldSelect  .= '<div class="banners type  active"><div class="banners-number">'.$count.'</div><div><a href="javascript:void(0)" ><img src="'.DIR_SOCONFIG.'images/banner.png" alt="homepage"/></a></div></div>';
				}else{
					$fieldSelect  .= '<input type="radio" name="'.$names.'" value="'.$count.'" >';
					$fieldSelect  .= '<div class="banners type"><div class="banners-number">'.$count.'</div><div><a href="javascript:void(0)" ><img src="'.DIR_SOCONFIG.'images/banner.png" alt="homepage"/></a></div></div>';
				}
				$fieldSelect .= '</div>';
				
			}
			$fieldSelect .= '</div></div>';
			$fieldSelect .= '</div>';
			
		$fieldSelect .= '</div>';
		$fieldSelect .= '</div>';
		$fieldSelect .= '</div>';
		return $fieldSelect;
		
		
	}
	
	function headerStyle($config_theme,$element,$options_array,$label=null,$desc=null,$class_sfx=null){
		$result = [];
		$resultPreview = [];
		$names	= $config_theme.'['.$element.']';
		$values	= isset($this->config[$config_theme][$element]) ? $this-> config[$config_theme][$element] : '';
		
		$fieldSelect  = '<div class="control-group control-group-select ">';
		$fieldSelect .= '<div class="control-group-inner ">';
		$fieldSelect .= '<label class="control-label">'.$label.'</label>';
		$fieldSelect .= '<div class="controls '.$class_sfx.'">';
			$fieldSelect  .= '<select name="'.$names.'"  class="header-seleclist form-control select2-input">';
			foreach ($options_array as $count => $value){
				if (!array_intersect($value[$element], $result)) {
				$result = $value[$element];
				$layoutID = $value['key'];
				$headerID = $value[$element]['key'];
				$headerTitle = $value[$element]['title'];
				
				($headerID == $values) ? $current = 'selected' : $current='';
				$fieldSelect .= '<option value="'.$headerID.'" '.$current.' >'.$headerTitle.'</option>	';
				}
			}
			$fieldSelect .= '</select>';
			
			$fieldSelect .= '<div id="header-preview" class="'.$element.'-preview" >';
			$fieldSelect .= '<div class="scrollDiv"><div class="scrollContent">';	
			foreach ($options_array as $count => $value){
				if (!array_intersect($value[$element], $resultPreview)) {
					$resultPreview = $value[$element];
					$layoutID = $value['key'];
					$headerID = $value[$element]['key'];
					$headerTitle = $value[$element]['title'];
					
					$fieldSelect .= '<div class="banners preview-holder preview-holder--headerStyle headerStyle-'.$headerID.'" >';		
						
						if(file_exists(PATH_SOCONFIG.'images/blogs/default.png'))$fieldSelect  .='<img src="'.DIR_SOCONFIG.'images/homepage/'.$element.$headerID.'.jpg" alt="homepage"/>';
						else $fieldSelect  .='	<img src="http://via.placeholder.com/150x180/ddd/3498db" alt="homepage"/>';
						$fieldSelect .= '<div class="banners-title">'.$headerTitle.'</div>';
					$fieldSelect .= '</div>';
					
				}
			}
			$fieldSelect .= '</div></div>';
			$fieldSelect .= '</div>';
		$fieldSelect .= '</div>';
		$fieldSelect .= '</div>';
		$fieldSelect .= '</div>';
		return $fieldSelect;
		
		
	}
	/**
	 * Function field_text
	 * Create Field Button Google Font
	 *
	 * Parameters:
	 *     (key)  - array value of Parent level 
	 *     (name) - string value of the name
	*/
	
	function field_date($nameconfig,$element,$label=null){
		$names	= $nameconfig.'['.$element.']';
		$values	= isset($this-> config[$nameconfig][$element]) ? $this-> config[$nameconfig][$element] : '';
	  
		$fielddate ='<div class="control-group control-group-date">';
		$fielddate .='<div class="control-group-inner">';
			$fielddate .='<label class="control-label">'.$label.'</label>';
			
			$fielddate .='<div class="input-group date">';
			$fielddate .='<span class="input-group-btn"><button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button> </span>';
			$fielddate .='<input type="text" name="'.$names.'" value="'.$values.'"  data-date-format="YYYY-MM-DD" id="input-date-comingsoon" class="form-control" />';
			
			$fielddate .='</div>';
		$fielddate .='</div>';
		$fielddate .='</div>';
		
		return $fielddate;

	}

	function field_colors($nameconfig,$element,$label=null, $class_sfx=null){
		$config = $this-> config;
		$names	= $nameconfig.'['.$element.']';
		$values	= isset($this-> config[$nameconfig][$element]) ? $this-> config[$nameconfig][$element] : '';
		
		$fieldColors  = '<div class="control-group control-group-colors">';
		$fieldColors .= '<div class="control-group-inner '.$class_sfx.'">';
		if($label && $label !=' '){
			$fieldColors .= '<label class="control-label">'.$label.'</label>';
		}
		
		$fieldColors .='<div class="input-group form-inline colorfield">';
			$fieldColors .= '<span class="minicolors-swatch"><i style="background-color:'.$values.'"></i></span>';
			$fieldColors .= '<input  class="minicolors-input colorpicker-element" name="'.$names.'" value="'.$values.'" placeholder="Select color">';
		$fieldColors .= '</div>';
		
		$fieldColors .= '</div>';
		$fieldColors .= '</div>';
		
		return $fieldColors;
	}
	
	function liveColors($nameconfig,$name,$label=null, $class_sfx=null){
		$names	= $nameconfig.'['.$name.']';
		
       if(isset($this->config[$nameconfig][$name]) ){
			$values = htmlentities($this->config[$nameconfig][$name]);
		}else{
			$values	= ' ' ;
		}
		
		$value  = isset($this->config[$nameconfig][$name]) ? json_decode($this->config[$nameconfig][$name]): '';
		$fieldColors  = '<div class="control-group control-group-colors so-ultimate-field-livecolor ">';
		$fieldColors .= '<div class="control-group-inner '.$class_sfx.'">';
		if($label && $label !=' '){
			$fieldColors .= '<label class="control-label">'.$label.'</label>';
		}
		
		$bgColor = (isset($value->background))? $value->background:'';
		$fieldColors .='<div class="input-group form-inline colorfield liveColors" >';
			$fieldColors .= '<span class="minicolors-swatch"><i style="background-color:'.$bgColor.'"></i></span>';
			$fieldColors .= '<input  class="minicolors-input so-ultimate-livecolor-background" value="'.$bgColor.'"  placeholder="Select color">';
			$fieldColors .= '<label class="minicolors-label">Background Color</label>';
		$fieldColors .= '</div>';
		
		$txtColor = (isset($value->color))?$value->color:'';
		$fieldColors .='<div class="input-group form-inline colorfield liveColors" >';
			$fieldColors .= '<span class="minicolors-swatch"><i style="background-color:'.$txtColor.'"></i></span>';
			$fieldColors .= '<input  class="minicolors-input so-ultimate-livecolor-color"  value="'.$txtColor.'"placeholder="Select color">';
			$fieldColors .= '<label class="minicolors-label">Text Color</label>';
		$fieldColors .= '</div>';
		$fieldColors .= '<input type="hidden" name="'. $names.'" value="'.$values.'" class="so-ultimate-livecolors-input" id="'. $name .'">';
		$fieldColors .= '</div>';
		$fieldColors .= '</div>';
		 //Preview
        

		
		return $fieldColors;
	}
	
	function field_addimage($nameconfig,$element,$label=null,$className=null,$desc=null){
		
		$names	= $nameconfig.'['.$element.']';
		$values	= isset($this->config[$nameconfig][$element]) ? $this->config[$nameconfig][$element] : '';
		if (is_file(DIR_IMAGE.$values)) $srcimage = $this->resize($values, 80, 80);
		else $srcimage = $this->resize('no_image.png', 80, 80);
		
		$fieldimage ='<div class="control-group control-group-addimage">';
		$fieldimage .='<div class="control-group-inner ">';
		if($label && $label !=' '){
			$fieldimage  .= '<label class="control-label">'.$label.'</label>';
		}
		$fieldimage .='<div class="preview-holder">';
		$fieldimage .= '<p>Select images</p>';
		$fieldimage .='<a href="" id="thumb-'.$element.'" data-toggle="image" class="img-thumbnail '.$className.'">';
		$fieldimage .='<img src="'.$srcimage.'"  data-placeholder="../image/no_image.png" width="80" />';
		$fieldimage .='</a>';
		$fieldimage .='<input type="hidden" name="'.$names.'" value="'.$values.'" id="input-'.$element.'" />';
		
		$fieldimage .='</div>';
		if(!empty($desc)) $fieldimage.=	'<div class="control-help">'.$desc.'</div>';
		$fieldimage .='</div>';
		$fieldimage .='</div>';
		
		return $fieldimage;
	}
	
	public function devices($nameconfig,$element,$label=null,$class_sfx=null){
		
		$devices = array(
			'lg'=>'<i class="fa fa-lg fa-desktop"></i> ',
			'md'=>'<i class="fa fa-lg fa-tablet" style=" transform: rotate(90deg);"></i> ',
			'sm'=>'<i class="fa fa-lg fa-tablet" ></i> ',
			'xs'=>'<i class="fa fa-lg fa-mobile"></i>  ',
		);
		$columns = array(
			'1'=>'1 column',
			'2'=>'2 columns',
			'3'=>'3 columns',
			'4'=>'4 columns',
			'5'=>'5 columns',
			'6'=>'6 columns',
			
		);
		
		$fielddevices  = '<div class="control-group control-group-devices">';
		$fielddevices .= '<div class="control-group-inner '.$class_sfx.'">';
		$fielddevices .= '<label class="control-label">'.$label.'</label>';
		$fielddevices .='<ul class="nav nav-tabs nav-tabs--devices" role="tablist">';
			foreach ($devices as $subfix => $device):
			$navActive = ($subfix == 'lg') ? 'active' : '';
			$fielddevices .='<li class="'.$navActive.'"><a href="#language-'.$element.$subfix.'" role="tab" data-toggle="tab">'.$device.'</a></li>';
			endforeach;
		$fielddevices .='</ul>';
		
		$fielddevices .='<div class="tab-content">';
			foreach ($devices as $subfix => $device):
			$navActive = ($subfix == 'lg') ? 'active' : '';
			$names	= $nameconfig.'['.$element.$subfix.']';
			$values	= isset($this-> config[$nameconfig][$element.$subfix]) ? $this-> config[$nameconfig][$element.$subfix]: '';
		
			$fielddevices .='<div class="tab-pane '.$navActive.'" id="language-'.$element.$subfix.'">';
			$fielddevices .='<select name="'.$names.'"  class="form-control width30">';
				foreach ($columns as $fv => $fc):
					$current = ($fv == $values ) ?  'selected' : '';
					$fielddevices .=' <option value="'.$fv.'" '.$current.' >'.$fc.'</option>';
				
				endforeach;
			$fielddevices .='</select>';
			$fielddevices .='</div>';
			endforeach;
		$fielddevices .='</div>';
		$fielddevices .='<span class="help-block">(set number columns of devices)</span>';
		
		$fielddevices .= '</div>';
		$fielddevices .= '</div>';
		
		return $fielddevices;
	}
	
	

	public function create_position($titlepositon=null,$namepositon=null,$layout_modules=null,$extensions=null,$module_row =null) {
		
		global $module_row;
		if($module_row == null ) $module_row = 0;
		$namepositon2 = str_replace('', '-', $namepositon);

		$create_position  = '<table id="module-'.$namepositon2.'" class="table table-striped table-bordered table-hover">';
		$create_position .= '<thead><tr><td class="text-center">'.$titlepositon.'</td></tr></thead> <tbody>';
		
		foreach ($layout_modules as $layout_module) { 
			if ($layout_module['position'] == $namepositon) {
				
				$create_position .= '<tr id="module-row'.$module_row.'"><td class="text-left"><div class="input-group">';
				$create_position .= '<select name="layout_module['.$module_row.'][code]" class="form-control input-sm select2-input">';
				
				foreach ($extensions as $extension) {
					$create_position .= '<optgroup label="'. $extension['name'].'">';
					if (!$extension['module']) {
						if ($extension['code'] == $layout_module['code']) {
							$create_position .= '<option value="'. $extension['code'] .'" selected="selected">'. $extension['name'].'</option>';
						}else{
							$create_position .= '<option value="'. $extension['code'] .'" >'. $extension['name'].'</option>';
						}
							
					}else{
						foreach ($extension['module'] as $module) { 
							if ($module['code'] == $layout_module['code']) { 
								$create_position .= '<option value="'.$module['code'].'" selected="selected">'.$module['name'].'</option>';
							}else{
								$create_position .= '<option value="'. $module['code'] .'">'. $module['name'].'</option>';
							}
						}
					}
					$create_position .= '</optgroup>';
				}
				
				$create_position .= '</select>';
				$create_position .= '<input type="hidden" name="layout_module['.$module_row.'][position]" value="'. $layout_module['position'].'" />';
				$create_position .= '<div class="input-group-btn">';
					$create_position .= '<input type="text" size="5" class="form-order input-sm" name="layout_module['. $module_row.'][sort_order]" value="'. $layout_module['sort_order'].'" />';
					$create_position .= '<a href="'. $layout_module['edit'] .'" type="button" data-toggle="tooltip" title="Edit" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-pencil"></i></a>';
					$create_position .= '<button type="button" onclick=$("#module-row'.$module_row.'").remove(); data-toggle="tooltip" title="Remove" class="btn btn-danger btn-sm"><i class="fa fa fa-minus-circle"></i></button>';
				$create_position .= ' </div>';
				$create_position .= '</div></td></tr>';
				
				$module_row++;
			}
		}
		
		$create_position .= '</tbody>';
		$create_position .= '<tfoot><tr><td class="text-left"><div class="input-group">';
		$create_position .= ' <select class="form-control input-sm select2-input">';
		$create_position .= ' <option value=""></option>';
		foreach ($extensions as $extension) {
					$create_position .= '<optgroup label="'.$extension['name'] .'">';
			if (!$extension['module']) { 
					$create_position .= '<option value="'. $extension['code'] .'">'. $extension['name'].'</option>';
			}else{
				foreach ($extension['module'] as $module) { 
					$create_position .= '<option value="'. $module['code'] .'">'. $module['name'].'</option>';
				}
			}
		}
		$create_position .= ' </select>';
		$create_position .= ' <div class="input-group-btn">';
		$create_position .=	'<button type="button" onclick=addModule("'.$namepositon2.'") data-toggle="tooltip" title="Add Module" class="btn btn-primary btn-sm"><i class="fa fa-plus-circle"></i></button>';
		$create_position .=	'</div>';
		$create_position .=	'</div></td></tr> </tfoot>';
		$create_position .=	'</table>';
		
		return $create_position;
		
	}	
	
	public function home_position($titlepositon=null,$namepositon=null,$layout_modules=null,$extensions=null,$module_row =null,$homeLayouts =null) {
		
		global $module_row;
		if($module_row == null ) $module_row = 0;
		$namepositon2 = str_replace('', '-', $namepositon);

		$create_position  = '<table id="module-'.$namepositon2.'" class="table table-striped table-bordered table-hover">';
		$create_position .= '<thead><tr><td class="text-center">'.$titlepositon.'</td></tr></thead> <tbody>';
		
		foreach ($layout_modules as $layout_module) { 
			if ($layout_module['position'] == $namepositon) {
				
				$create_position .= '<tr id="module-row'.$module_row.'"><td class="text-left"><div class="input-group ">';
				$create_position .= '<select name="layout_module['.$module_row.'][code]" class="form-control input-sm select2-input">';
				
				foreach ($extensions as $extension) {
					$create_position .= '<optgroup label="'. $extension['name'].'">';
					if (!$extension['module']) {
						if ($extension['code'] == $layout_module['code']) {
							$create_position .= '<option value="'. $extension['code'] .'" selected="selected">'. $extension['name'].'</option>';
						}else{
							$create_position .= '<option value="'. $extension['code'] .'" >'. $extension['name'].'</option>';
						}
							
					}else{
						foreach ($extension['module'] as $module) { 
							if ($module['code'] == $layout_module['code']) { 
								$create_position .= '<option value="'.$module['code'].'" selected="selected">'.$module['name'].'</option>';
							}else{
								$create_position .= '<option value="'. $module['code'] .'">'. $module['name'].'</option>';
							}
						}
					}
					$create_position .= '</optgroup>';
				}
				$create_position .= '</select>';
				
				$create_position .= '<input type="hidden" name="layout_module['.$module_row.'][position]" value="'. $layout_module['position'].'" />';
				$create_position .= '<div class="input-group-btn"><h5 style="margin: 0 15px;">Home Layout</h5></div>';
				$create_position .= '<div class="input-group-btn" style="width: 250px;">';
					$create_position .= ' <select  name="layout_module['. $module_row.'][sort_order]"  class="form-control input-sm" style="width: 75%;">';
					foreach ($homeLayouts as $homeid =>$homelayout) {
						($homeid == $layout_module['sort_order']) ? $current = 'selected' : $current='';
						$create_position .= '<option value="'.$homeid.'" '.$current.' >'. $homelayout.'</option>';
					}
				$create_position .= ' </select>';
					$create_position .= '<a href="'. $layout_module['edit'] .'" type="button" data-toggle="tooltip" title="Edit" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-pencil"></i></a>';
					$create_position .= '<button type="button" onclick=$("#module-row'.$module_row.'").remove(); data-toggle="tooltip" title="Remove" class="btn btn-danger btn-sm"><i class="fa fa fa-minus-circle"></i></button>';
				$create_position .= ' </div>';
				$create_position .= '</div></td></tr>';
				
				$module_row++;
			}
		}
		
		$create_position .= '</tbody>';
		$create_position .= '<tfoot><tr><td class="text-left"><div class="input-group">';
		$create_position .= ' <select class="form-control input-sm select2-input">';
		$create_position .= ' <option value=""></option>';
		foreach ($extensions as $extension) {
					$create_position .= '<optgroup label="'.$extension['name'] .'">';
			if (!$extension['module']) { 
					$create_position .= '<option value="'. $extension['code'] .'">'. $extension['name'].'</option>';
			}else{
				foreach ($extension['module'] as $module) { 
					$create_position .= '<option value="'. $module['code'] .'">'. $module['name'].'</option>';
				}
			}
		}
		$create_position .= ' </select>';
		$create_position .= ' <div class="input-group-btn">';
		$create_position .=	'<button type="button" onclick=addModule("'.$namepositon2.'") data-toggle="tooltip" title="Add Module" class="btn btn-primary btn-sm"><i class="fa fa-plus-circle"></i></button>';
		$create_position .=	'</div>';
		$create_position .=	'</div></td></tr> </tfoot>';
		$create_position .=	'</table>';
		
		return $create_position;
		
	}
	
	public function var_module_row() {
		global $module_row;
		$js_position = 'var module_row ='. $module_row;
		return $js_position;
	}

	public function resize($filename, $width, $height) {
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		$image_old = $filename;
		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . $width . 'x' . $height . '.' . $extension;

		if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
				 
			if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) { 
				return DIR_IMAGE . $image_old;
			}
 
			$path = '';

			$directories = explode('/', dirname($image_new));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

			if ($width_orig != $width || $height_orig != $height) {
				$image = new Image(DIR_IMAGE . $image_old);
				$image->resize($width, $height);
				$image->save(DIR_IMAGE . $image_new, 75);
			} else {
				copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
			}
		}

		return HTTP_CATALOG . 'image/' . $image_new;
	}
}