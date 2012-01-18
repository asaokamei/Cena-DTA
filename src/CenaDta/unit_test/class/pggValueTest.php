<?php
ob_start();
define( 'SVCART_USE_PRICE', TRUE );
require_once( dirname( __FILE__ ) . "/../../class/class.ext_func.php" );
require_once( dirname( __FILE__ ) . "/../../class/class.pgg_value.php" );

/*
PHPUnit test checks for following interfaces (methods)
	get
	setVarOrder
	pushValue
	popVariables
	popHtmlSafe
	getValue

*/
class pggValueTest extends PHPUnit_Framework_TestCase
{
	private $pgg;
	// +-------------------------------------------------------------+
	public function setUp()
	{
		$this->pgg = new pgg_value();
		$_POST     = array();
		$_GET      = array();
	}
	// +-------------------------------------------------------------+
	function testGetPost() 
	{
		// test to read from $_POST.
		
		// get value test.
		$_POST[ 'test' ] = 'value';
		$test = $this->pgg->get( 'test' );
		$this->assertEquals( $test, 'value' );
		
		// getting non-existing test2 data.
		$test2 = $this->pgg->get( 'test2' );
		$this->assertEquals( $test2, FALSE );
		
		// set to read only from POST data.
		$this->pgg->setVarOrder( PGG_POST );
		$test = $this->pgg->get( 'test' );
		$this->assertEquals( $test, 'value' );
		
		// set to read only from GET data; should fail.
		$this->pgg->setVarOrder( PGG_GET );
		$test = $this->pgg->get( 'test' );
		$this->assertEquals( $test, FALSE );
		
		// get empty value test.
		$_POST[ 'test' ] = '';
		$test = $this->pgg->get( 'test' );
		$this->assertEquals( $test, NULL );
	}
	// +-------------------------------------------------------------+
	function testSetVarOrder() 
	{
		// original var_order;
		$var_order = $this->pgg->var_order;
		
		// get the current var order
		$get_order = $this->pgg->SetVarOrder();
		$this->assertEquals( $var_order, $get_order );
		
		$get_order = $this->pgg->SetVarOrder( PGG_DATA );
		$this->assertEquals( array( PGG_DATA ), $get_order );
		
		$get_order = $this->pgg->SetVarOrder( $var_order );
		$this->assertEquals( $var_order, $get_order );
	}
	// +-------------------------------------------------------------+
	function testGetGet() 
	{
		// test to read from $_GET.
		
		$_GET[ 'test' ] = 'value';
		$test = $this->pgg->get( 'test' );
		$this->assertEquals( $test, 'value' );
		
		// get empty value test.
		$_GET[ 'test' ] = '';
		$test = $this->pgg->get( 'test' );
		$this->assertEquals( $test, NULL );
	}
	// +-------------------------------------------------------------+
	function testGetData() 
	{
		// test to read from $data.
		
		// get test data. 
		$data[ 'test' ] = 'value';
		$this->pgg->setPostVars( $data );
		$test = $this->pgg->get( 'test' );
		$this->assertEquals( $test, 'value' );
		
		// get non-existing test2; should fail.
		$_POST[ 'onlypost' ] = 'post-data';
		$this->pgg->setVarOrder( PGG_DATA );
		$test2 = $this->pgg->get( 'onlypost' );
		$this->assertEquals( $test2, '' );
		
		// get non-existing test2; should fail.
		$_GET[ 'onlyget' ] = 'get-data';
		$test2 = $this->pgg->get( 'onlyget' );
		$this->assertEquals( $test2, '' );
	}
	// +-------------------------------------------------------------+
	function testPushValue() 
	{
		// push and pop related tests.
		
		// push 'test' value.
		$test = $this->pgg->pushValue( 'test', 'value' );
		$this->assertEquals( $test, 'value' );
		
		// pop 'test' should have 'test'.
		$test = $this->pgg->popVariables( 'test' );
		$this->assertEquals( $test, 'value' );
		
		// pop 'test' as password, that is jammed.
		$test = $this->pgg->popPasswd( 'test' );
		$this->assertEquals( $test, '*****' );
		
		// pop all data at once. should have 'test'.
		$data = $this->pgg->popVariables();
		$this->assertEquals( $data[ 'test' ], 'value' );
		
		// deleting text.
		$this->pgg->deleteVar( 'test' );
		$test = $this->pgg->popVariables( 'test' );
		$this->assertNull( $test );
	}
	// +-------------------------------------------------------------+
	function testTextWithHtml() 
	{
		// pushing HTML text. pop as is for this.
		$this->pgg->pushValue( 'test', '<bold">' );
		
		// get text as HTML.
		$test = $this->pgg->popVariables( 'test' );
		$this->assertEquals( $test, '<bold">' );
		
		// get text as safe-HTML (against XSS/CSRF).
		$test = $this->pgg->popHtmlSafe( 'test' );
		$this->assertEquals( $test, '&lt;bold&quot;&gt;' );
	}
	// +-------------------------------------------------------------+
	function testTextWithNewLine() 
	{
		// pushing text with newline.
		$this->pgg->pushValue( 'test', "value\ntest" );
		
		// get text as is. 
		$test = $this->pgg->popHtmlSafe( 'test' );
		$this->assertEquals( $test, "value\ntest" );
		
		// pushing text with newline as <br /> in it.
		$test = $this->pgg->popTextArea( 'test' );
		$this->assertEquals( $test, "value<br />\ntest" );
	}
	// +-------------------------------------------------------------+
	function testTextWithQuote() 
	{
		// pushing text with newline.
		$this->pgg->pushValue( 'test', "value'test" );
		
		// get text as is. 
		$test = $this->pgg->popHtmlSafe( 'test' );
		$this->assertEquals( $test, "value'test" );
		
		// pushing text with newline as <br /> in it.
		$test = $this->pgg->popSqlSafe( 'test' );
		$this->assertEquals( $test, "value\'test" );
	}
	// +-------------------------------------------------------------+
	function testGetFilters() 
	{
		// testing trim
		$_POST[ 'test' ] = ' test ';
		
		// text is trimmed.
		$test = $this->pgg->getValue( 'test' );
		$this->assertEquals( $test, 'test' );
		
		// not trimmed (get/getBinary)
		$test = $this->pgg->get( 'test' );
		$this->assertEquals( $test, ' test ' );
		
		// protecting from null-byte attack.
		$_POST[ 'test' ] = "test\0test";
		$test = $this->pgg->getValue( 'test' );
		$this->assertEquals( $test, 'testtest' );
		
		// null-byte not removed.
		$test = $this->pgg->get( 'test' );
		$this->assertEquals( $test, "test\0test" );
	}
	// +-------------------------------------------------------------+
	function testPushManyValues() 
	{
		// push and pop many data
		
		// pushing test values.
		$test1 = $this->pgg->pushValue( 'test1', 'value1' );
		$test2 = $this->pgg->pushValue( 'test2', 'value2' );
		$test3 = $this->pgg->pushValue( 'test3', 'value3' );
		$this->assertEquals( $test1, 'value1' );
		$this->assertEquals( $test2, 'value2' );
		$this->assertEquals( $test3, 'value3' );
		
		// popping some data.
		$test  = $this->pgg->popVariables( 'test' );
		$test2 = $this->pgg->popVariables( 'test2' );
		$this->assertNull( $test );
		$this->assertEquals( $test2, 'value2' );
		
		// popping all data at once.
		
		$data = $this->pgg->popVariables();
		$this->assertEquals( $data[ 'test1' ], 'value1' );
		$this->assertEquals( $data[ 'test2' ], 'value2' );
		$this->assertEquals( $data[ 'test3' ], 'value3' );
		
		// $data has only above 3 items. no others.
		unset( $data[ 'test1' ] );
		unset( $data[ 'test2' ] );
		unset( $data[ 'test3' ] );
		$this->assertTrue( empty( $data ) );
		
		// popping only two data.
		$data = $this->pgg->popVariables( array( 'test1', 'test3' ) );
		$this->assertEquals( $data[ 'test1' ], 'value1' );
		$this->assertEquals( $data[ 'test3' ], 'value3' );
		$this->assertTrue( empty( $data[ 'test2' ] ) ); // no, you cannot get this.
		
		// popping all data at once via popHtmlSafe.
		
		$data = $this->pgg->popHtmlSafe();
		$this->assertEquals( $data[ 'test1' ], 'value1' );
		$this->assertEquals( $data[ 'test2' ], 'value2' );
		$this->assertEquals( $data[ 'test3' ], 'value3' );
		
		// $data has only above 3 items. no others.
		unset( $data[ 'test1' ] );
		unset( $data[ 'test2' ] );
		unset( $data[ 'test3' ] );
		$this->assertTrue( empty( $data ) );
		
		// popping all data at once via popSqlSafe.
		
		$data = $this->pgg->popSqlSafe();
		$this->assertEquals( $data[ 'test1' ], 'value1' );
		$this->assertEquals( $data[ 'test2' ], 'value2' );
		$this->assertEquals( $data[ 'test3' ], 'value3' );
		
		// $data has only above 3 items. no others.
		unset( $data[ 'test1' ] );
		unset( $data[ 'test2' ] );
		unset( $data[ 'test3' ] );
		$this->assertTrue( empty( $data ) );
	}
	// +-------------------------------------------------------------+
	function testPushError() 
	{
		// test pushError and popErros
		
		// push one error, and get the error.
		$test = $this->pgg->pushError( 'test', 'value', 9999, 'msg1' );
		$msg  = $this->pgg->popErrors( 'test' );
		$this->assertEquals( $msg[ 'test' ], 'msg1' );
		
		// push another error, and get both errors.
		$test = $this->pgg->pushError( 'test2', 'value2', 9999, 'msg2' );
		$msg  = $this->pgg->popErrors();
		$this->assertEquals( $msg[ 'test'  ], 'msg1' );
		$this->assertEquals( $msg[ 'test2' ], 'msg2' );
	}
	// +-------------------------------------------------------------+
}

?>