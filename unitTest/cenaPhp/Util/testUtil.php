<?php
use CenaDTA\Util\Util as Util;
error_reporting( E_ALL );
require_once(dirname(__FILE__) . "/../../cenaPhp/Util/Util.php");
define( 'WORDY', 0 );

/**
 * PHPUnit test for CenaDTA\Util\Util class.
 */
class Util_UtilTest extends PHPUnit_Framework_TestCase
{
	// +----------------------------------------------------------------------+
	public function setUp()
	{
	}
	// +----------------------------------------------------------------------+
	public function test_getArgs()
	{
        $type = 'asis';
        $req  = TRUE;
        $pat  = '[0-9]*';
        $def  = 'something';
        $more = 'more';
        $arg = array( $type, $req, $pat, $def );
        $map = array( 'type', 'require', 'pattern', 'default' );
        $result = Util::getArgs( $arg, $map );
        $this->assertTrue( !empty( $result ) );
        $this->assertEquals( $type, $result[ 'type' ] );
        $this->assertEquals( $req,  $result[ 'require' ] );
        $this->assertEquals( $pat,  $result[ 'pattern' ] );
        $this->assertEquals( $def,  $result[ 'default' ] );
        
        // add an array to argument
        $arg[] = array(  'more' => $more );
        $result = Util::getArgs( $arg, $map );
        $this->assertEquals( $more,  $result[ 'more' ] );
        
        // no default map. this should create result w/o default.
        $map = array( 'type', 'require', 'pattern' );
        $result = Util::getArgs( $arg, $map );
        $this->assertEquals( $type, $result[ 'type' ] );
        $this->assertEquals( $req,  $result[ 'require' ] );
        $this->assertEquals( $pat,  $result[ 'pattern' ] );
        $this->assertEquals( $more, $result[ 'more' ] );
        $this->assertTrue( !isset( $result[ 'default' ] ) );
    }
	// +----------------------------------------------------------------------+
	public function test_getValue()
	{
        $value   = 'value';
        $default = 'default';
        $data = array( 'test' => $value );
        
        // test for array
        $this->assertEquals( $value, Util::getValue( $data, 'test' ) );
        $this->assertFalse(  Util::getValue( $data, 'none' ) );
        $this->assertEquals( $default, Util::getValue( $data, 'none', $default ) );
        $this->assertFalse(  Util::getValue( $data['test'], 'none' ) );
        
        // try something not an array.
        $this->assertFalse(  Util::getValue( $value, 'none' ) );
        $this->assertEquals( $default, Util::getValue( $value, 'none', $default ) );
	}
	// +----------------------------------------------------------------------+
	public function test_isValue()
	{
        // test just simple value. 
        $this->assertTrue(  Util::isValue( 'A' ) );
        $this->assertTrue(  Util::isValue( ' ' ) );
        $this->assertFalse( Util::isValue( '' ) );
        $this->assertFalse( Util::isValue( FALSE ) );
        $this->assertFalse( Util::isValue( NULL ) );
        error_reporting( E_ALL ^ E_NOTICE );
        $this->assertFalse( Util::isValue( $void ) );
        error_reporting( E_ALL );
        
        // test a value in an array.
        $data = array( 'test' => 'test' );
        $this->assertTrue(  Util::isValue( $data ) );
        $this->assertTrue(  Util::isValue( $data, 'test' ) );
        $this->assertFalse( Util::isValue( $data, 'none' ) );
        
        // case if a value is a string.
        $data = 'some_string_which_can_be_a_array';
        $this->assertTrue(  Util::isValue( $data ) );
        $this->assertFalse( Util::isValue( $data, 'test' ) );
        error_reporting( E_ALL ^ E_NOTICE );
        $this->assertFalse( Util::isValue( $void, 'test' ) );
        error_reporting( E_ALL );
        
        // test a value in an array.
        $data = array( 'test' => 'test' );
        $this->assertTrue(  Util::isValue( $data, 'test' ) );
        
        $data = array( 'test' => '' );
        $this->assertFalse(  Util::isValue( $data, 'test' ) );
        
        $data = array( 'test' => FALSE );
        $this->assertFalse(  Util::isValue( $data, 'test' ) );
        
        $data = array( 'test' => NULL );
        $this->assertFalse(  Util::isValue( $data, 'test' ) );
        
	}
	// +----------------------------------------------------------------------+
}


?>
