<?php
ob_start();
define( 'SVCART_USE_PRICE', TRUE );
require_once( dirname( __FILE__ ) . "/../../class/class.ext_func.php" );
require_once( dirname( __FILE__ ) . "/../../class/class.svCart.php" );

class CartTest extends PHPUnit_Framework_TestCase
{
	private $cart;
	public function setUp()
	{
		$this->cart = new svCart();
	}
	function testAdd() {
		$this->cart->add( '100', 1 );
		$cart = $this->cart->get_list();
		$this->assertEquals( $cart[0]['pid'], '100' );
		$this->assertEquals( $cart[0]['num'], 1 );
	}
	function testAddInfo() {
		$this->cart->add( '100', 1, 'info', 100 );
		$cart = $this->cart->get_list();
		$this->assertEquals( $cart[0]['pid'],   '100' );
		$this->assertEquals( $cart[0]['num'],   1 );
		$this->assertEquals( $cart[0]['info'],  'info' );
	}
	function testAddInfoPrice() {
		$this->cart->add( '100', 1, 'info', 100 );
		$cart = $this->cart->get_list();
		$this->assertEquals( $cart[0]['pid'],   '100' );
		$this->assertEquals( $cart[0]['num'],   1 );
		$this->assertEquals( $cart[0]['info'],  'info' );
		$this->assertEquals( $cart[0]['price'], 100 );
	}
	function testAdd2() {
		$this->cart->add( '100', 1 );
		$this->cart->add( '200', 2 );
		$cart = $this->cart->get_list();
		$this->assertEquals( $cart[0]['pid'], '100' );
		$this->assertEquals( $cart[0]['num'], 1 );
		$this->assertEquals( $cart[1]['pid'], '200' );
		$this->assertEquals( $cart[1]['num'], 2 );
	}
	function testMod() {
		$this->cart->add( '100', 1 );
		$this->cart->add( '200', 2 );
		$this->cart->mod( '100', 2 );
		$cart = $this->cart->get_list();
		$this->assertEquals( $cart[0]['pid'], '100' );
		$this->assertEquals( $cart[0]['num'], 2 );
		$this->assertEquals( $cart[1]['pid'], '200' );
		$this->assertEquals( $cart[1]['num'], 2 );
	}
	function testDel() {
		$this->cart->add( '100', 1 );
		$this->cart->add( '200', 2 );
		$this->cart->del( '100' );
		$cart = $this->cart->get_list();
		$this->assertEquals( $cart[0]['pid'], '200' );
		$this->assertEquals( $cart[0]['num'], 2 );
	}
}

?>