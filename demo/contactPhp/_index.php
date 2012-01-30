<?php
require_once(__DIR__ . '/../../vendor/AmidaMVC/src/AmidaMVC/bootstrap.php');

/**
 * AmidaMVC's Demo Site...
 */

$routes = array(
    '/demo.css' => array( 'file' => 'demo.css' ),
    '/:action/:offset/:limit' => 
        array( 
            'file' => '_App.php', 
            'action' => 'default', 
            'offset' => 0, 
            'limit' => 5 
        ),
    '/:action' => array( 'file' => '_App.php', 'action' => 'default' ),
);

AmidaMVC\Tools\Route::set( $routes );

AmidaMVC\Component\Debug::_init();

$data = new \AmidaMVC\Component\SiteObj();
$ctrl = new \AmidaMVC\Framework\Controller();
$ctrl
    ->addComponent( 'Config', 'config' )
    ->addComponent( 'Debug',  'debug' )
    ->addComponent( 'Auth',   'auth' )
    ->addComponent( 'Router', 'router' )
    ->addComponent( 'Loader', 'loader' )
    ->addComponent( 'Render', 'render' )
;

$ctrl->start( $data );


