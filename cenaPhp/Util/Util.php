<?php
namespace CenaDTA\Util;
/**
 * Util class
 * utility class for Cena-DTA project. 
 */
class Util
{
    // +--------------------------------------------------------------- +
    /** checks if a variable ($var) have a value (non empty value).
     *  returns TRUE if $var==0 as well.
     */
    static function isValue( $var, $name=FALSE ) {
        if( $name !== FALSE ) {
            if( !is_array( $var ) ) {
                return FALSE;
            }
            if( isset( $var[ $name ] ) ) {
                return self::isValue( $var[ $name ] );
            }
            return FALSE;
        }
        else
        if( is_object( $var ) ) {
            return TRUE;
        }
        else
        if( "$var" == "" ) { 
            return FALSE; 
        }
        else { 
            return TRUE; 
        }
    }
    // +--------------------------------------------------------------- +
    /** gets a value in $arr[ $name ] without causing E_NOTICE error.
     */
    static function getValue( $arr, $name, $default=FALSE ) {
        if( is_array( $arr ) && array_key_exists( $name, $arr ) ) {
            return $arr[ $name ];
        }
        return $default;
    }
    // +--------------------------------------------------------------- +
    /**
     * recursively apply a function to modify values of an array. 
     * if $value is not an array, applies function to the value.  
     * @param array &$value   array of values to modify.
     *                        or just the value (not array).
     * @param closure $func   function to apply. 
     * @return mix            returns the function's return.
     */
    static function arrayWalk( &$value, $func ) {
        if( is_array( $value ) ) {
            return array_walk_recursive( $value, $func );
        }
        return $func( $value, NULL );
    }
    // +--------------------------------------------------------------- +
    /**
     * prepares argument for various function input. 
     * @param array $arg   list of argument.
     * @param array $map   list of names to map each argument. 
     * @return array       result. 
     */
    static function getArgs( $arg, $map ) {
        if( empty( $arg ) ) return array();
        if( !is_array( $arg ) ) return array();
        $result = array();
        foreach( $arg as $idx => $val ) {
            if(is_array( $val ) ) {
                $result = array_merge( $result, $val );
                break;
            }
            if( Util::isValue( $map, $idx ) ) {
                $result[ $map[$idx] ] = $val;
            }
        }
        return $result;
    }
    // +--------------------------------------------------------------- +
}

?>