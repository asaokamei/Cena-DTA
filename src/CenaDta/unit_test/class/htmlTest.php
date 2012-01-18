<?php
ob_start();
require_once( dirname( __FILE__ ) . "/../../class/class.ext_func.php" );
require_once( dirname( __FILE__ ) . "/../../class/class.html_forms.php" );
require_once( dirname( __FILE__ ) . "/../../class/class.html_code.php" );

class HtmlTest extends PHPUnit_Framework_TestCase
{
	// +----------------------------------------------------------------------+
	public function setUp()
	{
	}
	// +----------------------------------------------------------------------+
	public function test_htmlForm_htmlText()
	{
		$name = 'input_text';
		$text = 'input#text';
		$size = 31;
		$max  = 32;
		$elem = html_forms::htmlText( $name, $size, $max, $text );
		$this->assertContains( "type=\"text\"",        $elem );
		$this->assertContains( "name=\"{$name}\"",     $elem );
		$this->assertContains( "value=\"{$text}\"",    $elem );
		$this->assertContains( "size=\"{$size}\"",     $elem );
		$this->assertContains( "maxlength=\"{$max}\"", $elem );
		
		$ime  = 'OFF';
		$sel  = new htmlText( $name, $size, $max, $ime );
		$elem = $sel->show( 'EDIT', $text );
		$this->assertContains( "type=\"text\"",        $elem );
		$this->assertContains( "name=\"{$name}\"",     $elem );
		$this->assertContains( "value=\"{$text}\"",    $elem );
		$this->assertContains( "size=\"{$size}\"",     $elem );
		$this->assertContains( "maxlength=\"{$max}\"", $elem );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlForm_htmlTextIme()
	{
		$name = 'input_text';
		$text = 'input#text';
		$size = 31;
		$max  = 32;
		
		$ime  = 'ON';
		$sel  = new htmlText( $name, $size, $max, $ime );
		$elem = $sel->show( 'EDIT', $text );
		$this->assertContains( "ime-mode: active",   $elem );
		
		$ime  = 'OFF';
		$sel  = new htmlText( $name, $size, $max, $ime );
		$elem = $sel->show( 'EDIT', $text );
		$this->assertContains( "ime-mode: inactive",   $elem );
		
		$ime  = 'I1';
		$sel  = new htmlText( $name, $size, $max, $ime );
		$elem = $sel->show( 'EDIT', $text );
		$this->assertContains( "istyle=\"1\"",   $elem );
		
		$ime  = 'I2';
		$sel  = new htmlText( $name, $size, $max, $ime );
		$elem = $sel->show( 'EDIT', $text );
		$this->assertContains( "istyle=\"2\"",   $elem );
		
		$ime  = 'I3';
		$sel  = new htmlText( $name, $size, $max, $ime );
		$elem = $sel->show( 'EDIT', $text );
		$this->assertContains( "istyle=\"3\"",   $elem );
		
		$ime  = 'I4';
		$sel  = new htmlText( $name, $size, $max, $ime );
		$elem = $sel->show( 'EDIT', $text );
		$this->assertContains( "istyle=\"4\"",   $elem );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlForm_htmlTextArea()
	{
		$name   = 'input_area';
		$text   = 'input#textarea';
		$width  = 26;
		$height = 3;
		$elem = html_forms::htmlTextArea( $name, $width, $height, $text );
		$this->assertContains( "name=\"{$name}\"",    $elem );
		$this->assertContains( ">{$text}</textarea>", $elem );
		$this->assertContains( "rows=\"{$height}\"",  $elem );
		$this->assertContains( "cols=\"{$width}\"",   $elem );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlForm_htmlHidden()
	{
		$name   = 'input_hidden';
		$text   = 'input#hidden';
		$elem = html_forms::htmlHidden( $name, $text );
		$this->assertContains( "type=\"hidden\"",    $elem );
		$this->assertContains( "name=\"{$name}\"",   $elem );
		$this->assertContains( "value=\"{$text}\"",  $elem );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlForm_htmlSelect()
	{
		$name   = 'input_select';
		$size   = 2;
		$head   = 'added head';
		$item[0]= array( 'val 1', 'name 1' );
		$item[1]= array( 'val 2', 'name 2' );
		$elem = html_forms::htmlSelect( $name, $item, $size, array(), $head );
		$this->assertContains( "name=\"{$name}\"", $elem );	
		$this->assertContains( "size=\"{$size}\"", $elem );
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
		$elem = html_forms::htmlRadio( $name, $item, array(), $sep );
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
		$val    = 'val 1';
		$name   = 'name 1';
		$id     = 'id1';
		$elem = html_forms::htmlCheck( $name, $val, $name, $id );
		$this->assertContains( "type=\"checkbox\"",  $elem );
		$this->assertContains( "name=\"{$name}\"",   $elem );	
		$this->assertContains( "value=\"{$val}\"",   $elem );
		$this->assertContains( ">{$name}</label>",   $elem );
	}
	// +----------------------------------------------------------------------+
}

?>