<?php
ob_start();
require_once( dirname( __FILE__ ) . "/../../../cenaPhp/class/class.ext_func.php" );
require_once( dirname( __FILE__ ) . "/../../../cenaPhp/Html/Form.php" );
require_once( 'htmlTest.inc.php' );
use CenaDta\Html as html;

class HtmlFormTest extends PHPUnit_Framework_TestCase
{
	// +----------------------------------------------------------------------+
	public function setUp()
	{
	}
	// +----------------------------------------------------------------------+
	// test suites for htmlForm.
	// +----------------------------------------------------------------------+
	public function test_htmlForm_formCheck()
	{
		$name = 'conn_type';
		$size = 1;
		$html = new sel_connect_check( $name, NULL, NULL, 'OFF' );
		$item = get_htmlFormTest_item();
		$head = $html->add_head_option;
		$this->assertEquals( $item[0][1], $html->show( 'NAME', '1' ) );
		$this->assertEquals( $item[2][1], $html->show( 'NAME', '3' ) );
		
		$sep = $html->item_sep;
		$elem = $html->show( 'NEW' );
		$e = explode( $sep, $elem );
		
		$this->assertContains( "type=\"checkbox\"",       $e[0] );
		$this->assertContains( "name=\"{$name}[]\"",      $e[0] );	
		$this->assertContains( "value=\"{$item{0}{0}}\"", $e[0] );
		$this->assertContains( ">{$item{0}{1}}</label>",  $e[0] );
		
		$this->assertContains( "type=\"checkbox\"",       $e[1] );
		$this->assertContains( "name=\"{$name}[]\"",      $e[1] );	
		$this->assertContains( "value=\"{$item{1}{0}}\"", $e[1] );
		$this->assertContains( ">{$item{1}{1}}</label>",  $e[1] );
		
		$elem = $html->show( 'EDIT', $item[2][0] );
		echo "\nVisual Check - Check:" . $elem;
		$e = explode( $sep, $elem );
		$this->assertContains( "type=\"checkbox\"",       $e[2] );
		$this->assertContains( "name=\"{$name}[]\"",      $e[2] );	
		$this->assertContains( "value=\"{$item{2}{0}}\"", $e[2] );
		$this->assertContains( ">{$item{2}{1}}</label>",  $e[2] );
		$this->assertContains( "checked=\"checked\"",     $e[2] );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlForm_formRadio()
	{
		$name = 'conn_type';
		$size = 1;
		$html = new sel_connect_radio( $name, NULL, NULL, 'OFF' );
		$item = get_htmlFormTest_item();
		$head = $html->add_head_option;
		$this->assertEquals( $item[0][1], $html->show( 'NAME', '1' ) );
		$this->assertEquals( $item[2][1], $html->show( 'NAME', '3' ) );
		
		$sep = $html->item_sep;
		$elem = $html->show( 'NEW' );
		$e = explode( $sep, $elem );
		
		$this->assertContains( "type=\"radio\"",          $e[0] );
		$this->assertContains( "name=\"{$name}\"",        $e[0] );	
		$this->assertContains( "value=\"{$item{0}{0}}\"", $e[0] );
		$this->assertContains( ">{$item{0}{1}}</label>",  $e[0] );
		
		$this->assertContains( "type=\"radio\"",          $e[1] );
		$this->assertContains( "name=\"{$name}\"",        $e[1] );	
		$this->assertContains( "value=\"{$item{1}{0}}\"", $e[1] );
		$this->assertContains( ">{$item{1}{1}}</label>",  $e[1] );
		
		$elem = $html->show( 'EDIT', $item[2][0] );
		echo "\nVisual Check - Radio:" . $elem;
		$e = explode( $sep, $elem );
		$this->assertContains( "type=\"radio\"",          $e[2] );
		$this->assertContains( "name=\"{$name}\"",        $e[2] );	
		$this->assertContains( "value=\"{$item{2}{0}}\"", $e[2] );
		$this->assertContains( ">{$item{2}{1}}</label>",  $e[2] );
		$this->assertContains( "checked=\"checked\"",    $e[2] );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlForm_formSelect()
	{
		$name = 'conn_type';
		$size = 1;
		$html = new sel_connect_type( $name, NULL, NULL, 'OFF' );
		$item = get_htmlFormTest_item();
		$head = $html->add_head_option;
		$this->assertEquals( $item[0][1], $html->show( 'NAME', '1' ) );
		$this->assertEquals( $item[2][1], $html->show( 'NAME', '3' ) );
		
		$elem = $html->show( 'NEW' );
		$this->assertContains( "name=\"{$name}\"", $elem );	
		$this->assertContains( "size=\"{$size}\"", $elem );
		$this->assertContains( "<option value=\"\">{$head}</option>", $elem );
		$this->assertContains( "<option value=\"{$item{0}{0}}\">{$item{0}{1}}</option>", $elem );
		$this->assertContains( "<option value=\"{$item{1}{0}}\">{$item{1}{1}}</option>", $elem );
		
		$elem = $html->show( 'EDIT', '3' );
		$this->assertContains( "name=\"{$name}\"", $elem );	
		$this->assertContains( "size=\"{$size}\"", $elem );
		$this->assertContains( "<option value=\"\">{$head}</option>", $elem );
		$this->assertContains( "<option value=\"{$item{0}{0}}\">{$item{0}{1}}</option>", $elem );
		$this->assertContains( "<option value=\"{$item{1}{0}}\">{$item{1}{1}}</option>", $elem );
		$this->assertContains( "<option value=\"{$item{2}{0}}\" selected=\"selected\">{$item{2}{1}}</option>", $elem );
		echo "\nVisual Check - Select:" . $elem;
	}
	// +----------------------------------------------------------------------+
	public function test_htmlForm_formText()
	{
		$name = 'html_text';
		$text = 'input#text';
		$size = 31;
		$max  = 32;
		$html = new html\formText( $name, $size, $max, 'ON' );
		
		$elem = $html->show( 'NAME', $text );
		$this->assertEquals( $text, $elem );
		
		$elem = $html->show( 'EDIT', $text );
		echo "\nVisual Check - Text:" . $elem;
		$this->assertContains( " type=\"text\"",        $elem );
		$this->assertContains( " name=\"{$name}\"",     $elem );
		$this->assertContains( " value=\"{$text}\"",    $elem );
		$this->assertContains( " size=\"{$size}\"",     $elem );
		$this->assertContains( " maxlength=\"{$max}\"", $elem );
		$this->assertContains( " style=\"ime-mode:active\"", $elem );
		
		$name = 'html_text';
		$text = 'input<strong>text</strong>';
		$safe = html_safe( $text );
		$size = 31;
		$max  = 32;
		$html = new html\formText( $name, $size, $max, 'OFF' );
		
		$elem = $html->show( 'NAME', $text );
		$this->assertEquals(    $safe, $elem );
		$this->assertNotEquals( $text, $elem );
		
		$elem = $html->show( 'EDIT', $text );
		$this->assertContains( " type=\"text\"",        $elem );
		$this->assertContains( " name=\"{$name}\"",     $elem );
		$this->assertContains( " value=\"{$safe}\"",    $elem );
		$this->assertContains( " size=\"{$size}\"",     $elem );
		$this->assertContains( " maxlength=\"{$max}\"", $elem );
		$this->assertContains( " style=\"ime-mode:inactive\"", $elem );
		$this->assertNotContains( " value=\"{$text}\"",     $elem );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlForm_formTextArea()
	{
		$name   = 'input_area';
		$text   = 'input#textarea';
		$width  = 26;
		$height = 3;
		$html = new html\formTextArea( $name, $width, $height, 'ON' );
		
		$elem = $html->show( 'NAME', $text );
		$this->assertEquals(    $text, $elem );
		
		$elem = $html->show( 'EDIT', $text );
		$this->assertContains( "name=\"{$name}\"",    $elem );
		$this->assertContains( ">{$text}</textarea>", $elem );
		$this->assertContains( "rows=\"{$height}\"",  $elem );
		$this->assertContains( "cols=\"{$width}\"",   $elem );
		$this->assertContains( " style=\"ime-mode:active\"", $elem );
		
		$name   = 'input_area';
		$text   = "input\nTArea<strong>text</strong>";
		$safe   = html_safe( $text );
		$disp   = nl2br( html_safe( $text ) );
		$nlbr   = nl2br( $text );
		$width  = 26;
		$height = 3;
		$html = new html\formTextArea( $name, $width, $height, 'ON' );
		
		$elem = $html->show( 'NAME', $text ); // very safe output 
		$this->assertEquals(       $disp, $elem );
		$this->assertNotEquals(    $safe, $elem );
		$this->assertNotEquals(    $text, $elem );
		
		$html->make_name_funcs = array( 'nl2br' ); // not stripping tags
		$elem = $html->show( 'NAME', $text );
		$this->assertEquals( $nlbr, $elem ); // only nl2br (tags are present).
		
		$elem = $html->show( 'EDIT', $text );
		$this->assertContains( "name=\"{$name}\"",    $elem );
		$this->assertContains( ">{$safe}</textarea>", $elem );
		$this->assertContains( "rows=\"{$height}\"",  $elem );
		$this->assertContains( "cols=\"{$width}\"",   $elem );
		$this->assertContains( " style=\"ime-mode:active\"", $elem );
		$this->assertNotContains( ">{$text}</textarea>", $elem );
	}
	// +----------------------------------------------------------------------+
}


?>