<?php
namespace CenaDta\Dba;
/**
 *	Model for dba.
 *
 *	@copyright     Copyright 2010-2011, Asao Kamei
 *	@link          http://www.workspot.jp/cena/
 *	@license       GPLv2
 */
require_once( 'Dao.php' );

class DbaTableException           extends \Exception {}
class DbaTable_NoDatumException   extends DbaTableException {}

/* =================================================================================== *
Model
parent class for all tables. All of the methods are static!
 * =================================================================================== */
/**
 * Model class
 * parent class for dao's model.
 * this class and derived classes are used as static class; all the
 * methods and variables are declared as static. 
 *
 *	@author Asao Kamei, WorkSpot.JP
 *	@since PHP5.3
 *	@version 0.3
 */
class Model
{
    static $dba_record     = 'CenaDta\Dba\Record';
	static $config_name    = NULL;      // sets Pdo's config file name. 
	static $stdDatumFunc   = FALSE;     // a closure/function used in stdDatum
	static $useJoinedTable = FALSE;     // to use joined table when select
	
	static $dao_table      = FALSE;     // name db table.
	static $id_name        = FALSE;     // name of id (primary key). 
	static $id_info        = array();   // for old style id info. 
	
	static $col_names  = array();
	static $col_sels   = array();
	static $col_checks = array();
	static $col_info   = array(
		'new_date'     => FALSE,
		'new_time'     => FALSE,
		'new_datetime' => FALSE,
		'mod_date'     => FALSE,
		'mod_time'     => FALSE,
		'mod_datetime' => FALSE,
		'del_flag'     => FALSE,
		'del_value'    => FALSE,
	);

	static $throwException = TRUE;         // throws exception or not.
	static $dba_dao_name   = 'CenaDta\Dba\Dao';    // name of dao for accessing db. 
	static $restrictAccess = FALSE;        // use restriction on access.
	
