<?php
namespace CenaDta\Html;
/**
 *	Class for HTML properties. 
 *
 * @copyright     Copyright 2010-2011, Asao Kamei
 * @link          http://www.workspot.jp/cena/
 * @license       GPLv2
 */

// +----------------------------------------------------------------------+
/**
 * manages properties of html tags.
 */
class Prop
{
	/**
     * a static array for default properties. 
     * 
     * a static array that is automatically added to 
     * options of all instances of tags.
     * 
     * @var array $default
     */
	static $default      = array();
    /**
     * an array to keep properties. 
     * 
     * data is an array contains property data; 
     * example: $data = array(
     *    'class'  => 'some_class',
     *    'style' => 'a:b; c:d',... 
     * );
     * 
     * @var array $options
     */
	var    $options      = array();
    // +------------------------------------------------------------------+
    /**
     * constructor.
     * 
     * @param mix $option 
     * initial option to set. 
     */
    function __construct( $option=NULL ) {
		if( $option ) $this->setOption( $option );
	}
    // +------------------------------------------------------------------+
	// static method for default
    // +------------------------------------------------------------------+
    /**
     * a generic routine to set property options. 
     * 
     * @param array &$data
     * an array contains properties. 
     * @param string $key
     * name of the property, such as 'class', or 'style'.
     * @param char $val
     * value of the property, such as 'red'.
     * @param string $sep 
     * separator to concatnate property values. for instance, 
     * for space is uses for classes, and ';' is used for style. 
     * set to FALSE to replace existing value with the new value. 
     */
    static function setArray( &$data, $key, $val, $sep=FALSE )
    {
		if( $key == 'class' ) $sep = ' ';
		if( $key == 'style' ) $sep = ';';
		if( isset( $data[ $key ] ) ) {
			if( $sep === FALSE ) {
				$data[ $key ] = $val;
			}
			else {
				$data[ $key ] .= $sep . $val;
			}
		}
		else {
			$data[ $key ]  = $val;
		}
		if( WORDY > 4 ) wt( $data, "setArray( \$data, $key, $val, $sep )" );
	}
    // +------------------------------------------------------------------+
    /**
     * sets default property data. 
     * 
     * @param string $key  
     * name of property, such as 'class'.
     * @param string $val  
     * value of property, such as 'red'.
     * @param string $sep  
     */
    static function setDefault( $key, $val, $sep=FALSE ) {
		self::setArray( self::$default, $key, $val, $sep );
	}
    // +------------------------------------------------------------------+
    /**
     * clears default property. 
     */
    static function clearDefault() {
		self::$default = array();
	}
    // +------------------------------------------------------------------+
    /**
     * a helper method to merge two arrays of properties. 
     * 
     * @param array $opt1  array of property to be merged.
     * @param array $opt2  array of property to merge.
     * @return array       array of resulting property. 
     */
    static function mergeOptions( $opt1, $opt2 )
    {
		if( !empty( $opt2 ) && is_array( $opt2 ) )
		foreach( $opt2 as $key => $val ) {
			self::setArray( $opt1, $key, $val );
		}
		return $opt1;
	}
    // +------------------------------------------------------------------+
    /**
     * formats property options to HTML tag's property format. 
     * 
     * @param array   $options      array of options to format. 
     * @param boolean $add_default  set to TRUE to use default property. 
     * @return string               string of HTML tag's property. 
     */
    static function makeProp( $options, $add_default=TRUE )
    {
		$default = self::$default;
		
		// merge default with options.
		if( $add_default && !empty( self::$default ) )
		{
			$options = self::mergeOptions( self::$default, $options );
		}
		// merge htmlPrpo val
		foreach( $options as $key => $val ) 
		{
			if( is_object( $val ) && get_class( $val ) == 'htmlProp' ) {
				$options = self::mergeOptions( $options, $val->getOptions );
			}
		}
		$html    = '';
		if( !empty( $options ) )
		foreach( $options as $key => $val ) 
		{
			if( $val === FALSE ) {
				$html .= " {$key}";
			}
			else {
				$html .= " {$key}=\"{$val}\"";
			}
		}
		return $html;
	}
    // +------------------------------------------------------------------+
	// generic instanced method
    // +------------------------------------------------------------------+
    /**
     * sets options for properties of tags.
     * 
     * $option can be
     *   an array: array( 'key' => 'val', 'key2' => 'val2',... )
     *   a string: "key=>val | key2=>val2, ..."
     * 
     * @param mix $option
     * @return none 
     */
    function setOption( $option ) 
	{
		// $option is... 
		//  - 
		//  - 
		if( WORDY > 4 ) wt( $option, "setOption( $option )" );
		if( empty( $option ) ) return;
		if( !is_array( $option ) ) 
		{
			$list = explode( '|', $option );
			if( !empty( $list ) )
			foreach( $list as $keyval ) {
				$keyval = trim( $keyval );
				if( strstr( $keyval, '=>' ) ) {
					list( $key, $val ) = explode( '=>', $keyval, 2 );
					self::setArray( $this->options, $key, $val );
				}
				else {
					self::setArray( $this->options, $keyval, $keyval );
				}
			}
		}
		else 
		{
			foreach( $option as $key => $val ) {
				if( is_integer( $key ) ) {
					self::setArray( $this->options, $val, $val );
				}
				else
				if( $key == 'ime' ) {
					$this->setIME( $val );
				}
				else {
					self::setArray( $this->options, $key, $val );
				}
			}
		}
	}
    // +------------------------------------------------------------------+
    function getOptions()
    {
		if( empty( $this->options ) ) ; return $this->options;
		
		// this method is not implemented... 
		// well, the loop is not implemented/tested.
		$options = $this->options;
		foreach( $this->options as $key => $val ) {
			if( is_object( $val ) && get_class( $val ) == 'htmlProp' ) {
				$options = self::mergeOptions( $val->getOptions(), $options );
			}
		}
		return $options;
	}
    // +------------------------------------------------------------------+
    /**
     * adds options. 
     * 
     * @param string $key   name of property, such as 'class'.
     * @param string $val   value of property, such as 'red'.
     * @param string $sep   separator to concatenate value. 
     */
    function addOption( $key, $val, $sep=FALSE ) {
		self::setArray( $this->options, $key, $val, $sep );
	}
    // +------------------------------------------------------------------+
    /**
     * deletes property/value.
     * 
     * @param type $key   name of property to delete. 
     */
    function delOption( $key ) {
		unset( $this->options[ $key ] );
	}
    // +------------------------------------------------------------------+
    /**
     * adds class name to property. 
     * 
     * @param type $class   class name 
     * @return 
     */
    function addClass( $class ) {
		return self::setArray( $this->options, 'class', $class );
	}
    // +------------------------------------------------------------------+
    /**
     * adds style to property. 
     * 
     * @param type $style   value of style. 
     * @return type 
     */
    function addStyle( $style ) {
		return self::setArray( $this->options, 'style', $style );
	}
    // +------------------------------------------------------------------+
    /**
     *  cleans up options. 
     */
    function clearOptions() {
		$this->options = array();
	}
    // +------------------------------------------------------------------+
    /**
     * sets IME style for IE/docomo.
     *  
     * @param type $ime   a simplified IME style.
     */
    function setIME( $ime ) {
		if( WORDY > 5 ) echo "html_forms::setIME( $ime )<br>";
		$ime = strtoupper( trim( $ime ) );
		$ime_style = array(
			'ON'  => 'ime-mode:active',
			'OFF' => 'ime-mode:inactive'
		);
		$ime_opt = array(
			'I1'  => array( 'istyle', '1' ),
			'I2'  => array( 'istyle', '2' ),
			'I3'  => array( 'istyle', '3' ),
			'I4'  => array( 'istyle', '4' ),
		);
		if( isset( $ime_style[ $ime ] ) ) {
			$this->addStyle( $ime_style[ $ime ] );
		}
		else 
		if( isset( $ime_opt[ $ime ] ) ) {
			$this->addOption( $ime_opt[$ime][0], $ime_opt[$ime][1] );
		}
	}
    // +------------------------------------------------------------------+
    /**
     * formats HTML tags' property string. 
     * 
     * @param boolean $add_default  set to TRUE to add default options. 
     * @return string               formatted HTML tags property.  
     */
    function getProp( $add_default=TRUE ) {
		return self::makeProp( $this->options, $add_default );
	}
    // +------------------------------------------------------------------+
}


?>