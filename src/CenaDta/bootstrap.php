<?php
/**
 * SPL Class Loader for NameSpace classes using closure.
 * ex: spl_autoload_register( ClassLoader() );
 * copied from https://gist.github.com/221634
 * @param $path
 * @param array $option
 * @return closure
 */

spl_autoload_register( CenaDta_ClassLoader() );

function CenaDta_ClassLoader( $path=FALSE )
{
    if( $path === FALSE ) $path = __DIR__;
    $args = func_get_args();
    $args = array_slice( $args, 1 );
    $default = array(
        'ext' => '.php',
        'sep' => '\\',
        'tip' => FALSE,
    );
    $args = $args + $default;
    if( !$args[ 'tip' ] ) {
        $args[ 'tip' ] = substr( $path, strripos( $path, DIRECTORY_SEPARATOR ) + 1 );
        $path = substr( $path, 0, strripos( $path, DIRECTORY_SEPARATOR ) );
    }
    $args[ 'path' ] = $path;

    return function( $className ) use ( $args ) {
        $tip  = $args[ 'tip' ];
        if( !$tip ) return;
        $ds   = DIRECTORY_SEPARATOR;
        $sep  = $args[ 'sep' ];
        $ext  = $args[ 'ext' ];
        $path = $args[ 'path' ];
        if( substr( $className, 0, strlen( $tip.$sep ) ) === $tip.$sep ) {
            $fileName = '';
            if( FALSE !== ( $lastNsPos = strripos( $className, $sep ) ) ) {
                $namespace = substr( $className, 0, $lastNsPos );
                $className = substr( $className, $lastNsPos + 1 );
                $fileName  = str_replace( $sep, $ds, $namespace ) . $ds;
            }
            $fileName .= str_replace( '_', $ds, $className) . $ext;

            $inc = ( $path !== null ? $path . $ds : '' ) . $fileName;
            require( $inc );
        }
    };
}
