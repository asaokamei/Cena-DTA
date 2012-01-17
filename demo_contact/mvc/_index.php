<?php
require( __DIR__ . '/../../vendor/AmidaMVC/src/AmidaMVC/bootstrap.php');

/**
 * TODO: demo as test site.
 *  before eating dog food, make test/demo site.
 */
//class Debug extends AmidaMVC\Component\Debug {}
$data = array();
$ctrl = new AmidaMVC\Framework\Controller();
$ctrl
    ->loadDebug()
    ->addModel( 'Loader', 'load' )
    ->addModel( 'Viewer', 'view' )
;

$ctrl->start( $data );


