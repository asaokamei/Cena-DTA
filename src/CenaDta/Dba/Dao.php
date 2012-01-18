<?php
namespace CenaDta\Dba;
/**
 *	Data Access Object.
 *
 *	@copyright     Copyright 2010-2011, Asao Kamei
 *	@link          http://www.workspot.jp/cena/
 *	@license       GPLv2
 */
require_once( 'Sql.php' );
require_once( 'DaoObjPool.php' );
require_once( 'DaoHelper.php' );

class DaoException           extends \Exception {}
class DaoNoDatumException    extends DaoException {}
class DaoMoreThanOneException    extends DaoException {}
class DaoNoIdNameException   extends DaoException {}

/* =================================================================================== *
   baseDAO : base Data Access Object
   	requires PHP 5.3.0 or later...
 * =================================================================================== */
class Dao extends Sql
{
	// +----------------------------------------------- +
    /** $_table
     *  name of model which inherited Model.
     *  @var string
     */
	var $_table         = FALSE;
	// +----------------------------------------------- +
	/** $tableName
	 *	name of database table. (cannot be a joined name).
     *  @var string
	 */
	var $tableName      = FALSE;
	// +----------------------------------------------- +
    /** $id_info
     *  old style id info. please refer to DaoHelper's make_code
     *  methods
     *  @see DaoHelper::make_code
     *  @var array
     */
    var $id_info = array(             // primary key info
		'' => '', 
	);
	// +----------------------------------------------- +
    /** $id_name
     *  name of id (primary key)
     *  @var string
     */
	var $id_name;
	// +----------------------------------------------- +
    /** $col_info
     *  column information of the model.
     *  mod_{date|time|datetime}: DaoHelper::update_datetime
     *  new_{date|time|datetime}: DaoHelper::insert_datetime
     *	@var array
     */
	var $col_info = array(
		'new_date'     => FALSE,
		'new_time'     => FALSE,
		'new_datetime' => FALSE,
		'mod_date'     => FALSE,
		'mod_time'     => FALSE,
		'mod_datetime' => FALSE,
		'del_flag'     => FALSE,
		'del_value'    => FALSE,
	);
	// +----------------------------------------------- +
    /** $restrictAccess
     *  set to TRUE to use restriction filters (daoRestrictValue and
     *  daoRestrictWhere). _table model must supply restriction info. 
     *  @var boolean
     */
	var $restrictAccess = FALSE;
	// +----------------------------------------------- +
    /** $stdDatumFunc
     *  name of function used in stdDatum filter. 
     *  @var function
     */
	var $stdDatumFunc   = FALSE;
	// +----------------------------------------------- +
    /** $useJoinedTable
     *  set to TRUE to use joined table during select.
     *  @var boolean
     */
	var $useJoinedTable = FALSE;
	// +----------------------------------------------- +
    /** $throwException
     *  throws exception if set to TRUE during getDatum or some method.
     *  note that this record throws exception for some cases even if
     *  this flag is set to FALSE.
     *	@var beelan
     */
	var $throwException = TRUE;         // throws exception or not
	// +--------------------------------------------------------------- +
    /** constructor for data access object.
     * @param string $config
     *        config file name for Sql.
     * @param null $table
     */
    function __construct( $config=NULL, $table=NULL ) {
		// set table, etc. 
		parent::__construct( $config );
		$this->clear();
		if( have_value( $table ) ) {
			$this->setTableClass( $table );
		}
    }
	// +--------------------------------------------------------------- +
	function __toString() {
		return "Dao(#" . spl_object_hash( $this ).  ")::{$this->table} ";
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	sets Model for the Dao object (kept as class name).
	 *	properties are copied when set; they are:
	 *	tableName, idName, throwException
	 *
	 *	@param string $table
	 *		Model class name.
	 *	@return $this
	 */
	function setTableClass( $table ) {
		$this->_table         = $table; 
		$this->tableName      = $table::getTableName(); // not overwritten. 
		$this->table          = $table::getTableName(); // for Sql. 
		$this->idName         = $table::idName();
		$this->col_info       = $table::getColInfo();
		$this->throwException = $table::throwException();
		return $this;
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	sets flag to throw exception or not.
	 *	the default value flag is FALSE (not to throw exception).
	 *
	 *	@param boolen $throw
	 *		throws exception if set to TRUE.
	 *		set to FALSE suppress exception i.e. throwException( FALSE ).
	 *	@return boolean/$this
	 *		returns $this when flag is set, or
	 *		returns throwException flag otherwise.
	 */
	function throwException( $throw=TRUE ) {
		if( is_bool( $throw ) ) {
			$this->throwException = $throw;
			return $this;
		}
		return $this->throwException;
	}
	// +--------------------------------------------------------------- +
	/** clears the Sql properties, and sets table to dao's table.
	 *	@returns $this
	 */
    function clear() {
		parent::clear();
		$this->table( $this->getTableName() );
		return $this;
	}
	// +--------------------------------------------------------------- +
	/** returns id_name (primary key) of the table. 
	 *	@param boolen $throw
	 *		throws exception if $throw is true and $this_id_name is not set.
	 *	@returns name of id (primary key name).
	 */
    function getIdName() {
		if( !have_value( $this->idName ) && $this->throwException ) {
			throw new DaoNoIdNameException( 'no id' );
		}
		return $this->idName;
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	returns table name for dao. maybe joined table.
	 *	TODO: should return joined table if necessary.
	 *
	 *	@return string
	 *		returns table name.
	 */
    function getTableName() {
		return $this->tableName;
	}
    // +----------------------------------------------------------------------------+
    function lockTable() {
        $this->rdb->lockTable( $this->tableName );
    }
    // +----------------------------------------------------------------------------+
    //
	//          basic data manipulation: insert, update, delete, and select
	//
	// +----------------------------------------------------------------------------+
	
	// +--------------------------------------------------------------- +
	/**	
	 *	updates data. automatically setup table name only;
	 *	setup where before using this method.
	 *	filters:
	 *		stdDatum
	 *		restrictWhere
	 *		restrictValue
	 *		daoHistory
	 *
	 *	@param array &$data
	 *		data to update.
	 *		will be updated to the value stored to database.
	 *	@param array $func=array()
	 *		functions to update if exists.
	 *	@return boolen
	 *		returns TRUE on success, FALSE on fail.
	 */
    function update( &$data, $func=array() )
    {
        if( WORDY > 3 ) echo "<br>Dao::update( $id, $data, $id_name )<br>\n";
		
		$this	
			->stdDatum( $data,  'update' )
			->daoRestrictWhere( 'update' )
			->daoRestrictValue( 'update' )
		;
		if( isset( $this->dao_history ) ) {
			// save history/log data!
			$this->execSelect();
			$num = $this->fetchAll( $predata );
			for( $i = 0; $i < $num; $i++ ) {
				$this->dao_history->logMod( $predata[$i], $data );
			}
		}
		DaoHelper::update_datetime( $this->col_info, $data );
		$this
			->values( $data, $func )
			->table( $this->tableName )
			->execUpdate();
		return TRUE;
    }
	// +--------------------------------------------------------------- +
	/**	
	 *	deletes data of id. automatically setup table name only.
	 *	filters:
	 *		restrictWhere
	 *		daoHistory.
	 *
	 *	@param string $id
	 *		value of id (primary key) of data to delete.
	 *	@return boolen
	 *		returns TRUE on success, FALSE on fail.
	 */
    function removeDataFromTable( $id )
    {
        if( WORDY > 3 ) echo "<br>baseDAO::removeDataFromTable( $id )<br>\n";
        $id_name = $this->getIdName();
		$this
			->clear()
			->daoRestrictWhere( 'delete' )
			->table( $this->tableName ) 
			->Where( $id_name, $id )
			->execDelete();
		
		if( isset( $this->dao_history ) ) {
			$old_data = array( $id_name, $id );
			$this->dao_history->logDel( $old_data );
		}
        return TRUE;
    }
	// +--------------------------------------------------------------- +
	/**	
	 *	insert data.
	 *	filters:
	 *		stdDatum
	 *		restrictValue
	 *		daoHistory.
	 *
	 *	@param array &$datum
	 *		data to add. must be an array.
	 *		will be updated to the value stored to database.
	 *	@param array $func=array()
	 *		function to add if necessary. must be an array.
	 *	@param boolen $do_hist
	 *		filters with dao_history if TRUE.
	 *	@return boolen/string
	 *		returns id or TRUE on success, FALSE on fail.
	 *		value of inserted id is returned if data contains id value.
	 */
    function insert( &$datum, $func=array(), $do_hist=FALSE )
    {
        if( WORDY ) echo "<br><b>baseDAO::insert( $datum, $func )</b><br>\n";
		
		$this
			->stdDatum( $datum, 'insert' )
			->daoRestrictValue( 'insert' );
        DaoHelper::insert_datetime( $this->col_info, $datum );
		$this
			->clear()
			->values( $datum, $func )
			->table( $this->tableName )
			->execInsert();
		
		if( isset( $this->dao_history ) && $do_hist ) {
			$this->dao_history->logAdd( $datum );
		}
		$ret_val = TRUE;
        $id_name = $this->getIdName();
        if( isset( $datum[ $id_name ] ) ) {
			$ret_val = $datum[ $id_name ];
		}
		return $ret_val;
    }
	// +--------------------------------------------------------------- +
	/**	
	 *	inserts data with autonumbering id value with code defined by
	 *	$this->id_info. must set transactions.
	 *
	 *	@param array &$datum
	 *		data to add. must be an array.
	 *		will be updated to the value stored to database.
	 *	@param array $func=array()
	 *		function to add if necessary. must be an array.
	 *	@return string
	 *		returns id on success, FALSE on fail.
	 */
    function insert_code( &$datum, $func=array() )
    {
        if( WORDY ) echo "<br><b>baseDAO::insert_code( $datum )</b><br>\n";
        $id_name = $this->getIdName();
		$max     = DaoHelper::make_code( $this, $table, $this->id_info, $datum );
		if( $max !== FALSE ) {
			$datum[ $id_name ] = $max;
			return $this->insert( $datum, $func, TRUE );
		}
        return FALSE;
    }
	// +--------------------------------------------------------------- +
	/**	
	 *	inserts data with autonumbering id value as max number of id.
	 *	must set transactions.
	 *
	 *	@param array &$datum
	 *		data to add. must be an array.
	 *		will be updated to the value stored to database.
	 *	@param array $func=array()
	 *		function to add if necessary. must be an array.
	 *	@return string
	 *		returns id on success, FALSE on fail.
	 */
    function insert_max( &$datum, $func=array() )
    {
        if( WORDY ) echo "<br><b>baseDAO::insert_max( $datum )</b><br>\n";
        $id_name = $this->getIdName();
		$max     = DaoHelper::make_max( $this, $table, $id_name, $datum );
		if( $max !== FALSE ) {
			$datum[ $id_name ] = $max;
			return $this->insert( $datum, $func, TRUE );
		}
        return FALSE;
    }
	// +--------------------------------------------------------------- +
	/**	
	 *	inserts data with serialized autonumbering id.
	 *	no need of transaction.
	 *
	 *	@param array &$datum
	 *		data to add. must be an array.
	 *		will be updated to the value stored to database.
	 *	@param array $func=array()
	 *		function to add if necessary. must be an array.
	 *	@return string
	 *		returns id on success, FALSE on fail.
	 */
    function insert_id( &$datum, $func=array() )
    {
        if( WORDY ) echo "<br><b>baseDAO::insert_id( $datum )</b><br>\n";
		if( WORDY > 3 ) wordy_table( $datum, 'dao insert_id' );
		
        $id_name = $this->getIdName();
		if( isset( $datum[ $id_name ] ) ) unset( $datum[ $id_name ] );
        $this->insert( $datum, $func, FALSE );
        
		$last_id = $this->rdb->lastId();
		if( WORDY > 3 ) echo "->lastId returned: $last_id";
		$datum[ $id_name ] = $last_id;
		if( isset( $this->dao_history ) ) {
			$this->dao_history->logAdd( $datum );
		}
        return $last_id;
    }
	// +--------------------------------------------------------------- +
	/**	
	 *	selects data. specify conditions prior to use.
	 *	filters:
	 *		restrictWhere
	 *		stdDatum
	 *
	 *	@param array &$data
	 *		returns selected data (array of datum).
	 *	@returns integer
	 *		returns number of found data.
	 */
    function select( &$data )
    {
        if( WORDY ) echo "baseDAO: selectById( $id, $id_name )<br>\n";
		
		$this	
			->daoRestrictWhere( 'select' )
			->execSelect()
			->fetchAll( $data );
		;
		$this->stdData( $data, 'select' );
        return count( $data );
    }
	// +--------------------------------------------------------------- +
	/**	
	 *	same as select.
     *  @see select
	 */
    function read( &$data ) {
        return $this->select( $data );
    }
	// +--------------------------------------------------------------- +
	/**	
	 *	selects a datum (only one record) based on id (primary key).
	 *	a quick accessor for select method.
	 *
	 *	throws DaoNoDatumException if there are no data, or more than
	 *	one data exists, and if throwException is set to TRUE.
	 *
	 *	@param string $id
	 *		value of id (primary key)
	 *	@param string $id_name=""
	 *		specify name of column to use as conditions other than
	 *		primary key.
	 */
	function getDatum( $id, $id_name="" )
	{
		if( !have_value( $id_name ) ) {
			$id_name = $this->getIdName();
		}
		$this
			->clear()
			->table( $this->getTableName() )
			->setWhere()              // reset condition
			->where( $id_name, $id )  // set primary key
		;
		$num = $this->select( $data );
		if( $num == 1 ) {
			$datum = $data[0];
			return $datum;
		}
		if( $this->throwException ) {
			if( $num < 1 ) {
				throw new DaoNoDatumException( "Cannot get datum (id={$id})" );
			}
			else {
				throw new DaoMoreThanOneException( "more than one data (id={$id})" );
			}
		}
		return FALSE;
	}
	// +----------------------------------------------------------+
	/**	
	 *	finds data by id (primary key). can get multiple records.
	 *	another quick accessor for select.
	 *
	 *	@param string $id
	 *		value of id (primary key)
	 *	@param string $id_name=""
	 *		value of column for search, if not primary key.
	 *	@return array
	 *		returns found data.
	 */
	function findById( $id, $id_name="" ) {
		if( !have_value( $id_name ) ) {
			$id_name = $this->getIdName();
		}
        $this
			->where( $id_name, $id )  // set primary key
			->select( $data );
		;
		return $data;
	}
	// +----------------------------------------------------------+
	/**	
	 *	modify a datum (one record) based on id (primary key).
	 *	a quick accessor for update method.
	 *
	 *	@param string $id
	 *		update only the record of the $id.
	 *	@param array &$datum
	 *		data to update.
	 *	@param string $id_name
	 *		specify name of column to use as conditions other than
	 *		primary key.
	 *	@return boolen
	 *		returns TRUE on success, FALSE on fail.
	 */
	function modDatum( $id, &$datum, $id_name=NULL ) {
		if( !have_value( $id ) ) return FALSE;
		if( !have_value( $id_name ) ) {
			$id_name = $this->getIdName();
		}
		$this
			->clear()
			->where( $id_name, $id ); // set primary key
			
		return $this->update( $datum );
	}
	// +----------------------------------------------------------+
	/**	
		adds a datum (one record) to database. 
		a quick accessor for insert_id method. overwrite this 
		method to user other methods (insert_code or insert_max). 
		
		@param array &$datum
			data to update. 
		@return boolean
			returns id on success, FALSE on fail. 
	 */
	function addDatum( &$datum ) {
		$this->clear();
		return $this->insert_id( $datum );
	}
	// +----------------------------------------------------------+
	/**	
	 *	deletes a datum (one record) based on id (primary key).
	 *	may be quick accessor for removeDataFromTable, but this
	 *	method overwrite the behavior of data if col_info have
	 *	del_flag and del_value are set, it updates the data with
	 *	the specified column/value.
	 *
	 *	overwrite this method if necessary.
	 *
	 *	@param string $id
	 *		update only the record of the $id.
	 *	@return boolean
	 *		returns id on success, FALSE on fail.
	 */
	function delDatum( $id ) {
		$this->clear();
		if( have_value( $this->col_info[ 'del_flag'  ] ) && 
			have_value( $this->col_info[ 'del_value' ] ) ) {
			$data[ $this->del_flag ] = $this->del_value;
			return $this->update( $id, $data );
		}
		else {
			return $this->removeDataFromTable( $id );
		}
	}
    // +----------------------------------------------------------------------------+
	//
	//                      filters for update, insert, and select
	//
    // +----------------------------------------------------------------------------+

	// +----------------------------------------------------------+
	/** do the minimum sets up for history dao. 
	 *	need to work more on this...
	 *	@return $this
	 */
    function setupHistory() {
		$this->dao_history = dao_history::cloneInstance();
		$this->dao_history->setTable(  $this->tableName() );
		$this->dao_history->setIdName( $this->getIdName() );
		// more setups may be done in dao for each table.
		return $this;
    }
	// +----------------------------------------------------------+
	/**	
	 *	sets function for standarize data.
	 *
	 *	@param function $func
	 *		specify function to standarize.
	 *	@return $this
	 */
	function setStdDatum( $func ) {
		$this->stdDatumFunc = $func;
		return $this;
	}
	// +----------------------------------------------------------+
	/**	
	 *	standarize data when accessing database.
	 *	calls stdDatumFunc set by setStdDatum method.
	 *
	 *	@param array &$datum
	 *		data to standarize.
	 *	@param string $method
	 *		method name: update, insert, or select
	 *	@return $this
	 */
	function stdDatum( & $datum, $method='' ) {
		if( $this->stdDatumFunc ) {
			$func = $this->stdDatumFunc;
			$func( $datum, $method );
		}
		return $this;
	}
	// +----------------------------------------------------------+
	/**	
	 *	standarize data (array of datum) using stdDatum method.
	 *
	 *	@param array &$data
	 *		array of datum to standarize. call by reference.
	 *	@param string $method
	 *		how stdDatum is accessed. usually update, insert, select, etc.
	 *	@return $this
	 */
	function stdData( &$data, $method='' ) {
		foreach( $data as &$datum ) {
			$this->stdDatum( $datum, $method );
		}
		return $this;
	}
    // +----------------------------------------------------------------------------+
	//
	//                           methods requires Model
	//
    // +----------------------------------------------------------------------------+

	// +----------------------------------------------------------+
	/**	
	 *	restrict access by adding conditions in SQL statement.
	 *	requires Model to be set, and have restrictWhere
	 *	method returning array of conditions.
	 *	array(
	 *		array( 'col'=>'column', 'val'=>'value', 'rel'=>'=' ),
	 *	);
	 *
	 *	@param string $crud
	 *		specify which function this filter is called.
	 *		select, update, or delete.
	 *	@return $this
	 */
	function daoRestrictWhere( $crud=NULL ) 
	{
		if( !have_value( $this->_table ) ) return $this;
		$table = $this->_table;
		
		if( method_exists( $table, 'restrictAccess' ) )  // restrict access.
		{
			if( $restrict = $table::restrictWhere( $crud ) )
			foreach( $restrict as $cond ) {
				$this->where( $cond['col'], $cond['val'], $cond['rel'], 'AND' );
				if( WORDY > 3 ) echo "restrictWhere: $col $rel $val<br>";
			}
		}
		return $this;
	}
	// +----------------------------------------------------------+
	/**	
	 *	restrict saved data to have specific values.
	 *	requires Model to be set, and have restrictValue
	 *	method returning array of conditions.
	 *
	 *	array(
	 *		array( 'col'=>'column', 'val'=>'value' ),
	 *	);
	 *	@param string $crud
	 *		specify which function this filter is called.
	 *		insert, update, or delete.
	 *	@return $this
	 */
	function daoRestrictValue( $crud=NULL ) 
	{
		if( !have_value( $this->_table ) ) return $this;
		$table = $this->_table;
		
		if( method_exists( $table, 'restrictAccess' ) )  // restrict access.
		{
			if( $data = $table::restrictValue( $crud ) )
			foreach( $data as $value ) {
				$this->addVal( $value['col'], $value['val'] );
				if( WORDY > 3 ) echo "restrictValue: $col => $val<br>";
			}
		}
		return $this;
	}
	// +----------------------------------------------------------+
	/**	
	 *	fetches array of Record objects.
	 *	use instead of fetchAll.
	 *
	 *	@param array &$records
	 *		returns an array of Record objects.
	 *	@return $this
	 */
    function fetchRecords( &$records ) 
	{
		if( !have_value( $this->_table ) ) return $this;
        if( WORDY > 1 ) echo "<br><i>formSQL::fetchRecords( &\$records )...</i><br>\n";
		
		$table = $this->_table;
		$this->select( $data );
		$records = $table::convertRecords( $data, Record::TYPE_GET );
		return $this;
    }
	// +----------------------------------------------------------+
	/**	
	 *	fetches array of Cena_Record objects.
	 *	use instead of fetchAll.
	 *
	 *	@param array &$cenas
	 *		returns an array of Cena_Record objects.
	 *	@return $this
	 */
    function fetchCena( &$cenas ) 
	{
		if( !have_value( $this->_table ) ) return $this;
        if( WORDY > 1 ) echo "<br><i>formSQL::fetchCena( &\$records )...</i><br>\n";
		
		$table = $this->_table;
		$this->select( $data );
		$records = $table::convertRecords( $data );
		$cenas   = Cena::getCenaByRec( $records );
		return $this;
    }
	// +----------------------------------------------------------+
	/**	
	 *	returns HTML element for var_name (property, column etc.).
	 *	a quick access/backward compatibility method.
	 */
    function popHtml( $var_name, $html_type, $td, $err=array() )
	{
		if( !have_value( $this->_table ) ) return FALSE;
		$table = $this->_table;
		return $table::popHtml( $var_name, $html_type, $td, $err );
    }
	// +----------------------------------------------------------+
	/**	
	 *	returns HTML element for var_name (property, column etc.).
	 *	a quick access/backward compatibility method.
	 */
    function check_input( &$pgg )
	{
		if( !have_value( $this->_table ) ) return FALSE;
		$table = $this->_table;
		return $table::check_input( $pgg );
    }
    // +----------------------------------------------------------------------------+
}

?>