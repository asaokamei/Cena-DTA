<?php
require_once(__DIR__ . '/../../vendor/AmidaMVC/src/AmidaMVC/bootstrap.php');

/**
 * AmidaMVC's Demo Site...
 */

$routes = array(
    '/demo.css' => array( 'file' => 'demo.css' ),
    '/list/:offset/:limit' => 
        array( 
            'file' => '_App.php', 
            'action' => 'default', 
            'offset' => 0, 
            'limit' => 4 
        ),
    '/detail/:id' =>
        array(
            'file' => '_App.php',
            'action' => 'detail',
            'offset' => 0,
            'limit' => 4
        ),
    '/list' =>
        array(
            'file' => '_App.php',
            'action' => 'default',
            'offset' => 0,
            'limit' => 4
        ),
    '/:action' => array( 'file' => '_App.php', 'action' => 'default' ),
    '/' => array( 'file' => '_App.php', 'action' => 'default' ),
);

AmidaMVC\Tools\Route::set( $routes );

$data = new \AmidaMVC\Component\SiteObj();
$ctrl = new \AmidaMVC\Framework\Controller();
$ctrl
    ->addComponent( 'Config', 'config' )
    ->addComponent( 'Router', 'router' )
    ->addComponent( 'Loader', 'loader' )
    ->addComponent( 'Render', 'render' )
;

$ctrl->start( $data );


