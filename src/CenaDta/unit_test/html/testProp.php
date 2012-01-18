<?php
ob_start();
require_once( dirname( __FILE__ ) . "/../../class/class.ext_func.php" );
require_once( dirname( __FILE__ ) . "/../../Html/Prop.php" );
use CenaDta\Html as html;

class HtmlPropTest extends PHPUnit_Framework_TestCase
{
	// +----------------------------------------------------------------------+
	public function setUp()
	{
	}
	// +----------------------------------------------------------------------+
	// test suites for html\Prop class.
	// +----------------------------------------------------------------------+
	public function test_htmlProp_construct()
	{
		$prop = new html\Prop();
		$property = $prop->getProp();
		$this->assertEquals( "", $property );
		
		// option in string
		$prop = new html\Prop( 'test' );
		$property = $prop->getProp();
		$this->assertEquals( " test=\"test\"",             $property );
		
		$prop = new html\Prop( 'test=>testval' );
		$property = $prop->getProp();
		$this->assertEquals( " test=\"testval\"",          $property );
		
		$prop = new html\Prop( 'test=>testval|prop=>propval' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " prop=\"propval\"",        $property );
		
		$prop = new html\Prop( 'test => testval | prop => propval' );
		$property = $prop->getProp();
		$this->assertContains( " test =\" testval\"",      $property );
		$this->assertContains( " prop =\" propval\"",      $property );
		
		// option in array
		$prop = new html\Prop( array( 'test' ) );
		$property = $prop->getProp();
		$this->assertEquals( " test=\"test\"",             $property );
		
		$prop = new html\Prop( array( 'test' => 'testval' ) );
		$property = $prop->getProp();
		$this->assertEquals( " test=\"testval\"",          $property );
		
		$prop = new html\Prop( array( 'test' => 'testval', 'prop' =>'propval' ) );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " prop=\"propval\"",        $property );
		
		$prop = new html\Prop( array( 'test '=> ' testval', 'prop ' => ' propval' ) );
		$property = $prop->getProp();
		$this->assertContains( " test =\" testval\"",      $property );
		$this->assertContains( " prop =\" propval\"",      $property );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlProp_default()
	{
		$prop = new html\Prop( 'test=>testval|class=>class1' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " class=\"class1\"",        $property );
		
		html\Prop::setDefault( 'class', 'cladef' );
		$prop = new html\Prop( 'test=>testval|class=>class1' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " class=\"cladef class1\"", $property );
		
		html\Prop::setDefault( 'class', 'cladefdef' );
		$prop = new html\Prop( 'test=>testval|class=>class1' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",                  $property );
		$this->assertContains( " class=\"cladef cladefdef class1\"", $property );
		
		// make sure all the defaults are cleared!
		// otherwise all subsequent tests is altered!!!
		html\Prop::clearDefault();
	}
	// +----------------------------------------------------------------------+
	public function test_htmlProp_property()
	{
		$prop = new html\Prop( 'test=>testval|style=>style1' );
		$prop->addOption( 'newprop', 'newval' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " style=\"style1\"",        $property );
		$this->assertContains( " newprop=\"newval\"",      $property );
		
		$prop = new html\Prop( 'test=>testval|style=>style1' );
		$prop->addOption( 'newprop', 'newval' );
		$prop->addOption( 'newprop', 'newadd' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " style=\"style1\"",        $property );
		$this->assertContains( " newprop=\"newadd\"",      $property );
		$this->assertNotContains( " newprop=\"newval\"",   $property );
		
		$prop = new html\Prop( 'test=>testval|style=>style1' );
		$prop->addOption( 'newprop', 'newval' );
		$prop->addOption( 'newprop', 'newadd', '+' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",          $property );
		$this->assertContains( " style=\"style1\"",          $property );
		$this->assertContains( " newprop=\"newval+newadd\"", $property );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlProp_style()
	{
		// option in string
		$prop = new html\Prop( 'test=>testval|style=>style1' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " style=\"style1\"",        $property );
		
		$prop = new html\Prop( 'test=>testval|style=>sty|style=>stadd' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " style=\"sty;stadd\"",     $property );
		
		// option in array
		$prop = new html\Prop( array( 'test' => 'testval', 'style' => 'style1' ) );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " style=\"style1\"",        $property );
		
		$prop = new html\Prop( array( 'test' => 'testval', 'style' => 'sty', 'style' => 'stadd' ) );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " style=\"stadd\"",         $property );
		// NOTE: the first 'style' is overwritten 
		//       with latter 'style' when using array.
		$this->assertNotContains( " style=\"sty;stadd\"",  $property );
		
		// test addStyle method
		$prop = new html\Prop( 'test=>testval' );
		$prop->addStyle( 'style1' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " style=\"style1\"",        $property );
		
		$prop = new html\Prop( 'test=>testval' );
		$prop->addStyle( 'sty' );
		$prop->addStyle( 'stadd' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " style=\"sty;stadd\"",     $property );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlProp_class()
	{
		// option in string
		$prop = new html\Prop( 'test=>testval|class=>class1' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " class=\"class1\"",        $property );
		
		$prop = new html\Prop( 'test=>testval|class=>cla|class=>cladd' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " class=\"cla cladd\"",     $property );
		
		// option in array
		$prop = new html\Prop( array( 'test' => 'testval', 'class' => 'class1' ) );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " class=\"class1\"",        $property );
		
		$prop = new html\Prop( array( 'test' => 'testval', 'class' => 'cla', 'class' => 'cladd' ) );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " class=\"cladd\"",         $property );
		// NOTE: the first 'class' is overwritten 
		//       with latter 'class' when using array.
		$this->assertNotContains( " class=\"cla cladd\"",  $property );
		
		// test addClass method
		$prop = new html\Prop( 'test=>testval' );
		$prop->addClass( 'class1' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " class=\"class1\"",        $property );
		
		$prop = new html\Prop( 'test=>testval' );
		$prop->addClass( 'cla' );
		$prop->addClass( 'cladd' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " class=\"cla cladd\"",     $property );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlProp_ime()
	{
		$prop = new html\Prop( 'test=>testval|style=>sty' );
		$prop->setIME( 'I1' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " style=\"sty\"",           $property );
		$this->assertContains( " istyle=\"1\"",            $property );
		
		$prop = new html\Prop( 'test=>testval|style=>sty' );
		$prop->setIME( 'ON' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",              $property );
		$this->assertContains( " style=\"sty;ime-mode:active\"", $property );
		
		$prop = new html\Prop( 'test=>testval|style=>sty' );
		$prop->setIME( 'OFF' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",                $property );
		$this->assertContains( " style=\"sty;ime-mode:inactive\"", $property );
	}
	// +----------------------------------------------------------------------+
	public function test_htmlProp_loop()
	{
		/*
		$prop_1 = new html\Prop( 'test=>testval|class=>class1' );
		$prop = new html\Prop( array( $prop_1 ) );
		$prop->addClass( 'cladd' );
		$property = $prop->getProp();
		$this->assertContains( " test=\"testval\"",        $property );
		$this->assertContains( " class=\"class1\"",        $property );
		*/
	}
	// +----------------------------------------------------------------------+
}


?>