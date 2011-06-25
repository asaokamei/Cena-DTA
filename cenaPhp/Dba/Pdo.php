<?php
namespace CenaDta\Dba;
/* dba_base class
 * by Asao Kamei @ WorkSpot.JP, 
 * PURPOSE: to access RDB via PDO
 * ALL RIGHTS RESERVED 2002-2010
 *
*/

class DbaPdo_Exception extends \Exception {}
class DbaPdo_NoIniFile_Exception extends DbaPdo_Exception {}

class Pdo
{
	const FORCE_NEW = 'NEW';
	// for each instance
	var $conn   = FALSE; // returned from PDO::__construct
	var $sqlh   = FALSE; // returned from query
	var $numrow = 0;     // returned from exec
	var $driver = '';
	
	static $connects = array(); // storage for connections
    // +--------------------------------------------------------------- +
	function __construct( $config=NULL, $new=FALSE )
	{
		$this->connect( $config, $new );
	}
    // +--------------------------------------------------------------- +
	function connect( $config, $new=FALSE ) 
	{
		if( $new == self::FORCE_NEW ) {
			; // forced to create new connect 
		}
		else
		if( isset( self::$connects[ $config ] ) ) {
			$this->conn = self::$connects[ $config ];
			return $this;
		}
		// this routine taken from 
		// http://jp.php.net/manual/ja/class.pdo.php
		// thanks a lot.
		
		// parse ini file
		// $config is absolute path. 
		if( !have_value( $config ) ) {
	        $config   = realpath( dirname( __FILE__ ) . "/../{$config}" );
		}
		if( !file_exists( $config ) ) {
			throw new DbaPdo_NoIniFile_Exception( 'file not found:'.$config );
		}
        $parse = parse_ini_file ( $config , TRUE ) ;
		if( WORDY > 3 ) {
			$wt_info = $parse;
			$wt_info[ 'db_password' ] = '*****';
			wt( $wt_info, "config:{$config}, ini:{$ini}" );
		}
		
        $this->driver   = $parse[ "db_driver" ];
        $user     = $parse[ "db_user" ];
        $password = $parse[ "db_password" ];
        $options  = $parse[ "db_options" ];
        $attrib   = $parse[ "db_attributes" ];
        $command  = $parse[ "exec_command" ];
		
		// build dsn and connect
        $dsn = "{$this->driver}:" ;
        foreach( $parse [ "dsn" ] as $k => $v ) {
            $dsn .= "{$k}={$v};" ;
        }
		$opt_pdo  = array();
		if( !empty( $options ) )
		foreach( $options as $k => $v ) {
			$opt_pdo[] = array( constant( "PDO::{$k}" ), $v );
		}
        $this->conn = new \PDO( $dsn, $user, $password, $opt_pdo );
		
		if( !empty( $attrib ) ) 
        foreach( $attrib as $k => $v ) {
            $this->conn -> setAttribute( constant( "PDO::{$k}" ), constant( "PDO::{$v}" ) ) ;
        }
		if( !empty( $command ) ) 
		foreach( $command as $exec => $cmd ) {
			$this->conn->exec( $cmd );
		}
		self::$connects[ $config ] = $this->conn;
		return $this;
    }
    // +--------------------------------------------------------------- +
    function query( $sql ) 
	{
		// for selects
		if( WORDY > 3 ) echo "rdb::query( <font color=blue>$sql</font> )...<br>\n ";
		$this->numrow = 0;
		$this->sqlh   = $this->conn->query( $sql );
		return $this->sqlh;
    }
    // +--------------------------------------------------------------- +
    function exec( $sql ) 
	{
		// for insert, update, and delete
		if( WORDY > 3 ) echo "rdb::exec( <font color=blue>$sql</font> )...<br>\n ";
		$this->sqlh   = FALSE;
		$this->numrow = @$this->conn->exec( $sql );
		return $this->numrow;
	}
    // +--------------------------------------------------------------- +
    function prepare( $sql ) 
	{
		if( WORDY > 3 ) echo "rdb::prepare( <font color=blue>$sql</font> )...<br>\n ";
		if( is_object( $this->sqlh ) ) {
			$this->sqlh->closeCursor();
		}
		$this->numrow = 0;
		$this->sqlh   = $this->conn->prepare( $sql, array( \PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL ) );
		return $this->sqlh;
    }
    // +--------------------------------------------------------------- +
    function execute( &$data ) 
	{
		if( WORDY > 3 ) echo "rdb::execute( \$data )...<br>\n ";
		if( WORDY > 5 ) wordy_table( $data, 'executing prepare data' );
		if( is_object( $this->sqlh ) ) { 
			return $this->sqlh->execute( $data );
		}
		return FALSE;
    }
    // +--------------------------------------------------------------- +
    function errorMessage() 
	{
		if( is_object( $this->sqlh ) ) { 
			$err = $this->sqlh->errorInfo(); 
		}
		else { 
			$err = $this->conn->errorInfo(); 
		}
		$msg = "{$err{0}} ({$err{1}}): {$err{2}}";
		return $msg;
	}
    // +--------------------------------------------------------------- +
    function mod4Count( $sql ) 
	{
        $regex = '/^SELECT\s+(?:ALL\s+|DISTINCT\s+)?(?:.*?)\s+FROM\s+(.*)$/i';
		$csql  = '';
        if( preg_match( $regex, $sql, $output) > 0 ) {
            $csql = "SELECT COUNT(*) AS count FROM {$output[1]}";
		}
		return $csql;
    }
    // +--------------------------------------------------------------- +
    function getPreparedCount( $sql, $data ) 
	{
		// only for normal SQL statement; not for prepared statement...
        $sql = $this->mod4Count( $sql );
        if( $sql ) {
			if( WORDY ) wt( $data, $sql );
			$sqlh = @$this->conn->prepare( $sql );
			$sqlh->execute( $data );
			$count = $sqlh->fetch( \PDO::FETCH_ASSOC, \PDO::FETCH_ORI_ABS, 0 );
			return $count[ 'count' ];
        }
		return FALSE;
    }
    // +--------------------------------------------------------------- +
    function numRows() 
	{
		if( is_numeric( $this->numrow ) ) {
			return $this->numrow;
		}
		else
		if( is_object( $this->sqlh ) ) { 
			return $this->sqlh->rowCount();
		}
        return FALSE;
    }
    // +--------------------------------------------------------------- +
    function fetchAll() {
		if( is_object( $this->sqlh ) ) { 
			return $this->sqlh->fetchAll( \PDO::FETCH_ASSOC ); 
		}
        return array();
    }
    // +--------------------------------------------------------------- +
    function fetchRow( $row ) {
		if( is_object( $this->sqlh ) ) { 
			return $this->sqlh->fetch( \PDO::FETCH_ASSOC, \PDO::FETCH_ORI_ABS, $row ); 
		}
        return array();
    }
    // +--------------------------------------------------------------- +
    function cmdTuples() {
		if( is_numeric( $this->numrow ) ) {
			return $this->numrow;
		}
        return FALSE;
    }
    // +--------------------------------------------------------------- +
    function begin() {
		return $this->conn->beginTransaction();
    }
    // +--------------------------------------------------------------- +
    function commit() {
		return $this->conn->commit();
    }
    // +--------------------------------------------------------------- +
    function rollback() {
		return $this->conn->rollBack();
    }
    // +--------------------------------------------------------------- +
    function next( $next_name ) 
	{
        if( WORDY ) echo "next( $next_name ), DB={$this->driver}<br>\n";
        if( $this->driver == 'pgsql' ) {
			$this->query( "SELECT nextval( '{$next_name}' );" );
			$next_val = $this->fetchRow(0);
			$next_val = $next_val["nextval"];
			return $next_val;
		}
		else {
			$this->query( "SELECT next_val FROM {$next_name};" );
			$next_val = $this->fetchRow( 0 );
			$next_val = $next_val['next_val'] + 1;
			$this->exec( "UPDATE {$next_name} SET next_val={$next_val};" );
			return $next_val;
		}
    }
    // +--------------------------------------------------------------- +
    function lockTable( $table ) {
        if( $this->driver == 'pgsql' ) {
			return @$this->exec( "LOCK TABLE {$table} IN ACCESS EXCLUSIVE MODE;" );
		}
		else {
			return @$this->exec( "LOCK TABLE {$table};" );
		}
    }
    // +--------------------------------------------------------------- +
    function quote( &$val ) {
		$val = $this->conn->quote( $val );
    }
    // +--------------------------------------------------------------- +
    function lastId() {
		return @$this->conn->lastInsertId();
    }
    // +--------------------------------------------------------------- +
}

/*** Obsolete methods
    // +--------------------------------------------------------------- +
    function getCount( $sql ) 
	{
		// only for normal SQL statement; not for prepared statement...
        $csql = $this->mod4Count( $sql );
        if( $csql ) {
            $sqlh = $this->conn->query( $csql, PDO::FETCH_NUM );
            return $sqlh->fetchColumn();
        }
		return FALSE;
    }
    // +--------------------------------------------------------------- +
    function result( $row, $col ) 
	{
		if( is_object( $this->sqlh ) ) { 
			$rowdata = $this->sqlh->fetch( PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $row ); 
			return $rowdata[ $col ];
		}
        return FALSE;
    }
*/
?>