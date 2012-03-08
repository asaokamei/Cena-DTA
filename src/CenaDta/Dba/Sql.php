<?php
namespace CenaDta\Dba;
/**
 *	Class for wrapping SQL statement. 
 *
 * @property \CenaDta\Dba\value|string prepare_values
 * @property mixed sql
 * @copyright     Copyright 2010-2011, Asao Kamei
 * @link          http://www.workspot.jp/cena/
 * @license       GPLv2
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
	var $style;
    
	// variables for pdo.
	var $conn;
    var $sqlh;
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
    /**
     * @param null $config   config file location.
     * @param bool $new      set TRUE to get new db-connection. 
     */
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
    /**
     * semi-singleton factory method; single for each $config. 
     * @static
     * @param null $config     config file location. 
     * @param bool $new        set to TRUE to get new db-connection. 
     * @return mixed           Sql instance. 
     */
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
    /**
     * initialize instance; clears all variables. 
     * @return Sql
     */
    function clear() {
        $this->cols     = array();
        $this->vals     = array();
        $this->func     = array();
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
    /**
     * process $val for prepared statement. saves $val's value to 
     * $this->prepare_values with unique holder name (:dba_prep_#). 
     * @param string|int $val     value to use in Sql prepare statement. 
     * @return Sql
     */
    function prepareGetHolder( &$val ) {
		$holder = ':dba_prep_' . count( $this->prepare_values );
		$this->prepare_values[ $holder ] = $val;
		if( WORDY > 3 ) echo "prepareGetHolder( $val )=>$holder <br>";
		$val = $holder;
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * execute prepared statement. 
     * @param $prepare_values   
     * @return Sql
     */
    function xxx_prepareExecute( $prepare_values ) {
		$this->rdb->execute( $prepare_values );
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * resets properties for prepared statement: prepare_values and func. 
     * @return Sql
     */
    function prepareReset() {
		$this->prepare_values = array();
		$this->func           = array();
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * process data for prepared statement. 
     * @param $data    an array or value. 
     * @return Sql
     */
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
    /**
     * a wrapper for makeWhere method but process $data for prepared 
     * statement  using prepareData. 
     * @param $col
     * @param $val
     * @param string $rel
     * @param string $op
     * @return Sql
     */
    function prepareWhere( $col, $val, $rel='', $op='' ) {
		$this->prepareData( $val );
		return $this->makeWhere( $col, $val, $rel, $op );
    }
    // +--------------------------------------------------------------- +
	// for constructing SQL with quotes
    // +--------------------------------------------------------------- +
    /**
     * quotes $data for safer sql statement. 
     * @param $data
     * @return Sql
     */
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
    /**
     * a wrapper method for makeWhere by processing $val by quoteData. 
     * @param $col
     * @param $val
     * @param string $rel
     * @param string $op
     * @return Sql
     */
    function quoteWhere( $col, $val, $rel='', $op='' ) {
		$this->quoteData( $val );
		return $this->makeWhere( $col, $val, $rel, $op );
    }
    // +--------------------------------------------------------------- +
    /**
     * quotes value using correct quote for db. 
     * @param $string
     * @return Sql
     */
    function quote( &$string ) {
		$this->rdb->quote( $string );
		return $this;
    }
    // +--------------------------------------------------------------- +
	// setting conditions (i.e. where)
    // +--------------------------------------------------------------- +
    /**
     * creates where statement using either prepare/quote. 
     * @param $col
     * @param string $val
     * @param string $rel
     * @param string $op
     * @return Sql
     */
    function where( $col, $val='', $rel='', $op='' ) {
		if( $this->use_prepared ) {
	    	return $this->prepareWhere( $col, $val, $rel, $op );
		}
		else {
	    	return $this->quoteWhere( $col, $val, $rel, $op );
		}
	}
    // +--------------------------------------------------------------- +
    /**
     * a quick method to search based on $condition. resets where only,
     * so you can keep issuing find method for the same table. 
     * TODO: this method belongs to later section of this class. 
     * @param array $condition
     * @return Sql
     */
    function find( $condition=array() ) 
	{
		$this->where = NULL;
		if( !empty( $condition ) && is_array( $condition ) ) {
			if( is_array( $condition[0] ) ) {
				foreach( $condition as $c ) call_user_func_array( array( $this, 'where' ), $c );
			}
			else {
				call_user_func_array( array( $this, 'where' ), $condition );
			}
		}
		$this->execSelect();
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * makes where condition, and append to $this->where.
     * $op ( $col $rel '$val' ) as AND ( col = 'val' )
     * @param $col
     * @param string $val
     * @param string $rel
     * @param string $op
     * @return Sql
     * @throws DbaSql_BadSql_Exception
     */
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
    /**
     * appends condition to $this->where. 
     * @param string $where       a condition (ex: col LIKE '%ex')
     * @param string $op   how to append (ex: 'AND').
     * @return Sql
     */
    function addWhere( $where, $op='' ) 
	{
		if( $op !== FALSE && !have_value( $op ) ) $op = 'AND';
		if( $this->where ) $this->where .= " {$op} ";
		$this->where .= $where;
		if( WORDY ) echo "addWhere( $where, $op )=> {$this->where}<br>";
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * sets $this->where. 
     * @param null $where
     * @return Sql
     */
    function setWhere( $where=NULL ) {
        $this->where = $where;
		return $this;
    }
    // +--------------------------------------------------------------- +
	// other select options
    // +--------------------------------------------------------------- +
    /**
     * set columns to select. default is '*'. 
     * @param null $cols
     * @return Sql
     */
    function columns( $cols=NULL ) {
		if( !have_value( $cols ) ) $cols = '*';
        $this->cols = $cols;
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * set values for insert/update statement. 
     * @param array $vals         values to set
     * @param null $func    statement (ex: NULL) to set
     * @return Sql
     */
    function values( $vals, $func=NULL ) {
        $this->vals = array_merge( $this->vals, $vals );
		if( have_value( $func ) ) {
	        $this->func = array_merge( $this->func, $func );
		}
		$this->_preprocessVals();
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * append value for insert/update statement. 
     * @param $var
     * @param $val
     * @return Sql
     */
    function addVal( $var, $val ) {
        $this->vals[ $var ] = $val;
		$this->_preprocessVals();
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * append statement for insert/update statement. 
     * @param $var
     * @param $func
     * @return Sql
     */
    function addFunc( $var, $func ) {
        $this->func[ $var ] = $func;
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * remove value from $this->vals. 
     * @param $var_name
     * @return Sql
     */
    function delVal( $var_name ) {
        if( isset( $this->vals[ $var_name ] ) ) {
            unset( $this->vals[ $var_name ] );
        }
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * remove statement from $this->func.
     * @param $var_name
     * @return Sql
     */
    function delFunc( $var_name ) {
        if( isset( $this->func[ $var_name ] ) ) {
            unset( $this->func[ $var_name ] );
        }
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * pre-process values; empty value (i.e. NULL) is moved from 
     * $this->vals to $this->func with statement as DEFAULT. 
     * TODO: does this work with prepared statement???
     */
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
    /**
     * final preparation of $this->{func|vals|prepareData}. 
     * @return Sql
     */
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
    /**
     * sets ORDER BY statement. 
     * @param $order
     * @return Sql
     */
    function order( $order ) {
        $this->order = $order;
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * sets GROUP BY statement. 
     * @param $group
     * @return Sql
     */
    function group( $group ) {
        $this->group = $group;
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * sets FROM $table statement. 
     * @param $table
     * @return Sql
     */
    function table( $table ) {
        $this->table = $table;
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * sets HAVING statement. 
     * @param $having
     * @return Sql
     */
    function having( $having ) {
        $this->having = $having;
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * sets other statement. 
     * @param $misc
     * @return Sql
     */
    function misc( $misc ) {
        $this->misc = $misc;
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * sets LIMIT statement. 
     * @param $limit
     * @param bool $offset
     * @return Sql
     */
    function limit( $limit, $offset=FALSE ) {
        $this->limit  = $limit;
        if( $offset !== FALSE ) $this->offset = $offset;
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * sets OFFSET statement. 
     * @param $offset
     * @param bool $limit
     * @return Sql
     */
    function offset( $offset, $limit=FALSE ) {
        if( $limit !== FALSE ) $this->limit  = $limit;
        $this->offset = $offset;
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * makes SQL statement based on $style (ex: SELECT, INSERT). 
     * @param $style
     * @return Sql
     * @throws DbaSql_BadSql_Exception
     */
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
    /**
     * makes insert sql statement. 
     * @param null|string $type
     * @return Sql
     * @throws DbaSql_BadSql_Exception
     */
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
    /**
     * make update sql statement. 
     * @return Sql
     * @throws DbaSql_BadSql_Exception
     */
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
    /**
     * make delete sql statement. 
     * @return Sql
     * @throws DbaSql_BadSql_Exception
     */
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
    /**
     * make select sql statement. 
     * @param null $style
     * @return Sql
     * @throws DbaSql_BadSql_Exception
     */
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
    /**
     * set log.
     * @param $sql
     * @param $time1
     * @param $time2
     */
    function sql_log( $sql, $time1, $time2 )
    {
        /** global variables to pass extra info in log data. */
		global $formsql_log_info;
		
		$item   = array();
		$item[] = date( "Y/m/d H:s:i " );
		if( !empty( $formsql_log_info ) )
		while( list( $key, $val ) = each( $formsql_log_info ) ) { 
			$item[] = "{$key}=>{$val} "; 
		}
		$item[] = sprintf( " %f ", $time2 - $time1 );
		$item[] = $sql;
		if( in_array( $this->style, $this->exec_type ) ) {
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
    /**
     * set log to a file. 
     * @param null $file_id
     * @return string
     */
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
    /**
     * gets micro time (micro-sec). 
     * @return float
     */
    function getMicroTime() {
        // taken from www.php.net, about micro time
        list( $usec, $sec ) = explode( " ",microtime() );
        return ( (float) $usec + (float) $sec);
    }
    // +--------------------------------------------------------------- +
	// execXXXX methods.
	// execute and reset prepare values.
    // +--------------------------------------------------------------- +
    /**
     * execute select sql. resets data for prepare. 
     * @return Sql
     */
    function execSelect() {
		$this->makeSQL( self::SELECT );
		$this->execSQL();
		return $this->prepareReset();
    }
    // +--------------------------------------------------------------- +
    /**
     * makes select sql, get count by executing count sql, and execute 
     * the select sql statement. resets data for prepare.
     * @param $count
     * @return Sql
     */
    function execSelectCount( &$count ) {
		$this->makeSQL( self::SELECT )
			 ->fetchCount( $count )
			 ->execSQL();
		return $this->prepareReset();
    }
    // +--------------------------------------------------------------- +
    /**
     * execute update sql. resets data for prepare.
     * @return Sql
     */
    function execUpdate() {
		$this->makeSQL( self::UPDATE );
		$this->execSQL();
		return $this->prepareReset();
    }
    // +--------------------------------------------------------------- +
    /**
     * execute delete sql. resets data for prepare.
     * @return Sql
     */
    function execDelete() {
		$this->makeSQL( self::DELETE );
		$this->execSQL();
		return $this->prepareReset();
    }
    // +--------------------------------------------------------------- +
    /**
     * execute insert sql. resets data for prepare.
     * @param array $vals
     * @param null $table
     * @return Sql
     */
    function execInsert( $vals=array(), $table=NULL ) {
		if( !empty(     $vals  ) ) $this->values( $vals );
		if( have_value( $table ) ) $this->table( $table );
		$this->makeSQL( self::INSERT );
		$this->execSQL();
		return $this->prepareReset();
    }
    // +--------------------------------------------------------------- +
    /**
     * execute a sql statement. 
     * @param null $sql
     * @return Sql
     * @throws DbaSql_Exception
     */
    function execSQL( $sql=NULL )
    {
        if( !$sql ) {
			$sql = $this->sql;
			if( strtoupper( substr( $sql, 0, 6 ) ) === 'SELECT' ) $this->style = self::SELECT;
		}
        if( !$sql ) {
			throw new DbaSql_Exception( 'no SQL statement' );
		}
        if( Sql::$log_sql ) $time1 = $this->getMicroTime();
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
		catch( \PDOException $e ) {
            $msg = $this->rdb->errorMessage();
            if( WORDY ) {
				echo "<font color=red>PDO exec: \"{$sql}\" )</font><br>{$msg}<br>\n";
				wt( $this->prepare_values, 'prepare_values' );
			}
            throw new DbaSql_Exception( "SQL Execution Error (Message:{$msg}) (SQL:{$sql})\n" );
		}
        if( Sql::$log_sql ) {
			$time2 = $this->getMicroTime();
			$this->sql_log( $sql, $time1, $time2 );
        } 
        if( WORDY > 5 ) echo " -- executed SQL: $sql<br>\n";
        return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * get next counter value from a sequence. 
     * @param $next_name   name of sequencer. 
     * @return array
     */
    function nextCounter( $next_name ) {
        $next = $this->rdb->next( $next_name );
        if( WORDY > 3 ) echo "nextCounter( $next_name ) => $next <br>\n";
		return $next;
    }
    // +--------------------------------------------------------------- +
    /**
     * fetch number of rows selected. 
     * @return bool|int
     */
    function fetchNumRow() {
        $num = $this->rdb->numRows(); 
        if( WORDY > 3 ) echo "fetchNumRow()=> $num " . 
			"<font color=brown>WARNING! this value can be false with PDO!!!</font><br>\n";
        return $num;
    }
    // +--------------------------------------------------------------- +
    /**
     * fetch all selected data in an array. 
     * @param $data
     * @return int
     */
    function fetchAll( &$data ) {
        if( WORDY > 3 ) echo "<br><i>formSQL::fetchAll( &\$data )...</i><br>\n";
		$data = $this->rdb->fetchAll();
		return count( $data );
    }
    // +--------------------------------------------------------------- +
    /**
     * fetch $row's data. 
     * @param $row
     * @return array
     */
    function fetchRow( $row ) {
        if( WORDY > 3 ) echo "<br><i>formSQL::fetchRow( $row )...</i><br>\n";
		return $this->rdb->fetchRow( $row );
    }
    // +--------------------------------------------------------------- +
    /**
     * issue sql to count based on select 
     * @param $count
     * @return Sql
     */
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
	//    $option[ 'limit'   ] : number of rows per page.
	//    $option[ 'options' ] : options to pass to other page.
	// +--------------------------------------------------------------- +
    /**
     * 
     * @param $pn
     * @param array $option
     * @return Sql
     */
    function setPage( &$pn, $option=array() ) {
		require_once( dirname( __FILE__ ) . '/dba.page.php' );
		$page = new dba_page( $this, $option );
		$page->setPage()->fetchPN( $pn );
		$this->execSelect();
		return $this;
    }
    // +--------------------------------------------------------------- +
    /**
     * @param $data
     * @param $pn
     * @param array $option
     * @return Sql
     */
    function fetchPage( &$data, &$pn, $option=array() ) {
		require_once( dirname( __FILE__ ) . '/dba.page.php' );
		$page = new dba_page( $this, $option );
		$page->setOptions( $option )
			->fetchPage( $data )
			->fetchPN( $pn );
		return $this;
    }
    // +--------------------------------------------------------------- +
}


