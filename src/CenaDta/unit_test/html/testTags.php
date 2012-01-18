<?php
ob_start();
require_once( dirname( __FILE__ ) . "/../../class/class.ext_func.php" );
require_once( dirname( __FILE__ ) . "/../../Html/Prop.php" );
require_once( dirname( __FILE__ ) . "/../../Html/Tags.php" );
use CenaDta\Html as html;

class HtmlTagsTest extends PHPUnit_Framework_TestCase
{
	// +----------------------------------------------------------------------+
	public function setUp()
	{
	}
	// +----------------------------------------------------------------------+
	// test suites for html\Tags class.
	// +----------------------------------------------------------------------+
	public function test_htmlTags_htmlText()
	{
		$name = 'input_text';
		$text = 'input#text';
		$size = 31;
		$max  = 32;
		$option = array(
			'size'       => $size,
			'maxlength'  => $max,
		);
		$elem = html\Tags::inputText( $name, $text, $option );
		$this->assertContains( " type=\"text\"",        $elem );
		$this->assertContains( " name=\"{$name}\"",     $elem );
		$this->assertContains( " value=\"{$text}\"",    $elem );
		$this->assertContains( " size=\"{$size}\"",     $elem );
		$this->assertContains( " maxlength=\"{$max}\"", $elem );
		
		// test using htmlProp. kind of testing html\Prop though...
		$prop = new html\Prop( $option );
		$prop->setIME( 'OFF' );
		$property = $prop->getOptions();
		$elem = html\Tags::inputText( $name, $text, $property );
		$this->assertContains( " type=\"text\"",        $elem );
		$this->assertContains( " name=\"{$name}\"",     $elem );
		$this->assertContains( " value=\"{$text}\"",    $elem );
		$this->assertContains( " size=\"{$size}\"",     $elem );
		$this->assertContains( " maxlength=\"{$max}\"", $elem );
		$this->assertContains( " style=\"ime-mode:inactive\"", $elem );
		
		$name = 'input_text';
		$text = 'input<strong>text</strong>';
		$safe = html_safe( $text );
		$size = 31;
		$max  = 32;
		$option = array(
			'size'       => $size,
			'maxlength'  => $max,
		);
		$elem = html\Tags::inputText( $name, $text, $option );
		$this->assertContains( " type=\"text\"",        $elem );
		$this->assertContains( " name=\"{$name}\"",     $elem );
		$this->assertContains( " value=\"{$safe}\"",    $elem );
		$this->assertContains( " size=\"{$size}\"",     $elem );
		$this->assertContains( " maxlength=\"{$max}\"", $elem );
		$this->assertNotContains( " value=\"{$text}\"",    $elem );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlTags_varfooter()
	{
		$footer = '[test]';
		html\Tags::setVarFooter( $footer );
		$name = 'input_text';
		$text = 'input#text';
		$size = 31;
		$max  = 32;
		$option = array(
			'size'       => $size,
			'maxlength'  => $max,
		);
		$elem = html\Tags::inputText( $name, $text, $option );
		$this->assertContains( " type=\"text\"",             $elem );
		$this->assertContains( " name=\"{$name}{$footer}\"", $elem );
		$this->assertContains( " value=\"{$text}\"",         $elem );
		$this->assertContains( " size=\"{$size}\"",          $elem );
		$this->assertContains( " maxlength=\"{$max}\"",      $elem );
		
		html\Tags::setVarFooter( '' );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlTags_varcena()
	{
		$cena = '[scheme][test]';
		html\Tags::setCenaTerm( $cena );
		$name = 'input_text';
		$text = 'input#text';
		$size = 31;
		$max  = 32;
		$option = array(
			'size'       => $size,
			'maxlength'  => $max,
		);
		$elem = html\Tags::inputText( $name, $text, $option );
		$this->assertContains( " type=\"text\"",             $elem );
		$this->assertContains( " name=\"{$cena}[{$name}]\"", $elem );
		$this->assertContains( " value=\"{$text}\"",         $elem );
		$this->assertContains( " size=\"{$size}\"",          $elem );
		$this->assertContains( " maxlength=\"{$max}\"",      $elem );
		
		html\Tags::setCenaTerm( '' );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlForm_htmlTextArea()
	{
		$name   = 'input_area';
		$text   = 'input#textarea';
		$width  = 26;
		$height = 3;
		$option = array(
			'rows'  => $height,
			'cols'  => $width,
		);
		$elem = html\Tags::textArea( $name, $text, $option );
		$this->assertContains( "name=\"{$name}\"",    $elem );
		$this->assertContains( ">{$text}</textarea>", $elem );
		$this->assertContains( "rows=\"{$height}\"",  $elem );
		$this->assertContains( "cols=\"{$width}\"",   $elem );
		
		$name   = 'input_area';
		$text   = 'inputTArea<strong>text</strong>';
		$safe   = html_safe( $text );
		$width  = 26;
		$height = 3;
		$option = array(
			'rows'  => $height,
			'cols'  => $width,
		);
		$elem = html\Tags::textArea( $name, $text, $option );
		$this->assertContains( "name=\"{$name}\"",    $elem );
		$this->assertContains( ">{$safe}</textarea>", $elem );
		$this->assertContains( "rows=\"{$height}\"",  $elem );
		$this->assertContains( "cols=\"{$width}\"",   $elem );
		$this->assertNotContains( ">{$text}</textarea>", $elem );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlForm_htmlHidden()
	{
		$name   = 'input_hidden';
		$text   = 'input#hidden';
		$elem = html\Tags::inputHidden( $name, $text );
		$this->assertContains( "type=\"hidden\"",    $elem );
		$this->assertContains( "name=\"{$name}\"",   $elem );
		$this->assertContains( "value=\"{$text}\"",  $elem );
		
		$name   = 'input_hidden';
		$text   = 'inputHidden<strong>text</strong>';
		$safe   = html_safe( $text );
		$elem = html\Tags::inputHidden( $name, $text );
		$this->assertContains( "type=\"hidden\"",    $elem );
		$this->assertContains( "name=\"{$name}\"",   $elem );
		$this->assertContains( "value=\"{$safe}\"",  $elem );
		$this->assertNotContains( "value=\"{$text}\"",  $elem );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlForm_htmlSelect()
	{
		$name   = 'input_select';
		$size   = 2;
		$head   = 'added head';
		$item[0]= array( 'val 1', 'name 1' );
		$item[1]= array( 'val 2', 'name 2' );
		$option = array(
			'size' => $size,
		);
		$elem = html\Tags::select( $name, $item, $head, $option );
		$this->assertContains( "name=\"{$name}\"", $elem );	
		$this->assertContains( "size=\"{$size}\"", $elem );
		$this->assertContains( "<option value=\"\">{$head}</option>", $elem );
		$this->assertContains( "<option value=\"{$item{0}{0}}\">{$item{0}{1}}</option>", $elem );
		$this->assertContains( "<option value=\"{$item{1}{0}}\">{$item{1}{1}}</option>", $elem );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlForm_htmlRadio()
	{
		$name   = 'input_radio';
		$sep    = '.sep.';
		$item[0]= array( 'val 1', 'name 1' );
		$item[1]= array( 'val 2', 'name 2' );
		$elem = html\Tags::listRadio( $name, $item, $sep );
		list( $e1, $e2 ) = explode( $sep, $elem );
		
		$this->assertContains( "type=\"radio\"",          $e1 );
		$this->assertContains( "name=\"{$name}\"",        $e1 );	
		$this->assertContains( "value=\"{$item{0}{0}}\"", $e1 );
		$this->assertContains( ">{$item{0}{1}}</label>",  $e1 );
		
		$this->assertContains( "type=\"radio\"",          $e2 );
		$this->assertContains( "name=\"{$name}\"",        $e2 );	
		$this->assertContains( "value=\"{$item{1}{0}}\"", $e2 );
		$this->assertContains( ">{$item{1}{1}}</label>",  $e2 );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlForm_htmlCheck()
	{
		$name   = 'input_check';
		$sep    = '.sep.';
		$item[0]= array( 'val 1', 'name 1' );
		$item[1]= array( 'val 2', 'name 2' );
		$elem = html\Tags::listCheck( $name, $item, $sep );
		list( $e1, $e2 ) = explode( $sep, $elem );
		
		$this->assertContains( "type=\"checkbox\"",       $e1 );
		$this->assertContains( "name=\"{$name}[]\"",      $e1 );	
		$this->assertContains( "value=\"{$item{0}{0}}\"", $e1 );
		$this->assertContains( ">{$item{0}{1}}</label>",  $e1 );
		
		$this->assertContains( "type=\"checkbox\"",       $e2 );
		$this->assertContains( "name=\"{$name}[]\"",      $e2 );	
		$this->assertContains( "value=\"{$item{1}{0}}\"", $e2 );
		$this->assertContains( ">{$item{1}{1}}</label>",  $e2 );
	}
	// +----------------------------------------------------------------------+
}


?>