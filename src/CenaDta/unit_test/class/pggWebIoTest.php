<?php
ob_start();
define( 'SVCART_USE_PRICE', TRUE );
require_once( dirname( __FILE__ ) . "/../../class/class.ext_func.php" );
require_once( dirname( __FILE__ ) . "/../../class/class.web_io.php" );

class WebIoTest extends PHPUnit_Framework_TestCase
{
	function testEncode()
	{
		$orig_data = array( 'test' => 'value', 'test2' => 'more value' );
		$encoded   = web_io::encodeData( $orig_data, WEBIO_ENCODE_BASE64 );
		$decoded   = web_io::decodeData( $encoded,   WEBIO_ENCODE_BASE64 );
		$this->assertEquals( $orig_data, $decoded );
	}
}


?>