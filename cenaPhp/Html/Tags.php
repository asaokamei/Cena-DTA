<?php
namespace CenaDta\Html;
/**
 *	Class for HTML tags. 
 *
 * @copyright     Copyright 2010-2011, Asao Kamei
 * @link          http://www.workspot.jp/cena/
 * @license       GPLv2
 */
require_once( dirname( __FILE__ ) . '/Prop.php' );

// +----------------------------------------------------------------------+
/**
 * collection of static methods to format HTML tags. 
 * 
 * mostly generate HTML elements used in forms, such as <SELECT.., 
 * <INPUT..., and more. 
 * uses Prop instance to manage property of tags. 
 * 
 */
class Tags
{
	static $this_wordy   = 3;   // debug output level. 
    /**
     * string added to name of tag. 
     * if the name is 'your_name', and var_footer = '[1]', 
     * the tag's name will be name="your_name[1]". 
     * 
     * @var string
     */
	static $var_footer   = '';  // add to var_name (i.e. var[1])
    /**
     * a string to incorporate cena style name.
     * 
     * @var string 
     */
	static $var_format   = '';  // for cena enabled forms.
    // +------------------------------------------------------------------+
	// static methods.
    // +------------------------------------------------------------------+
    /**
     * wrapper for htmlspecialchars. 
     * @param string &$data 
     */
    static function safe( &$data ) {
		$data = htmlspecialchars( $data );
    }
    // +------------------------------------------------------------------+
    /**
     * generates tag's name using var_format and var_footer. 
     * 
     * @param  string $var_name  name of tag, such as 'your_name'.
     * @return string            formated tag name. 
     */
    static function getVarName( $var_name ) {
		if( have_value( self::$var_format ) ) {
			return sprintf( self::$var_format, $var_name );
		}
		else
		if( have_value( self::$var_footer ) ) {
			return $var_name . self::$var_footer;
		}
		return $var_name;
    }
    // +------------------------------------------------------------------+
    /**
     * sets var_footer. 
     * 
     * @param  string $footer
     * @return Tags 
     */
    static function setVarFooter( $footer ) {
		self::$var_footer = $footer;
    }
    // +------------------------------------------------------------------+
    /**
     * sets var_format for cena style name. for instance, 
     * var_format = 'Cena[tab][col]' and name='var_name', 
     * the result be 'Cena[tab][col][var_name]'.
     * 
     * @param  string $cena_term   cena's body. 
     * @return Tags                tag's name
     */
    static function setCenaTerm( $cena_term ) {
		if( have_value( $cena_term ) ) {
			self::$var_format = $cena_term . '[%s]';
		}
		else {
			self::$var_format = '';
		}
    }
    // +------------------------------------------------------------------+
    /**
     * generates id name of a tag. 
     * 
     * replaces '.', '[', and ']', to '_' to make valid id name. 
     * 
     * @param  string $var_name   name of tag
     * @return string             id name of tag
     */
    static function getIdName( $var_name ) {
		if( substr( $var_name, -2, 2 ) === '[]' ) {
			$var_name = substr( $var_name, 0, -2 );
		}
		$var_name = self::getVarName( $var_name );
		$replace  = array( '.', '[', ']' );
		$id_name  = str_replace( $replace, '_', $var_name );
		return $id_name;
    }
    // +------------------------------------------------------------------+
    /**
     * rename option name. 
     * 
     * @param arary &$option
     * @param string $old     original key name to be replaced.
     * @param string $new     new key name. 
     */
    static function changeOption( &$option, $old, $new ) {
		if( isset( $option[ $old ] ) && !isset( $option[ $new ] ) ) {
			$option[ $new ] = $option[ $old ];
			unset( $option[ $old ] );
		}
    }
    // +------------------------------------------------------------------+
    /**
     * formats tag's property based on prop data. 
     * 
     * @param  mix $prop    property data. may be Prop or an array.
     * @return string       tag's property. 
     */
    static function getProperty( $prop ) 
	{
		$property = $prop;
		if( is_object( $prop ) && method_exists( $prop, 'getProp' ) ) {
			$property = $prop->getProp();
		}
		else
		if( is_array( $prop ) ) {
			$property = Prop::makeProp( $prop );
		}
		if( WORDY > 3 ) wordy_table( $prop, $property );
		return $property;
    }
    // +------------------------------------------------------------------+
    /**
     * checks if the value is in selected (value or an array). 
     * 
     * used for default value of select, radio, check elements. 
     * 
     * @param  string $val    checks if the value is in the $selected. 
     * @param  mix $selected  a value, or array of values. 
     * @return boolean        returns TRUE if $val is in $selected. 
     */
    static function checkSelected( $val, $selected )
    {
		$found = FALSE;
		if( $selected !== FALSE ) {
			if( is_array( $selected ) && in_array( $val, $selected ) ) {
				$found = TRUE;
			}
			else
			if( $val == $selected ) {
				$found = TRUE;
			}
		}
		if( WORDY > 5 ) echo "checkSelected( $val, $selected ) => $found / ";
		return $found;
	}
    // +------------------------------------------------------------------+
	// make html tags. mostly form elements. 
    // +------------------------------------------------------------------+
    /**
     * generates HTML tags that are sandwiched i.e. <tag>...</tag>
     * 
     * @param  string $tag      type of tag, such as INPUT, etc.
     * @param  string $content  contents sandwiched with tags.
     * @param  mix    $option   property in Prop or array. 
     * @param  string $term     termination string. 
     * @return string           html element.
     */
    static function makeTag( $tag, $content, $option, $term='' ) {
		$html = "<{$tag}" . self::getProperty( $option ) . ">{$content}</{$tag}>{$term}";
		return $html;
    }
    // +------------------------------------------------------------------+
    /**
     * generates HTML alone tags; i.e. <tag ... />
     * 
     * @param  string $tag      type of tag, such as INPUT, etc.
     * @param  mix    $option   property in Prop or array. 
     * @param  string $term     termination string. 
     * @return string           html tag.
     */
    static function aloneTag( $tag, $option, $term='' ) {
		$html = "<{$tag}" . self::getProperty( $option ) . " />{$term}";
		return $html;
    }
    // +------------------------------------------------------------------+
    /**
     * create <input type=... type HTML element. 
     * 
     * @param  string $type      type of input; TEXT, HIDDEN, etc. 
     * @param  string $var_name  name of HTML tag. 
     * @param  array  $option    property of element. 
     * @return string            HTML element. 
     */
    static function inputType( $type, $var_name, $option )
    {
		if( WORDY > 3 ) wt( $option, "htmlText( $type, $var_name, ... )<br>" );
		if( !have_value( $var_name ) ) return FALSE;
		$default  = array( 
			'type'  => $type,
			'name'  => self::getVarName( $var_name ), 
			'id'    => self::getIdName(  $var_name )
		);
		$option = array_merge( $default, $option );
		if( isset( $option[ 'value' ] ) ) { self::safe( $option[ 'value' ] ); }
		$html   = self::aloneTag( 'input ', $option );
		return $html;
    }
    // +------------------------------------------------------------------+
    /**
     * generates input type text HTML elements. 
     * 
     * @param  string $var_name   name of tag. 
     * @param  string $text       default value. 
     * @param  array  $option     property of element. 
     * @return string             html element. 
     */
    static function inputText( $var_name, $text=NULL, $option=array() )
    {
		$default  = array( 
			'value' => $text, 
			'size'  => 30 
		);
		$option = array_merge( $default, $option );
		return self::inputType( 'text', $var_name, $option );
	}
    // +------------------------------------------------------------------+
    /**
     * generates input type hidden HTML elements. 
     * 
     * @param  string $var_name   name of tag. 
     * @param  string $text       default value. 
     * @param  array  $option     property of element. 
     * @return string             html element. 
     */
    static function inputHidden( $var_name, $text=NULL, $option=array() )
    {
		$default  = array(
			'value' => $text, 
		);
		$option = array_merge( $default, $option );
		return self::inputType( 'hidden', $var_name, $option );
	}
    // +------------------------------------------------------------------+
    /**
     * generates textarea HTML elements. 
     * 
     * @param  string $var_name   name of tag. 
     * @param  string $text       default value. 
     * @param  array  $option     property of element. 
     * @return string             html element. 
     */
    static function textArea( $var_name, $text=NULL, $option=array() )
    {
		if( WORDY > 3 ) echo "htmlTextArea( $var_name, $text )<br>";
		if( !have_value( $var_name ) ) return FALSE;
		self::safe( $text );
		self::changeOption( $option, 'width',  'cols' );
		self::changeOption( $option, 'height', 'rows' );
		$default  = array(
			'name'  => self::getVarName( $var_name ), 
			'id'    => self::getIdName(  $var_name ), 
			'cols'  => 40, 
			'rows'  => 5 
		);
		$option = array_merge( $default, $option );
		return self::makeTag( 'textarea', $text, $option );
	}
    // +------------------------------------------------------------------+
    /**
     * generates input type=radio HTML elements. 
     * 
     * @param  string $var_name   name of tag. 
     * @param  string $text       default value. 
     * @param  array  $option     property of element. 
     * @return string             html element. 
     */
    static function inputRadio( $var_name, $val, $option=array() )
    {
		if( !have_value( $var_name ) ) return FALSE;
		$id_name  = self::getIdName( $var_name );
		$id       = "{$id_name}_{$val}";
		$default  = array( 
			'type'  => 'radio',
			'name'  => self::getVarName( $var_name ), 
			'id'    => $id,
			'value' => $val, 
		);
		$option = array_merge( $default, $option );
		$html   = self::aloneTag( 'input', $option );
        return $html;
	}
    // +------------------------------------------------------------------+
    /**
     * generates input type=checkbox HTML elements. 
     * 
     * @param  string $var_name   name of tag. 
     * @param  string $text       default value. 
     * @param  array  $option     property of element. 
     * @return string             html element. 
     */
    static function inputCheck( $var_name, $val, $option=array() )
    {
		if( !have_value( $var_name ) ) return FALSE;
		$id_name  = self::getIdName( $var_name );
		$id       = "{$id_name}_{$val}";
		$default  = array( 
			'type'  => 'checkbox',
			'name'  => self::getVarName( $var_name ), 
			'id'    => $id,
			'value' => $val, 
		);
		$option = array_merge( $default, $option );
		$html   = self::aloneTag( 'input', $option );
        return $html;
	}
    // +------------------------------------------------------------------+
   /**
    * generates only one input type=checkbox HTML element. 
    * 
    * quick access to make on/off using checkbox. example: 
    *   <input type=check name=var_name value=val1>
    * 
    * @param  string  $var_name   name of check element.
    * @param  string  $val1       value of check element.
    * @param  boolean $checked    set to TRUE to be checked. 
    * @param  array   $option     property of element. 
    * @return string              html element. 
    */
    static function checkOne( $var_name, $val1, $checked=FALSE, $option=array() )
    {
        // quick access to make on/off using checkbox.
        //   <input type=hidden name=var_name value=val1>
        //   <input type=check  name=var_name value=val2>
		if( $checked ) $option[] = 'checked';
		$html = self::inputCheck(  $var_name, $val1, $option );
		return $html;
    }
    // +------------------------------------------------------------------+
    /**
     * 
     * generates a hidden element and an input type=checkbox HTML element. 
     * 
     * quick access to make on/off using checkbox. example: 
     *   <input type=hidden name=var_name value=val1>
     *   <input type=check  name=var_name value=val2>
     * 
     * @param  string  $var_name   name of check element.
     * @param  string  $val1       value of hidden element.
     * @param  string  $val2       value of check element.
     * @param  boolean $checked    set to TRUE to be checked. 
     * @param  array   $option     property of element. 
     * @return string              HTML element. 
     */    static function checkTwo( $var_name, $val1, $val2, $checked=FALSE, $option=array() )
    {
		if( $checked ) $option[] = 'checked';
		$html  = self::inputHidden( $var_name, $val1 ) . "\n";
		$html .= self::inputCheck(  $var_name, $val2, $option );
		return $html;
    }
    // +------------------------------------------------------------------+
	// making check/radio listing
    // +------------------------------------------------------------------+
    /**
     * generates select listing HTML element. 
     * 
     * creates select like...
     * <select name=\"$var_name\">
     *      <option name=$items[$i][0]>$items[$i][1]</option>
     *  </select>
     *  $var_name: name of html form name
     *  $items   : an array pair of code/name.
     *           : $items[$i][0] = code value
     *           : $items[$i][1] = display name
     *           : $items[$i][2] = group label
     * warning: apply group label to all items to use group function. 
     * 
     * @param  string $var_name  name of select element. 
     * @param  array $items      list of items. 
     * @param  string $head      adds empty item at the beginnig of list. 
     * @param  array $option     property of select element. 
     * @param  mix $selected     list/value of value to be checked. 
     * @param  mix $disabled     list/value of value to be disabled. 
     * @return string            select element. 
     */
    static function select( 
		$var_name,         // tag name
		$items,            // list of items for options.
		$head=FALSE,       // add head option with no value.
		$option=array(),   // optional property (i.e. class, etc.)
		$selected=FALSE,   // list of values of selected options.
		$disabled=FALSE    // list of values of disabled options.
	)
    {
        $prev_label = NULL;
		$html       = '';
		$html_group = ''; // html when optgroup is used
        if( have_value( $head ) ) { // adding head option.
			$html .=  self::makeTag( "option", $head, array( 'value'=>'' ), "\n" );
		}
        for( $i = 0; isset( $items[$i][0] ); $i++ )
        {
            $val   = $items[$i][0];
            $text  = $items[$i][1];
			self::safe( $text );
			if( isset( $items[$i][2] ) ) $label = $items[$i][2]; else $label = NULL;
			
			if( have_value( $label ) && $label != $prev_label &&!is_null( $prev_label ) ) {
				$grp_opt     = array( 'label' => $label );
				$html_group .= self::makeTag( 'optgroup', $html, $grp_opt, "\n" );
				$html  = "";
				$prev_label = $label;
			}
			$opt_option = array( 'value' => $val );
            if( self::checkSelected( $val, $selected ) ) {
				$opt_option[ "selected" ] = "selected";
            }
			if( self::checkSelected( $val, $disabled ) ) {
				$opt_option[ "disabled" ] = "disabled";
			}
            $html .= self::makeTag( 'option', $text, $opt_option, "\n" );
        }
		if( !is_null( $prev_label ) ) {
			$html_group .= self::makeTag( 'optgroup', $html, array( 'label'=>$label ), "\n" );
		}
		$default = array(
			'name'  => self::getVarName( $var_name ), 
			'id'    => self::getIdName(  $var_name ), 
			'size'  => 1, 
		);
		$option = array_merge( $default, $option );
		if( isset( $option[ 'multiple' ] ) ) {
			$option[ 'name' ] .= "[]";
		}
        $html  = self::makeTag( "select", $html . $html_group, $option );
        return $html;
    }
    // +------------------------------------------------------------------+
    /**
     * generates input type=radio. see argument for listItems.
     * 
     * @return string   HTML element
     */
    static function listRadio( 
		$var_name,        // tag name
		$items,           // list of radio items
		$sep=', ',        // seperater text.
		$chop=0,          // add break-line 
		$option=array(),     // option for radio tag.
		$header=NULL,     // optional head item
		$checked=FALSE,   // checked values
		$disabled=FALSE   // disabled values
	) {
		return self::listItems( 
			'inputRadio', $var_name, 
			$items, $sep, $chop, $option, $header, $checked, $disabled );
	}
    // +------------------------------------------------------------------+
    /**
     * generates input type=checkbox. see argument for listItems.
     * 
     * notice that var_name will become an array. i.e. name="var_name[]".
     * 
     * @return string   HTML element
     */
    static function listCheck( 
		$var_name,        // tag name
		$items,           // list of radio items
		$sep=', ',        // seperater text.
		$chop=0,          // add break-line 
		$option=array(),     // option for radio tag.
		$header=NULL,     // optional head item
		$checked=FALSE,   // checked values
		$disabled=FALSE   // disabled values
	) {
		return self::listItems( 
			'inputCheck', $var_name . '[]', 
			$items, $sep, $chop, $option, $header, $checked, $disabled );
	}
    // +------------------------------------------------------------------+
    /**
     * generates list of radio/checkbox elements. 
     * 
     * creates elements like
     * <input type="radio or checkbox" value=$items[$i][0]>$items[$i][1]
     *  $var_name: name of html form name
     *  $items   : an array pair of code/name.
     *           : $items[$i][0] = code value
     *           : $items[$i][1] = display name
     * 
     * @param  type $method     inputRadio, inputCheck, etc.
     * @param  type $var_name   name of element.
     * @param  type $items      list of items.
     * @param  type $sep        seperater text, such as '<br />'.
     * @param  type $chop       add break-line every $chop items. 
     * @param  type $option     option of element.
     * @param  type $header     optional head item with empty value. 
     * @param  type $checked    array of values to be checked.
     * @param  type $disabled   array of values to be disabled. 
     * @return type             HTML element. 
     */
    static function listItems( 
		$method,  
		$var_name,
		$items,   
		$sep=', ',
		$chop=0,  
		$option=array(),
		$header=NULL,   
		$checked=FALSE, 
		$disabled=FALSE 
	) {
        // DESCRIPTION create form/select like...
        // <select name=\"$var_name\">
        //     <option name=$items[$i][0]>$items[$i][1]
        // </select>
        // 
        // $var_name: name of html form name
        // $items   : an array pair of code/name.
        //          : $items[$i][0] = code value
        //          : $items[$i][1] = display name
        // $checked : used as checked item 
		if( !$var_name ) return FALSE;
		if( empty( $items ) ) return FALSE;
		$html  = '';
		$count = 0;
		if( have_value( $header ) ) {
			$html = self::$method( $var_name, '', $option );
			$count ++;
		}
		$html_list = array();
        foreach ( $items as $item )
        {
            $val   = $item[0];
            $text  = $item[1];
			self::safe( $text );
			if( $chop > 0 ) {
				if( $count > 0 && $count % $chop == 0 ) $html .= "<br />\n";
			}
			$item_option = $option; // copy
            if( self::checkSelected( $val, $checked ) ) {
                $item_option[ 'checked' ] = "checked";
            }
			if( self::checkSelected( $val, $disabled ) ) {
                $item_option[ 'disabled' ] = "disabled";
			}
			$input = self::$method( $var_name, $val, $item_option );
            $html .= self::makeTag( "label", $input . $text, array() );
			$html_list[] = $html;
			$html = '';
			$count++;
        }
		$html = implode( $sep, $html_list );
        return $html;
    }
    // +------------------------------------------------------------------+
}



?>