	// +--------------------------------------------------------------- +
	private function __construct() {
		throw new DbaTableException( 'cannot instanciate ' . __CLASS__ );
	}
	// +--------------------------------------------------------------- +
	function __toString() {
		$class = get_called_class();
		return "Model(#" . spl_object_hash( $this ).  ")::{$class} ";
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	setup definitions for columns in this method.
	 */
	static function setupColumns() {
	}
	// +--------------------------------------------------------------- +
	/**	sets flag to throw exception or not. 
	 *	the default value flag is FALSE (not to throw exception).
	 *
	 *	@param boolen $throw
	 *		throws exception if set to TRUE.
	 *		set to FALSE suppress exception i.e. throwException( FALSE ).
	 */
	static function throwException( $throw=NULL ) {
		if( is_bool( $throw ) ) {
			self::$throwException = $throw;
		}
		return self::$throwException;
	}
	// +--------------------------------------------------------------- +
	/**	singleton the DAO object. 
	 *	Note that this method returns an instance of Dao object,
	 *	not the Model, which is not supposed to be instanced.
	 *
     *  @param string $config
     *      name of configuration file for Pdo.
     *      set static::$config_name to model specify config file.
	 *	@return object
	 *		returns singleton object.
	 */
	static function getInstance( $config=NULL )
	{
		$class = get_called_class();
		if( $dao = DaoObjPools::getInstance( $class ) ) { // found!
			$dao->setTableClass( $class );
			return $dao;
		}
        if( is_callable( array( $class, '_init' ) ) ) {
            $class::_init();
        }
		if( !have_value( $config ) && have_value( $class::$config_name ) ) {
			$config = $class::$config_name; 
		}
		$dao_name = static::$dba_dao_name;
		$dao = new $dao_name( $config );
		$dao->setTableClass( $class );
		DaoObjPools::setInstance( $class, $dao );
		$class::setupColumns();
		return $dao;
    }
	// +--------------------------------------------------------------- +
	/**	
	 *	a quick access to static::$dao. if not initialized, call
	 *	static::getDaoInstance() to obtain dao and set to static::$dao.
	 *	note that no argument is given to getDaoInstance.
	 *
	 *	@return object
	 *		Dao object.
	 */
	static function dao() {
		return static::getInstance();
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	get table name (static::$dao_table). always returns table.
	 *
	 *	@return string
	 *		table name.
	 */
    static function tableName() {
		if( !have_value( static::$dao_table ) ) {
			throw new DbaTableException( "No table name specified" );
		}
		return static::$dao_table;
    }
	// +--------------------------------------------------------------- +
	/**	
	 *	get table name for SQL statement. depending on the flag, this
	 *	may return real db table name, or joined table name.
	 *
	 *	@return string
	 *		table name.
	 */
    static function getTableName() {
		return static::$dao_table;
    }
	// +--------------------------------------------------------------- +
	/** get id name (static:$dao_pkey), primary key of the table. 
	 *	@return string
	 *		id name (primary key name).
	 */
    static function idName() {
		if( !have_value( static::$dao_pkey ) ) {
			throw new DbaTableException( "No ID specified" );
		}
		return static::$dao_pkey;
    }
	// +--------------------------------------------------------------- +
	/** get col_info of the table. 
	 *	@return array
	 *		column info.
	 */
    static function getColInfo() {
		return static::$col_info;
    }
	// +--------------------------------------------------------------- +
	/**	
	 *	make joined table based on static::$join_table or argument.
	 *
	 *	@param string $join
	 *		join notations.
	 *	@return string
	 *		SQL portion of joined table.
	 */
    static function makeJoinedTable( $join=FALSE )
    {
		if( have_value( $join ) ) { // join condition is set. 
			$join_list = $join; 
		}
		else { // use the one defined in each dao. 
			$join_list = static::$join_table; 
		}
		$table = static::tableName();
		return DaoHelper::getJoinedTable( $table, $join_list );
    }
    // +----------------------------------------------------------------------------+
    //
	//             basic data manipulation: {get|mod|add|del}Datum
	//
	// +----------------------------------------------------------------------------+
	
	// +--------------------------------------------------------------- +
	static function getDatum( $id, $id_name="" ) {
		return static::dao()->getDatum( $id, $id_name );
	}
	// +--------------------------------------------------------------- +
	/** modify a datum for given id. alternatively, 
	 *	can specify which column's value using id_name.
	 *
	 *	@param string $id
	 *		update only the record of the $id.
	 *	@param array &$data
	 *		data to update.
	 *	@param string $id_name
	 *		to specify column for the $id if not id_name (primary key).
	 *	@return boolen
	 *		returns TRUE on success, FALSE on fail.
	 */
	static function modDatum( $id, &$data, $id_name=NULL ) {
		return static::dao()->modDatum( $id, $data, $id_name );
	}
	// +--------------------------------------------------------------- +
	static function addDatum( &$data ) {
		return static::dao()->addDatum( $data );
	}
	// +--------------------------------------------------------------- +
	static function delDatum( $id ) {
		return static::dao()->delDatum( $id );
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	restrict data access by adding conditions in query's where.
	 *	return restricted value based on $crud type.
	 *	overwrite this method to restrict access on data.
	 *
	 *	@param string $crud
	 *		type of operation: insert, update, delete, select.
	 *	@return array
	 *		return restricted value as
	 *		array(
	 *	 		array( 'col' => 'column', 'val' = 'value', 'rel' = '=' ),
	 *		);
	 */
	static function restrictWhere( $crud ) {
		return FALSE;
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	restrict data by adding extra column/value in query's values.
	 *	return restricted value based on $crud type.
	 *	overwrite this method to restrict data value.
	 *
	 *	@param string $crud
	 *		type of operation: insert, update, delete, select.
	 *	@return array
	 *		return restricted value as
	 *		array(
	 *			array( 'col' => 'column', 'val' => 'value' ),
	 *		);
	 */
	static function restrictValue( $crud ) {
		return FALSE;
	}
    // +----------------------------------------------------------------------------+
	//
	//                        selectors for building forms
	//
    // +----------------------------------------------------------------------------+
	
	// +--------------------------------------------------------------- +
	/** get column name for variable name. uses $class::$col_names.
	 *	@param string $var_name
	 *		name of variable (i.e. contact_name) to get column name
	 *	@return string
	 *		returns column name such as "contact's name".
	 */
	static function getColumn( $var_name=FALSE )
	{
		if( !isset( static::$col_names ) ) {
			static::setColumns();
		}
		if( !isset( static::$col_names ) ) 
			throw new DbaTableException( 'col_names not set:' . get_called_class() );
		
		if( have_value( $var_name ) ) {
			if( isset( static::$col_names[ $var_name ] ) ) {
				return static::$col_names[ $var_name ];
			}
			return FALSE;
		}
		else 
			return static::$col_names;
	}
	// +--------------------------------------------------------------- +
	static function checkInput( $pgg, $var_name )
	{
		if( !isset( static::$col_checks ) ) {
			static::setColumns();
		}
		if( !isset( static::$col_checks ) ) 
			throw new DbaTableException( 'col_checks not set:' . get_called_class() );
		
		return DaoObjPools::checkInput( $pgg, $var_name, static::$col_checks );
	}
	// +--------------------------------------------------------------- +
	static function getSelector( $var_name )
	{
		if( !isset( static::$col_sels ) ) {
			static::setColumns();
		}
		if( !isset( static::$col_sels ) ) 
			throw new DbaTableException( 'col_sels not set:' . get_called_class() );
		
		return DaoObjPools::getSelector( $var_name, static::$col_sels );
	}
	// +--------------------------------------------------------------- +
	static function getSelInstance( $var_name )
	{
		$class   = get_called_class();
		if( $elem = DaoObjPools::getElement( $class, $var_name ) ) {
			return $elem; 
		}
		$elem = self::getSelector( $var_name );
		DaoObjPools::setElement( $class, $var_name, $elem );
		return $elem;
	}
	// +--------------------------------------------------------------- +
	static function popHtml( $var_name, $html_type, $td, $err=array() )
	{
		$sel = self::getSelInstance( $var_name );
		if( method_exists( $sel, 'popHtml' ) ) {
			return $sel->popHtml( $html_type, $td, $err );
		}
		if( WORDY ) echo "popHtml( $var_name, $html_type, $td, $err )::"
			 . " <font color=red>method 'popHtml' not exists!</font><br>";
	}
	// +--------------------------------------------------------------- +
	static function disp_html( $html_type, $td, $err=array() )
	{
		$class = get_called_class();
		return DaoHelper::disp_html( $class, $html_type, $td, $err );
	}
	// +--------------------------------------------------------------- +
	static function disp_html_row( $html_type, $data, $err=array() )
	{
		$class = get_called_class();
		return DaoHelper::disp_html_row( $class, $html_type, $td, $err );
	}
    // +----------------------------------------------------------------------------+
	//
	//                      methods for Active Record and Cena
	//
    // +----------------------------------------------------------------------------+

	// +----------------------------------------------------------+
    /** 
     *  returns relation information for a table.
     *  returns information in following format.
     *  array(
     *      'column_name' => array(
     * 			'rec_model'  => 'dao_contact110',
     * 			'rec_column' => 'contact_id',
     * 			'rel_model'  => 'dao_contact100',
     * 			'rel_column' => NULL
     *      ), ....
     *  );
     *
     *	@return array
     *      returns relation information of table. 
     */
	static function getRelationship() {
		return FALSE;
	}
	// +----------------------------------------------------------+
    /** getChildship()
     *
     *	@return array
     */
	static function getChildship() {
		return FALSE;
	}
	// +----------------------------------------------------------+
    /** 
	 *	returns child record for given $column name and its $value.
	 *	uses relationship data to determine the children. 
     *	
	 *	@param string $column 
	 *		column name of relation/children. 
	 *	@param string $value
	 *		value of the column. usually an id. 
     *	@return array
	 *		returns array of Record.
     */
	static function getChildren( $column, $value ) {
		$model = get_called_class();
		$relationship = $model::getRelationship();
		if( empty( $relationship ) ) return array();
		if( !isset( $relationship[ $column ] ) ) return $array();
		
		$rule = $relationship[ $column ];
		$child_column = $rule[ 'rec_column' ];
		$model::getInstance()
			->clear()
			->where( $child_column, $value )
			->fetchRecords( $records )
		;
		return $records;
	}
	// +----------------------------------------------------------+
	/** returns Record object given data. 
	 *	use this method if you already have data read from db,
	 *	and want to create Record.
	 *	uses Record->setUpByData.
	 *
	 *	@param	array	$data
	 *		record data
	 *	@param string $type=Record::TYPE_GET
	 *	@return object
	 *		returns Record object.
	 */
	static function convertDataToRecord( $data, $type=Record::TYPE_GET )
	{
		$model  = get_called_class();
		$record = $model::$dba_record;
		return $record::getRecord( $model, $type, $data );
	}
	// +----------------------------------------------------------+
	/** returns an array of Record objects given an array of 
	 *	data records. wrapper for convertDataToRecord method.
     *
	 *	@param array $data
	 *		array of record data
	 *	@returns array
	 *		array of Record objects.
	 */
	static function & convertRecords( &$data, $type=NULL )
	{
		$records = array();
		if( !empty( $data ) ) {
			foreach( $data as $ddd ) {
				$records[] = self::convertDataToRecord( $ddd );
			}
		}
		return $records;
	}
	// +----------------------------------------------------------+
	/** generate Record objects. 
	 *	object pool for all Dao/Record objects.
	 *
	 *	getRecord( $type, $data )
	 *		if $type=='NEW' then
	 *			if $data is set data is an new id
	 *			if $data is empty create new id
	 *		if $type=='GET' then
	 *			if $data is array then data is a data
	 *			if $data is string then data is an id
	 *
	 *	@param string $type
	 *		type of record: GET or NEW
	 *	@param string $id
	 *		id (primary key)
	 *	@return object
	 *		returns Record object.
	 */
	static function getRecord( $type, $id )
	{
		$model  = get_called_class();
		$record = $model::$dba_record;
        return $record::getRecord( $model, $type, $id );
	}
    // +--------------------------------------------------------------- +
	/**	
	 *	generates new Record object.
	 *	a quick access to getRecord( NEW, $data ) method.
	 *
	 *	@param array $data
	 *		set data of new record if any.
	 *	@return object
	 *		returns Record object.
	 */
	static function getNewRecord( $data=array() )
	{
		$model  = get_called_class();
		$record = $model::$dba_record;
		return $record::getRecord( $model, $record::TYPE_NEW, $data );
	}
    // +----------------------------------------------------------------------------+
}


?>