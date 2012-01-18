<?php
namespace CenaDta\Util;
/**
 * Verify.php 
 * a static class to verify/obtain data from a source. 
 * a wrapper class for Validator class. 
 */
require_once( dirname( __FILE__ ) . "/Validator.php" );

class Verify
{
    /**
     * source to search data for. 
     * @var array
     */
    static $source = array();
    /**
     * found data
     * @var array
     */
    static $data = array();
    /**
     * error message
     * @var array
     */
    static $error = array();
    /**
     * number of errors
     * @var integer
     */
    static $err_num = 0;
    // +--------------------------------------------------------------- +
    /**
     * set source data. 
     * @param array $source 
     */
    static function source( &$source ) {
        if( !is_null( $source ) && is_array( $source ) ) {
            self::$source = &$source;
        }
    }
    // +--------------------------------------------------------------- +
    /**
     * initializes this static class.
     */
    static function _init() {
        self::$source  = array();
        self::$data    = array();
        self::$error   = array();
        self::$err_num = 0;
    }
    // +--------------------------------------------------------------- +
    /**
     * sets external repository for verified data. 
     * and returns verified data. 
     * @param array $data    not sure its use. 
     * @return array         verified data. 
     */
    static function data( &$data=NULL ) {
        if( !is_null( $data ) && is_array( $data ) ) {
            self::$data = &$data;
        }
        return self::$data;
    }
    // +--------------------------------------------------------------- +
    /**
     * searchs $name variable in source data (self::$source) and 
     * verifies based on given $type and $filter. 
     * @param string $name     name of variable in the source. 
     * @param string $type     type of the variable. 
     * @param array $filter   filter to apply
     * @return string        return verified value if validated. 
     *                       returns NULL if not found and validated.
     *                       returns FALSE if not validated. 
     */
    static function _validate( $name, $type='asis', $filter=array() ) {
        $error   = NULL;
        $value   = NULL;
        $success = Validator::find( self::$source, $name, $value, $type, $filter, $error );
        if( !$success ) {
            self::$err_num++;
            self::$error[ $name ] = $error;
            return FALSE;
        }
        self::$data[ $name ] = $value;
        return $value;
    }
    // +--------------------------------------------------------------- +
    /**
     * pops value in the data. 
     * @param type $name
     * @return type 
     */
    static function pop( $name ) {
        if( array_key_exists( $name, self::$data ) ) {
            return self::$data[ $name ];
        }
        return FALSE;
    }
    // +--------------------------------------------------------------- +
    /**
     * a quick verify method for verify. 
     * if an array is given as argument, it is used as filter and 
     * rest of arguments are ignored. 
     * @param string $name       name of value. required arg.
     * @param string $type       type of value. required arg. 
     * @param string $required   required value if TRUE. 
     * @param string $pattern    validate the pattern (regexp).
     * @param string $default    default value if not found. 
     */
    static function push( $name, $type ) {
        $args = func_get_args();
        $name = $args[0];
        $type = $args[1];
        
        $args = array_slice( $args, 2 );
        $filter = Util::getArgs( $args, array( 'required', 'pattern', 'default' ) );
        self::_validate( $name, $type, $filter );
    }
    // +--------------------------------------------------------------- +
    /**
     * process $list as an array of input for push method. 
     * @param array $list   list of input. 
     *     array( 
     *       name1 => array( type, required, pattern, default ),
     *       name2 => array( type, array( filter=>option,... ) ),.. )
     * @return boolean    
     */
    static function check( $list ) {
        if( is_array( $list ) && !empty( $list ) ) {
            foreach( $list as $name => $item ) {
                self::push( $name, $item[0], $item[1] );
            }
        }
        return isError();
    }
    // +--------------------------------------------------------------- +
    /**
     * returns error status and error messages. 
     * @param array $error   returns error messages. 
     * @return boolean       TRUE if no error, otherwise FALSE.
     */
    static function isError( &$error=NULL ) {
        $error = self::$error;
        return !self::$err_num;
    }
    // +--------------------------------------------------------------- +
}

?>
