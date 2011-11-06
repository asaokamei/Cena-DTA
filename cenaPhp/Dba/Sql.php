<?php
namespace CenaDta\Dba;
/**
 *	Class for wrapping SQL statement. 
 *
 *	@copyright     Copyright 2010-2011, Asao Kamei
 *	@link          http://www.workspot.jp/cena/
 *	@license       GPLv2
 */

if( !defined( "WORDY" ) ) define( "WORDY",  0 ); // very wordy...

require_once( dirname( __FILE__ ) . "/FormSql.php" );
require_once( dirname( __FILE__ ) . "/Pdo.php" );
require_once( dirname( __FILE__ ) . "/../class/class.ext_func.php" );

class DbaSql_Exception extends \Exception {}
class DbaSql_BadSql_Exception extends DbaSql_Exception {} // for badly formed sql statement.

class Sql
{
	const INSERT  = "INSERT";
	const INSERT2 = "INSERT2";
	const UPDATE  = "UPDATE";
	const SELECT  = "SELECT";
	const DELETE  = "DELETE";
	const SELECT_DISTINCT   = "SELECT DISTINCT";
	const SELECT_FOR_UPDATE = "SELECT FOR UPDATE";
	const DB_INI_FILE_NAME  = 'dba.ini.php';
	
    // variables to build SQL statement...
    var $cols;        // array of columns used in SELECT sql statement. 
    var $vals;        // array of values used in INSERT/UPDATE sql statement.
    var $func;        // array of functions used in INSERT/UPDATE sql statement.
    var $table;       // names of db table
    var $order;       // order by statement
    var $where;       // where statement
    var $group;       // group by statement
    var $having;      // having statement
    var $misc;        // misc statement such as LIMIT
	var $limit;       // limit 
	var $offset;      // offset
	
	// variables for pdo.
	var $conn;
	var $err_num, $err_msg;
	var $exec_type  = array( self::INSERT, self::INSERT2, self::UPDATE, self::DELETE );
	var $query_type = array( self::SELECT, self::SELECT_DISTINCT, self::SELECT_FOR_UPDATE );
	
	// for prepared statement
	var $prepared_values = array();
	var $use_prepared    = TRUE;
	
