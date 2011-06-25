<?php
namespace CenaDta\Dba;
	
class Dba_DaoObjPools_Exception extends \Exception {}

class DaoObjPools
{
	private static $dao_pool = array(); // for Dao object pool
	private static $rec_pool = array(); // for Record object pool
	private static $ele_pool = array(); // for html form element pool
    // +----------------------------------------------------------------------------+
	//
	//                            Pooling Dao Objects
	//
    // +----------------------------------------------------------------------------+
	public static function setInstance( $class, $dao )
	{
		self::$dao_pool[ $class ] = $dao;
    }
    // +----------------------------------------------------------------------------+
	public static function getInstance( $class )
	{
		if( isset( self::$dao_pool[ $class ] ) ) {
			return self::$dao_pool[ $class ];
		}
		return FALSE;
    }
    // +----------------------------------------------------------------------------+
	public static function clearInstance( $class=FALSE )
	{
		if( have_value( $class ) ) {
			self::$dao_pool[ $class ] = array();
		}
		else {
			self::$dao_pool = array();
		}
    }
    // +----------------------------------------------------------------------------+
	//
	//                          Pooling Record Objects
	//
    // +----------------------------------------------------------------------------+
	public static function setRecord( $class, $type, $id, $rec )
	{
		self::$rec_pool[ $class ][ $type ][ $id ] = $rec;
    }
    // +----------------------------------------------------------------------------+
	public static function getRecord( $class, $type, $id )
	{
        if( WORDY > 5 ) echo "ObjPool::getRecord( $class, $type, $id )\n";
        if( !have_value( $class ) ) throw new Dba_DaoObjPools_Exception( 'getRecord: no class' );
        if( !have_value( $type ) )  throw new Dba_DaoObjPools_Exception( 'getRecord: no type' );
        if( !have_value( $id ) )    throw new Dba_DaoObjPools_Exception( 'getRecord: no id' );
		if( isset( self::$rec_pool[ $class ][ $type ][ $id ] ) ) {
			return self::$rec_pool[ $class ][ $type ][ $id ];
		}
		return FALSE;
	}
    // +----------------------------------------------------------------------------+
	public static function clearRecord( $class=FALSE )
	{
		if( $class ) {
			self::$rec_pool[ $class ] = array();
		}
		else {
			self::$rec_pool = array();
		}
    }
    // +----------------------------------------------------------------------------+
	//
	//                          Pooling Html Form Elements
	//
    // +----------------------------------------------------------------------------+
	public static function setElement( $class, $name, $elem )
	{
		self::$ele_pool[ $class ][ $name ] = $elem;
    }
    // +----------------------------------------------------------------------------+
	public static function getElement( $class, $name )
	{
		if( isset( self::$ele_pool[ $class ][ $name ] ) ) {
			return self::$ele_pool[ $class ][ $name ];
		}
		return FALSE;
	}
    // +----------------------------------------------------------------------------+
	public static function clearElement( $class=FALSE )
	{
		if( $class ) {
			self::$ele_pool[ $class ] = array();
		}
		else {
			self::$rec_pool = array();
		}
    }
    // +----------------------------------------------------------------------------+
	public static function getSelector( $var_name, &$col_sels )
	{
		$selInst = FALSE;
		if( isset( $col_sels[ $var_name ] ) ) {
			$info    = $col_sels[ $var_name ];
			$selClss = $info[0];
			$selInst = new $selClss( $var_name, $info[1], $info[2], $info[3] );
		}
		return $selInst;
	}
    // +----------------------------------------------------------------------------+
	public static function checkInput( $pgg, $var_name, &$col_checks )
	{
		$value  = FALSE;
		if( isset( $col_checks[ $var_name ] ) ) {
			$info    = $col_checks[ $var_name ];
			$method  = $info[0];
			$info[0] = $var_name;
			if( WORDY ) wt( $info, "checkInput: $method for $var_name" );
			if( !method_exists( $pgg, $method ) ) {
				throw new Exception( "no method $method in \$pgg" );
			}
			$func  = array( $pgg, $method );
			$value = call_user_func_array( $func, $info );
		}
		return $value;
	}
    // +----------------------------------------------------------------------------+
}



?>