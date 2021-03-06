<?php
error_reporting( E_ALL ^ E_NOTICE );
ob_start();
if( !defined( 'WORDY' ) ) define( 'WORDY', FALSE );
require_once( dirname( __FILE__ ) . "/../../../cenaPhp/Html/Control.php" );


class HtmlControlTest extends PHPUnit_Framework_TestCase
{
    var $ctrl;
    // +----------------------------------------------------------------------+
    public function setUp()
    {
        $this->ctrl = new \CenaDta\Html\Control();
    }
    // +----------------------------------------------------------------------+
    // test default function.
    // +----------------------------------------------------------------------+
    function test_Default()
    {
        global $test_Control_default;

        // test default function works.
        $test_Control_default = FALSE;
        $title_default        = 'title_is_default';
        $this->ctrl->setDefault(
            function( $ctrl, $method ) {
                global $test_Control_default;
                $test_Control_default = TRUE;
            },
            $title_default
        );
        $this->ctrl->action();
        $this->assertTrue( $test_Control_default );
        $this->assertTrue( $this->ctrl->isDefault() );
        $this->assertEquals( $title_default, $this->ctrl->getCurrTitle() );

        // test default function works even if other function is set.
        $test_Control_default = FALSE;
        $title_test1          = 'title_is_test1';
        $this->ctrl->setAction(
            'test1',
            function( $ctrl, $method ) {
                global $test_Control_default;
                $test_Control_default = 'test1';
            },
            $title_test1
        );
        $this->ctrl->action();
        $this->assertTrue( $test_Control_default );
        $this->assertTrue( $this->ctrl->isDefault() );
        $this->assertEquals( $title_default, $this->ctrl->getCurrTitle() );

        // now, run test1 function.
        $test_Control_default = FALSE;
        $_REQUEST[ $this->ctrl->act_name ] = 'test1';
        $this->ctrl->action();
        $this->assertEquals( 'test1', $test_Control_default );
        $this->assertEquals( 'test1', $this->ctrl->currAct() );
        $this->assertFalse( $this->ctrl->isDefault() );
        $this->assertEquals( $title_test1, $this->ctrl->getCurrTitle() );
    }
    /**
     * test add/get functions.
     */
    function test_AddGet()
    {
        $test = 'this is test';
        $this->ctrl->add( 'test', $test );
        $saved = $this->ctrl->get( 'test' );
        $this->assertEquals( $test, $saved );

        $test1 = 'this is test1';
        $test2 = 'this is test2';
        $more = array(
            'test1' => $test1,
            'test2' => $test2
        );
        $this->ctrl->add( $more );
        $got  = $this->ctrl->get( 'test' );
        $got1 = $this->ctrl->get( 'test1' );
        $got2 = $this->ctrl->get( 'test2' );
        $this->assertEquals( $test,  $got );
        $this->assertEquals( $test1, $got1 );
        $this->assertEquals( $test2, $got2 );
    }
    /**
     * test add/set functions.
     */
    function test_SetGet()
    {
        $test = 'this is test';
        $this->ctrl->set( 'test', $test );
        $saved = $this->ctrl->get( 'test' );
        $this->assertEquals( $test, $saved );

        $test1 = 'this is test1';
        $test2 = 'this is test2';
        $more = array(
            'test1' => $test1,
            'test2' => $test2
        );
        $this->ctrl->set( $more );
        $got  = $this->ctrl->get( 'test' );
        $got1 = $this->ctrl->get( 'test1' );
        $got2 = $this->ctrl->get( 'test2' );
        $this->assertEquals( $test,  $got );
        $this->assertEquals( $test1, $got1 );
        $this->assertEquals( $test2, $got2 );
    }
}


?>