	// for singleton and loggings.
	static $instances = array();
	static $log_sql  = FALSE; // set to TRUE to log sql 
    // +--------------------------------------------------------------- +
    function __construct( $config=NULL, $new=FALSE )
    {
		$this->clear();
		if( !$config ) $config = self::DB_INI_FILE_NAME;
		try {
	        $this->rdb = new Pdo( $config, $new );
		}
		catch ( DbaPdo_Exception $e ) {
			throw new DbaSql_Exception( "failed to create PDO driver with Error Message: <br />".$e->getMessage() );
		}
		$this->err_num = 0;
		$this->err_msg = NULL;
		if( WORDY ) echo "form_sql instance...<br>\n";
    }
	// +--------------------------------------------------------------- +
    static function getInstance( $config=NULL, $new=FALSE )
    {
		if( !$config ) $config = self::DB_INI_FILE_NAME;
        if( !isset( self::$instances[ $config ] ) ) {
            self::$instances[ $config ] = new Sql( $config, $new );
        }
        return self::$instances[ $config ];
    }
    // +--------------------------------------------------------------- +
	// Building SQL statement
    // +--------------------------------------------------------------- +
    function clear() {
        $this->cols     = array();
        $this->vals     = array();
        $this->func     = array();
        $this->wh_arr   = array();
        $this->table    = NULL;
        $this->order    = NULL;
        $this->where    = NULL;
        $this->group    = NULL;
        $this->having   = NULL;
        $this->misc     = NULL;
        $this->limit    = NULL;
        $this->offset   = NULL;
		
        $this->style    = NULL;
        $this->sql      = NULL;
        $this->err_num  = 0;
        $this->err_msg  = '';
        $this->prepare_values = array();
		return $this;
    }
    // +--------------------------------------------------------------- +
	// for prepared statement
    // +--------------------------------------------------------------- +
    function prepareGetHolder( &$val ) {
		$holder = ':dba_prep_' . count( $this->prepare_values );
		$this->prepare_values[ $holder ] = $val;
		if( WORDY > 3 ) echo "prepareGetHolder( $val )=>$holder <br>";
		$val = $holder;
		return $this;
    }
    // +--------------------------------------------------------------- +
    function prepareExecute( $prepare_values ) {
		$this->rdb->execute( $prepare_values );
		return $this;
    }
    // +--------------------------------------------------------------- +
    function prepareReset() {
		$this->prepare_values = array();
		$this->func           = array();
		return $this;
    }
    // +--------------------------------------------------------------- +
    function prepareData( &$data ) {
		if( empty( $data ) ) return $this;
		if( is_array( $data ) ) {
			foreach( $data as $col => $val ) {
				$this->prepareGetHolder( $data[ $col ] );
			}
		}
		else {
			$this->prepareGetHolder( $data );
		}
		return $this;
    }
    // +--------------------------------------------------------------- +
    function prepareWhere( $col, $val, $rel='', $op='' ) {
		$this->prepareData( $val );
		return $this->makeWhere( $col, $val, $rel, $op );
    }
    // +--------------------------------------------------------------- +
	// for constructing SQL with quotes
    // +--------------------------------------------------------------- +
    function quoteData( &$data ) {
		if( empty( $data ) ) return $this;
		if( is_array( $data ) ) {
			foreach( $data as $col => $val ) {
				$this->rdb->quote( $data[ $col ] );
			}
		}
		else {
			$this->rdb->quote( $data );
		}
		return $this;
    }
    // +--------------------------------------------------------------- +
    function quoteWhere( $col, $val, $rel='', $op='' ) {
		$this->quoteData( $val );
		return $this->makeWhere( $col, $val, $rel, $op );
    }
    // +--------------------------------------------------------------- +
    function quote( &$string ) {
		$this->rdb->quote( $string );
		return $this;
    }
    // +--------------------------------------------------------------- +
	// setting conditions (i.e. where)
    // +--------------------------------------------------------------- +
    function where( $col, $val='', $rel='', $op='' ) {
		if( $this->use_prepared ) {
	    	return $this->prepareWhere( $col, $val, $rel, $op );
		}
		else {
	    	return $this->quoteWhere( $col, $val, $rel, $op );
		}
	}
    // +--------------------------------------------------------------- +
    function find( $cond=array() ) 
	{
		$this->where = NULL;
		if( !empty( $cond ) && is_array( $cond ) ) {
			if( is_array( $cond[0] ) ) {
				foreach( $cond as $c ) $this->where( $c[0], $c[1], $c[2], $c[3] );
			}
			else {
				$this->where( $cond[0], $cond[1], $cond[2], $cond[3] );
			}
		}
		$this->execSelect();
		return $this;
    }
    // +--------------------------------------------------------------- +
    function makeWhere( $col, $val='', $rel='', $op='' ) 
	{
        $where = '';
		if( $rel === FALSE ) {
			$rel = ''; // practically do nothing.
		}
		else
		if( !have_value( $rel ) ) {
			$rel = '=';
		}
		$rel = strtoupper( $rel );
		
		if( $rel === 'IN' ) { // for column IN ( v1, v2, ... )
			if( is_array( $val ) ) {
				$val = "( " . implode( ", ", $val ) . " )";
			}
			else {
				$val = "( {$val} )";
			}
		}
		else
		if( $rel === 'BETWEEN' ) { // for column BETWEEN v1 and v2
			if( is_array( $val ) ) {
				$val = "{$val{0}} AND {$val{1}}";
			}
			else 
			throw new DbaSql_BadSql_Exception( "where: val for between not an array" );
		}
		$where .= "{$col} {$rel} {$val}";
		$this->addWhere( $where, $op );
		
		return $this;
    }
    // +--------------------------------------------------------------- +
    function addWhere( $where, $op='' ) 
	{
		if( $op !== FALSE && !have_value( $op ) ) $op = 'AND';
		if( $this->where ) $this->where .= " {$op} ";
		$this->where .= $where;
		if( WORDY ) echo "where( $col, $val, $rel, $op )=> {$this->where}<br>";
		return $this;
    }
    // +--------------------------------------------------------------- +
    function setWhere( $where=NULL ) {
        $this->where = $where;
		return $this;
    }
    // +--------------------------------------------------------------- +
	// other select options
    // +--------------------------------------------------------------- +
    function columns( $cols=NULL ) {
		if( !have_value( $cols ) ) $cols = '*';
        $this->cols = $cols;
		return $this;
    }
    // +--------------------------------------------------------------- +
    function values( $vals, $func=NULL ) {
        $this->vals = array_merge( $this->vals, $vals );
		if( have_value( $func ) ) {
	        $this->func = array_merge( $this->func, $func );
		}
		$this->_preprocessVals();
		return $this;
    }
    // +--------------------------------------------------------------- +
    function addVal( $var, $val ) {
        $this->vals[ $var ] = $val;
		$this->_preprocessVals();
		return $this;
    }
    // +--------------------------------------------------------------- +
    function addFunc( $var, $func ) {
        $this->func[ $var ] = $func;
		return $this;
    }
    // +--------------------------------------------------------------- +
    function delVal( $var_name ) {
        if( isset( $this->vals[ $var_name ] ) ) {
            unset( $this->vals[ $var_name ] );
        }
		return $this;
    }
    // +--------------------------------------------------------------- +
    function delFunc( $var_name ) {
        if( isset( $this->func[ $var_name ] ) ) {
            unset( $this->func[ $var_name ] );
        }
		return $this;
    }
    // +--------------------------------------------------------------- +
    function _preprocessVals() {
		// if value is NULL, set it to DEFAULT in func.
		if( !empty( $this->vals ) ) 
		foreach( $this->vals as $key => $val ) {
			if( $val === NULL ) {
				$this->func[ $key ] = 'DEFAULT';
				unset( $this->vals[ $key ] );
			}
		}
    }
    // +--------------------------------------------------------------- +
    function _prepareValsForSql() {
		// process vals (prepared or quoted) and move to func.
		if( $this->use_prepared ) {
			$this->prepareData( $this->vals );
			$this->func = array_merge( $this->func, $this->vals );
			$this->vals = array();
		}
		else {
			$this->quoteData( $this->vals );
			$this->func = array_merge( $this->func, $this->vals );
			$this->vals = array();
		}
		return $this;
    }
    // +--------------------------------------------------------------- +
    function order( $order ) {
        $this->order = $order;
		return $this;
    }
    // +--------------------------------------------------------------- +
    function group( $group ) {
        $this->group = $group;
		return $this;
    }
    // +--------------------------------------------------------------- +
    function table( $table ) {
        $this->table = $table;
		return $this;
    }
    // +--------------------------------------------------------------- +
    function having( $having ) {
        $this->having = $having;
		return $this;
    }
    // +--------------------------------------------------------------- +
    function misc( $misc ) {
        $this->misc = $misc;
		return $this;
    }
    // +--------------------------------------------------------------- +
    function limit( $limit, $offset=FALSE ) {
        $this->limit  = $limit;
        if( $offset !== FALSE ) $this->offset = $offset;
		return $this;
    }
    // +--------------------------------------------------------------- +
    function offset( $offset, $limit=FALSE ) {
        if( $limit !== FALSE ) $this->limit  = $limit;
        $this->offset = $offset;
		return $this;
    }
    // +--------------------------------------------------------------- +
    function makeSQL( $style )
    {
        if( WORDY > 2 ) echo "<br><i>formSQL::makeSQL( $style )...</i><br>\n";
        $style = strtoupper( $style );
        switch( $style )
        {
            case self::INSERT:
                $this->makeSqlInsert();
                break;
            case self::INSERT2:
                $this->makeSqlInsert( FormSql::INSERT_NO_COL );
                break;
            case self::UPDATE:
                $this->makeSqlUpdate();
                break;
            case self::SELECT:
            case self::SELECT_DISTINCT:
            case self::SELECT_FOR_UPDATE:
               $this->makeSqlSelect( $style );
                break;
            case self::DELETE:
                $this->makeSqlDelete();
                break;
            default:
				throw new DbaSql_BadSql_Exception( 'Bad sql style: ' . $style );
        }
        if( WORDY > 3 ) echo "-- SQL: {$this->sql} <br>\n";
		return $this;
    }
    // +--------------------------------------------------------------- +
    function makeSqlInsert( $type=NULL ) 
	{
        if( !$this->table ) { 
			throw new DbaSql_BadSql_Exception( 'makeInsert missing table' );
        }
        if( !$this->vals && !$this->func ) { 
			throw new DbaSql_BadSql_Exception( 'makeInsert missing vals' );
        }
		$this->_prepareValsForSql();
        $sql = FormSql::insert( $this->table, $this->func, $type );
		$this->sql   = $sql;
        $this->style = self::INSERT;
		return $this;
    }
    // +--------------------------------------------------------------- +
    function makeSqlUpdate()
    {
        if( !$this->table ) { 
			throw new DbaSql_BadSql_Exception( 'makeSqlUpdate missing table' );
        }
        if( !$this->vals && !$this->func ) { 
			throw new DbaSql_BadSql_Exception( 'makeSqlUpdate missing vals' );
        }
		$this->_prepareValsForSql();
        $sql = FormSql::update( $this->table, $this->func, $this->where );
		$this->sql   = $sql;
        $this->style = self::UPDATE;
		return $this;
    }
    // +--------------------------------------------------------------- +
    function makeSqlDelete( )
    {
        if( !$this->table ) { 
			throw new DbaSql_BadSql_Exception( 'makeSqlDelete missing table' );
        }
        $sql = FormSql::delete( $this->table, $this->where );
		$this->sql   = $sql;
        $this->style = self::DELETE;
		return $this;
    }
    // +--------------------------------------------------------------- +
    function makeSqlSelect( $style=NULL )
    {
        if( !$this->table ) { 
			throw new DbaSql_BadSql_Exception( 'makeSqlSelect missing table' );
        }
		$options = array(
			'group'    => $this->group,
			'having'   => $this->having,
			'order_by' => $this->order,
			'misc'     => $this->misc,
			'limit'    => $this->limit,
			'offset'   => $this->offset,
		);
        if( $style == Sql::SELECT_DISTINCT ) {
			$sql = FormSql::selectDistinct( $this->table, $this->cols, $this->where, $options );
        }
        else 
        if( $style == Sql::SELECT_DISTINCT ) {
			$sql = FormSql::selectUpdate( $this->table, $this->cols, $this->where, $options );
        }
        else {
			$sql = FormSql::select( $this->table, $this->cols, $this->where, $options );
			$style = self::SELECT;
        }
		$this->sql   = $sql;
        $this->style = $style;
		return $this;
    }
    // +--------------------------------------------------------------- +
    function sql_log( $time1, $time2 )
    {
		global $formsql_log_info;
		
		$item   = array();
		$item[] = date( "Y/m/d H:s:i " );
		if( !empty( $formsql_log_info ) )
		while( list( $key, $val ) = each( $formsql_log_info ) ) { 
			$item[] = "{$key}=>{$val} "; 
		}
		$item[] = sprintf( " %f ", $time2 - $time1 );
		$item[] = $sql;
		if( in_array( $style, $this->exec_type ) ) {
			$num_effected = $this->rdb->cmdtuples( $this->sqlh );
		}
		else {
			$num_effected = $this->rdb->numRows( $this->sqlh );
		}
		$item[] = "Rows({$num_effected})";
		
		$log    = implode( "|", $item ) . "\n";
		$filename = $this->getLogFile();
		if( $fd = fopen( $filename, "a" ) ) 
		{
			set_file_buffer( $fd, 0 );
			flock( $fd, LOCK_EX );
			fwrite( $fd, $log );
			flock( $fd, LOCK_UN );
			fclose( $fd );
		}
    }
    // +--------------------------------------------------------------- +
    function getLogFile( $file_id=NULL )
    {
        // use $file_id to log to different files. 
        // ex) set $file_id as userID to make a log file for each user.
        $dir_log = "./logs";
        if( !is_dir( $dir_log ) ) mkdir( $dir_log, 0777 );
        
        $to_day = date( "Ymd" );
        $dir_log .= "/" . $to_day;
        if( !is_dir( $dir_log ) ) mkdir( $dir_log, 0777 );
        
        if( $file_id ) {
            $file_name = "sql_{$file_id}.log";
        }
        else {
            $file_name = "sql.log";
        }
        return "{$dir_log}/{$file_name}";
    }
    // +--------------------------------------------------------------- +
    function getmicrotime() {
        // taken from www.php.net, about microtime
        list($usec, $sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }
    // +--------------------------------------------------------------- +
	// execXXXX methods.
	// execute and reset prepare values.
    // +--------------------------------------------------------------- +
    function execSelect() {
		$this->makeSQL( self::SELECT );
		$this->execSQL();
		return $this->prepareReset();
    }
    // +--------------------------------------------------------------- +
    function execSelectCount( &$count ) {
		$this->makeSQL( self::SELECT )
			 ->fetchCount( $count )
			 ->execSQL();
		return $this->prepareReset();
    }
    // +--------------------------------------------------------------- +
    function execUpdate() {
		$this->makeSQL( self::UPDATE );
		$this->execSQL();
		return $this->prepareReset();
    }
    // +--------------------------------------------------------------- +
    function execDelete() {
		$this->makeSQL( self::DELETE );
		$this->execSQL();
		return $this->prepareReset();
    }
    // +--------------------------------------------------------------- +
    function execInsert( $vals=array(), $table=NULL ) {
		if( !empty(     $vals  ) ) $this->values( $vals );
		if( have_value( $table ) ) $this->setTable( $table );
		$this->makeSQL( self::INSERT );
		$this->execSQL();
		return $this->prepareReset();
    }
    // +--------------------------------------------------------------- +
    function execSQL( $sql=NULL )
    {
        if( !$sql ) {
			$sql = $this->sql;
			if( strtoupper( substr( $sql, 0, 6 ) ) === 'SELECT' ) $this->style = self::SELECT;
		}
        if( !$sql ) {
			throw new DbaSql_Exception( 'no SQL statement' );
		}
        if( Sql::$log_sql ) $time1 = $this->getmicrotime();
		try {
	        if( WORDY>1 ) {
				wordy_table( $this->prepare_values, "execSQL/{$this->style}( $sql )<br>\n" );
			}
			if( !empty( $this->prepare_values ) ) {
				if( WORDY > 3 ) wordy_table( $this->prepare_values, 'prepared values' );
				$this->rdb->prepare( $sql );
				$this->rdb->execute( $this->prepare_values );
			}
			else 
			if( in_array( $this->style, $this->query_type ) ) {
				if( WORDY > 5 ) echo "execSQL: query( $sql ).<br>";
				$this->rdb->query( $sql );
			}
			else {
				if( WORDY > 5 ) echo "execSQL: exec( $sql ).<br>";
				$this->rdb->exec( $sql );
			}
		}
		catch( PDOException $e ) {
            $msg = $this->rdb->errorMessage();
            if( WORDY ) {
				echo "<font color=red>PDO exec: \"{$sql}\" )</font><br>{$msg}<br>\n";
				wt( $prepare_values, 'prepare_values' );
			}
            throw new DbaSql_Exception( "SQL Execution Error (Message:{$msg}) (SQL:{$sql})\n" );
		}
        if( Sql::$log_sql ) {
			$time2 = $this->getmicrotime();
			$this->sql_log( $time1, $time2 );
        } 
        if( WORDY > 5 ) echo " -- executed SQL: $sql<br>\n";
        return $this;
    }
    // +--------------------------------------------------------------- +
    function nextCounter( $next_name ) {
        $next = $this->rdb->next( $next_name );
        if( WORDY > 3 ) echo "nextCounter( $next_name ) => $next <br>\n";
		return $next;
    }
    // +--------------------------------------------------------------- +
    function fetchNumRow() {
        $num = $this->rdb->numRows(); 
        if( WORDY > 3 ) echo "fetchNumRow()=> $num " . 
			"<font color=brown>WARNING! this value can be false with PDO!!!</font><br>\n";
        return $num;
    }
    // +--------------------------------------------------------------- +
    function fetchAll( &$data ) {
        if( WORDY > 3 ) echo "<br><i>formSQL::fetchAll( &\$data )...</i><br>\n";
		$data = $this->rdb->fetchAll();
		return count( $data );
    }
    // +--------------------------------------------------------------- +
    function fetchRow( $row ) {
        if( WORDY > 3 ) echo "<br><i>formSQL::fetchRow( $row )...</i><br>\n";
		return $this->rdb->fetchRow( $row );
    }
    // +--------------------------------------------------------------- +
    function fetchCount( &$count ) {
		// use this method after makeSqlSelect then execute SQL.
		// i.e. $this->makeSqlSelect()->fetchCount( $count )->execSQL();
		// or use execSelectCount() method
		$count = $this->rdb->getPreparedCount( $this->sql, $this->prepare_values );
        if( WORDY > 3 ) echo "<br>fetchCount()=>{$count}<br>\n";
        return $this;
    }
    // +--------------------------------------------------------------- +
	//  for pagination. requires dba_page class.
	//    $opt[ 'limit'   ] : number of rows per page.
	//    $opt[ 'options' ] : options to pass to other page.
	// +--------------------------------------------------------------- +
    function setPage( &$pn, $opt=array() ) {
		require_once( dirname( __FILE__ ) . '/dba.page.php' );
		$page = new dba_page( $this, $opt );
		$page->setPage()->fetchPN( $pn );
		$this->execSelect();
		return $this;
    }
    // +--------------------------------------------------------------- +
    function fetchPage( &$data, &$pn, $opt=array() ) {
		require_once( dirname( __FILE__ ) . '/dba.page.php' );
		$page = new dba_page( $this, $opt );
		$page->setOptions( $options )
			->fetchPage( $data )
			->fetchPN( $pn );
		return $this;
    }
    // +--------------------------------------------------------------- +
}



/*** Obsolete methods
    // +--------------------------------------------------------------- +
	// obsolete... use prepared statement, instead.
    // +--------------------------------------------------------------- +

    function xxxpushValue( $col, $val='', $rel=FALSE, $op=FALSE ) 
	{
		$add_quote = create_function( '&$v,$k', '$v = "' . $v . '";' );
		array_walk( $val, $add_quote );
		return $this->where( $col, $val, $rel, $op );
    }
    // +--------------------------------------------------------------- +
    function xxxsetWhere( $where ) {
        if( WORDY > 3 ) echo "<br><i>formSQL::setWhere( $where )...</i><br>\n";
        $this->where = $where;
		return $this;
    }
    // +--------------------------------------------------------------- +
    function xxxaddWhere( $where, $rel="AND", $end='' ) 
	{
		if( WORDY > 3 ) echo "<br><i>form_sql::addWhere( $where, $rel )</i><br>\n";
		$this->where = trim( $this->where );
		$where = trim( $where );
        if( have_value( $where ) ) 
		{
			if( substr( $this->where, -1, 1 ) == '(' ) {
				$this->where .= " {$where}";
			}
			else
			if( $this->where ) {
				$this->where .= " {$rel} {$where}";
			}
			else {
				$this->where  = "{$where}";
			}
			if( $end ) $this->where .= " {$end}"; // for ending parenthesis.
		}
		if( WORDY > 3 ) echo "->{$this->where}<br>\n";
		return $this;
    }
    // +--------------------------------------------------------------- +
    function prepareExec() {
		$this->rdb->execute( $this->prepare_values );
		return $this;
    }
    // +--------------------------------------------------------------- +


*/

?>