<?php
namespace CenaDta\Dba;
use CenaDta\Html as html;
// Record class
// ActiveRecord implementation.
// charset: utf-8, 日本語

require_once( 'Model.php' );
class DbaRecord_Exception extends \Exception {}
class DataInvalid_DbaRecord_Exception extends DbaRecord_Exception {}
class NoIdValue_DbaRecord_Exception extends DbaRecord_Exception {}

/** Record
 *  an active-record implementation. requires $model of Dao.
 *  use getRecord to pool object. 
 */
class Record
{
	const TYPE_NEW = 'new';
	const TYPE_GET = 'get';
	const TYPE_IGNORE = 'ignore-record'; // when ignoring record for new
	
	const EXEC_NONE = 'exec-none';
	const EXEC_SAVE = 'exec-save';
	const EXEC_DEL  = 'exec-delete';
	
	static $links  = array(); // for late linking
	static $new_id = 1;       // an id for type_new
	static $new_wd = 'new #'; // an word for new id
	
    /**
     * specifies name of Model class for accessing db.
     * @var <stringe>
     */
	var $model     = NULL; // i.e. dao name (Model...)
	var $id        = NULL;
	var $id_name   = NULL;
	var $type      = self::TYPE_NEW;
	
	var $data         = array(); // data of the record
	var $input_data   = array(); // input data from form.
	var $relations    = array(); // related records
	var $children     = array(); // child records.
	var $input_method = 'check_input';
	
	var $err_msg   = array(); // error message after validate
	var $err_num   = FALSE;
	var $validated = FALSE; // TRUE only if validation is OK
	var $execute   = self::EXEC_NONE; // object's final behavior
	
    // +----------------------------------------------------------------------------+
    //
	//                         for late linking/relations
	//
	// +----------------------------------------------------------------------------+
	
	// +--------------------------------------------------------------- +
	/**	sets request for late linking.
	 *	@param object $rec
	 *		Record object to link.
	 *	@param object $rel
	 *		Record object that link to. 
	 *	@param string $method
	 *		method name used to link, i.e. $rec->$method( $rel );
	 */
	private static function linkSet( $rec, $obj, $method, $column=NULL )
	{
		if( WORDY > 3 ) 
			echo "Record::linkSet( $rec, $obj, $method, $column )<br />\n";
		self::$links[] = array( $rec, $obj, $method, $column );
	}
	// +--------------------------------------------------------------- +
	/**	perform the late linking. 
	 */
	static function linkNow()
	{
		if( !empty( self::$links ) ) 
		foreach( self::$links as $link ) {
			$rec    = $link[0];
			$obj    = $link[1];
			$method = $link[2];
			$column = $link[3];
			$rec->$method( $column, $obj );
		}
	}
    // +----------------------------------------------------------------------------+
    //
	//                           construction of records
	//
	// +----------------------------------------------------------------------------+
	
	// +--------------------------------------------------------------- +
	/** constructor for Record
	 *	@param object $dao
	 *		Dao object of the record.
	 *	@param string $id
	 *		primary key of the record.
	 *		if $id is given, constructor initializes itself with the key.
	 *	@param string $type
	 *		type of the data: new or get, currently.
	 */
	function __construct( $model, $id=NULL, $type=self::TYPE_GET )
	{
		$this->setModel( $model );
		if( have_value( $id ) ) $this->initRecord( $id, $type );
	}
	// +--------------------------------------------------------------- +
	function __toString() {
		return "Record(#" . spl_object_hash( $this ).
            ")::model={$this->model}, type={$this->type}, id={$this->id} ";
	}
	// +--------------------------------------------------------------- +
    /** getRecord( $model, $type, $data )
     *  gets Record instance for the argument. records are from
     *  object pool; creates new record only if the record is not
     *  in the pool.
     *
     *  @param string> $model
     *      name of the model (dao).
     *  @param string $type
     *      name of type (usually get or new).
     *  @param string/array $data
     *      value of id (primary key) if string.
     *      data containing id value. 
     *  @return Record
     */
	function getRecord( $model, $type, $data )
	{
		if( is_array( $data ) ) { // it's a data. get id.
			$id_name = $model::idName();
            if( !have_value( $data[ $id_name ] ) ) {
                if( $type === Record::TYPE_NEW ) {
                    $data[ $id_name ] = Record::getNewId();
                }
                else {
                    throw new NoIdValue_DbaRecord_Exception( "No id" );
                }
            }
			$id      = $data[ $id_name ];
		}
		else { // it's an id. 
			$id      = $data;
		}
		$rec = DaoObjPools::getRecord( $model, $type, $id );
		if( $rec ) { // found record in object pool. 
			return $rec;
		}
		// create new instance. 
		$rec = new Record( $model );
		$rec->initRecord( $data, $type );
		DaoObjPools::setRecord( $model, $type, $id, $rec );
		return $rec;
	}
	// +--------------------------------------------------------------- +
	/** sets Dao for the record.
	 *	@param object $dao
	 *		Dao object.
	 *		if it is a string, treated as dao's class name.
	 *	@returns $this
	 */
	function setModel( $model )
	{
		if( WORDY ) echo "setModel( $model )<br />\n";
		if( is_object( $model ) ) {
            // must be a Dao name. get model name.
            $model        = $model->_table;
			$this->model  = $model;
            if( !have_value( $model ) )
                throw new DbaRecord_Exception( 'setModel: no model name' );
        }
        $this->model   = $model;
		$this->id_name = $model::idName();
		$this->resetValidated();
		return $this;
	}
	// +--------------------------------------------------------------- +
	/** initializes Record. 
	 *	call this function after dao is specified.
	 *
	 *	@param string $type
	 *		specify type of record: TYPE_GET or TYPE_NEW.
	 *	@param string/array $data
	 *		string: id (primary key) value of record
	 *		array : data of record
	 *	@return $this
	 */
	function initRecord( $data, $type=self::TYPE_GET )
	{
		if( WORDY ) echo "<strong>initRecord( $data, $type )</strong><br />\n";
		if( $type == self::TYPE_GET ) { // record for existing data. 
			if( !is_array( $data ) ) {
				// $id is an id (primary key). 
				// get data from model::getDatum($id).
				$data = call_user_func( array( $this->model, 'getDatum' ), $data );
			}
		}
		else
		if( $type == self::TYPE_NEW ) {  // record for new data. 
			if( !have_value( $data ) ) { // no argument. create new id.
				$data = array( $this->id_name => self::getNewId() );
			}
			else
			if( !is_array( $data ) ) { // it's a new id.
				$data = array( $this->id_name => $data );
			}
			else { // it's a data
				if( !isset( $data[ $this->id_name ] ) ) { 
					// need a new id. 
					$data[ $this->id_name ] = self::getNewId();
				}
			}
			// for new record, use data as input data. 
			$this->input_data = $data;
		}
		else {
			throw new DataInvalid_DbaRecord_Exception( 'wrong type:' . $type );
		}
		// setup this record with data. 
		$this->data = $data;
		$this->id   = $data[ $this->id_name ];
		$this->type = $type;
		$this->resetValidated();
		
		return $this;
	}
	// +--------------------------------------------------------------- +
	/** initializes Record as new reocrd. 
	 *	a quick access to initRecord method.
     * 
	 *	@param array/string $data
	 *		string: id (primary key) value of record
	 *		array : data of record
	 */
	function initNewRecord( $data=FALSE ) 
	{
		if( WORDY ) echo "initNewRecord( $data )<br />\n";
		return $this->initRecord( $data, self::TYPE_NEW );
	}
	// +--------------------------------------------------------------- +
	/** getNewId()
     *  gets new id automatically.
     *
	 *	@return id
	 *		returns new id.
	 */
	function getNewId() 
	{
		return self::$new_wd . self::$new_id ++;
	}
	// +--------------------------------------------------------------- +
	/**	markSaveRecord()
	 *	set execute flag to EXEC_SAVE if execute flag is EXEC_NONE.
	 *	i.e. this does not change the flag if set to EXEC_DEL.
	 */
	function markSaveRecord() {
		if( $this->execute == self::EXEC_NONE ) {
			$this->execute = self::EXEC_SAVE;
		}
	}
	// +----------------------------------------------------------------------------+
	//
	//                             manipulating record
	//
	// +----------------------------------------------------------------------------+
	
	// +--------------------------------------------------------------- +
	/**	get( $name=FALSE )
	 *	quick access method to getProperty.
	 */
	function get( $name=FALSE ) {
		return $this->getProperty( $name );
	}
	// +--------------------------------------------------------------- +
	/**	set( $data, $val=FALSE )
	 *	quick access method to setProperty.
	 */
	function set( $data, $val=FALSE ) {
		return $this->setProperty( $data, $val );
	}
	// +--------------------------------------------------------------- +
	/**	rel( $data, $val=FALSE )
	 *	quick access method to setRelation.
	 *	may be deleted...
	 */
	function rel( $data, $val=FALSE ) {
		return $this->setRelation( $data, $val );
	}
	// +--------------------------------------------------------------- +
	/**	del( $data=FALSE )
	 *	quick access method to delRecord.
	 *	may be deleted...
	 */
	function del( $data=FALSE ) {
		return $this->delRecord( $data );
	}
	// +--------------------------------------------------------------- +
	/**	delRecord( $data=FALSE, $val=FALSE )
	 *	delete current record by setting execute to EXEC_DEL.
	 *	will be deleted when saved to database.
	 *
	 *	@param mixed $data
	 *		dummy argument.
	 *	@param mixed $data
	 *		dummy argument.
	 *	@return $this
	 */
	function delRecord( $data=FALSE, $val=FALSE ) {
		$this->execute = self::EXEC_DEL;
		if( WORDY > 1 ) echo "setDel() => {$this->execute}<br>";
		return $this;
	}
	// +--------------------------------------------------------------- +
	/**	getProperty( $name=FALSE )
	 *	get data (property) of the record.
     * 
	 *	@param string $name
	 *		if $name is given, returns the value of the variable name.
	 *		if not, returns all the data of the record as array.
	 *	@returns mix
	 *		returns value of the variable name, or array.
	 */ 
	function getProperty( $name=FALSE )
    {
        $data = array_merge( $this->data, $this->input_data );
		if( $name === FALSE ) return $data;
		if( isset( $data[ $name ] ) ) return $data[ $name ];
		return FALSE;
	}
	// +--------------------------------------------------------------- +
	/**	setProperty( $data, $val=FALSE )
	 *	sets property for the record.
	 *
	 *	@param mix $data
	 *		if $data is array, merge the $data to input data.
	 *		if not, $data is uses as a variable name.
	 *	@param string $val
	 *		if $data is a string, $val is a value of the variable.
	 *	@returns $this
	 */ 
	function setProperty( $data, $val=FALSE ) 
	{
		if( is_array( $data ) ) {
			$this->input_data = array_merge( $this->input_data, $data );
		}
		else {
			$this->input_data[ $data ] = $val;
		}
		$this->markSaveRecord();
		$this->resetValidated();
		return $this;
	}
	// +--------------------------------------------------------------- +
	/**	loadRelation()
	 *	loads relations based on db data. uses dao's getRelationship
	 *	method for defining relations. dao's getRelationship shall
	 *	return an array containing
	 *	array(
	 *		'rec_model'  => this record's model name. not used
	 *		'rec_column' => column name of the relationship.
	 *		'rel_model'  => related record's model name.
	 *		'rel_column' => related record's column name. if omitted, id is used.
	 *	);
	 *	this method can be used for TYPE_GET, probably...
	 *
	 *	@returns $this
	 */
	function loadRelation() 
	{
		// create relations
		if( method_exists( $this->model, 'getRelationship' ) )
		{
			$model = $this->model;
			$relationship = $model::getRelationship();
			if( !empty( $relationship ) )
			foreach( $relationship as $rule ) 
			{
				$column = $rule[ 'rec_column' ];
				$id_rel = $this->get( $column );
				if( have_value( $id_rel ) ) { // relation is set. 
					$model  = $rule[ 'rel_model' ];
					$rec    = $model::getRecord( Record::TYPE_GET, $id_rel );
					$this->relations[ $column ] = $rec;
				}
			}
		}
		return $this;
	}
	// +--------------------------------------------------------------- +
	/**	getRelation( $name=FALSE )
	 *	get related objects as specified by $name. if $name is omitted,
	 *	return all the related objects.
	 *
	 *	@param string $name
	 *		name of relation
	 *	@return objects/array
	 *		return found related objects.
	 *		if $name is omitted, returns array of related objects.
	 */
	function getRelation( $name=FALSE ) {
		if( WORDY > 3 ) wtc( $this->relations, 'Record::getRelation' );
		if( $name === FALSE ) return $this->relations;
		if( isset( $this->relations[ $name ] ) ) return $this->relations[ $name ];
		return FALSE;
	}
	// +--------------------------------------------------------------- +
	/**	setRelation( $data, $val=FALSE )
	 *	sets relation for the record.
	 *
	 *	TODO: if only Record given, get column by relation rule.
	 *
	 *	@param string/array $data
	 *		$data is column name if $val is Record object.
	 *		or, $data is an array{ 'column_name' => $record_object }.
	 *	@param object $val
	 *		$val is Record object that relates to this record.
	 *	@returns $this
	 */ 
	// +--------------------------------------------------------------- +
	function setRelation( $data, $val=FALSE ) {
		if( is_array( $data ) ) {
			foreach( $data as $col => $rec ) {
				$this->relations[ $col ] = $rec;
			}
		}
		else 
		if( have_value( $val ) ) {
			$this->relations[ $data ] = $val;
		}
		$this->markSaveRecord();
		if( WORDY > 3 ) wtc( $this->relations, "Record::setRelation model:{$this->model}, id:{$this->id}" );
		return $this;
	}
	// +--------------------------------------------------------------- +
	/**	loadChildren()
	 *	loads children objects based on db data. uses dao's getChildship
	 *	method for defining children. dao's getChildship shall return
	 *	an array containing
	 *	array(
	 *		'rec_model'  => this record's model name. not used
	 *		'rec_column' => column name to idenity children. if omitted, id is used.
	 *		'rel_model'  => child record's model name.
	 *		'rel_column' => child record's column name.
	 *	);
	 */
	function loadChildren() {
		if( method_exists( $this->model, 'getChildship' ) )
		{
			$model = $this->model;
			$child = $model::getChildship();
			if( !empty( $child ) )
			foreach( $child as $rule ) 
			{
				$child_model  = $rule[ 'child_model' ];
				$child_column = $rule[ 'child_column' ];
				$column       = $rule[ 'rec_column' ];
				if( have_value( $column ) ) {
					$id_rec       = $this->get( $column );
				}
				else {
					$id_rec       = $this->getId();
				}
				$records = $child_model::getChildren( $child_column, $id_rec );
				if( have_value( $records ) ) {
					$this->children[ $child_model ] = $records;
				}
			}
		}
		return $this;
	}
	// +--------------------------------------------------------------- +
	/**	getChildren( $model=FALSE )
	 *	get child objects as specified by $model. if $model is omitted,
	 *	return all the child objects.
	 *
	 *	@param string $model
	 *		model name of children
	 *	@return objects/array
	 *		return found child objects.
	 *		if $name is omitted, returns array of child objects.
	 */
	function getChildren( $model=FALSE ) {
		if( $model === FALSE ) return $this->children;
		if( isset( $this->children[ $model ] ) ) return $this->children[ $model ];
		return array();
	}
	// +--------------------------------------------------------------- +
	/**	setChildren( $model_name, $val=FALSE )
	 *	sets children for the record. specify the model name of the
	 *	children so that parent record can tell which child to store.
	 *
	 *	TODO: if only Record is given, get model name by getModel().
	 *
	 *	@param string/array $data
	 *		$data is column name if $val is Record object.
	 *		or, $data is an array{ 'column_name' => $record_object }.
	 *	@param object $val
	 *		$val is Record object that relates to this record.
	 *	@returns $this
	 */ 
	// +--------------------------------------------------------------- +
	function setChildren( $model_name, $val=FALSE ) {
		if( is_array( $model_name ) ) {
			foreach( $model_name as $model => $child ) {
				$this->children[ $model_name ] = $child;
				$this->setChildRelation( $model_name, $child );
			}
		}
		else 
		if( have_value( $val ) ) {
			$this->children[ $model_name ] = $val;
			$this->setChildRelation( $model_name, $val );
		}
		$this->markSaveRecord();
		return $this;
	}
	// +--------------------------------------------------------------- +
	/**	setChildRelation( $model_name, $child )
	 *	sets child Record to relate to this record ($this)
	 *	when new relation is set.
	 *
	 *	@param string $model_name
	 *		name of the model of children
	 *	@param object $child
	 *		child Record object to set relation
	 */
	function setChildRelation( $model_name, $child ) {
		if( method_exists( $this->model, 'getChildship' ) ) {
			$model = $this->model;
			$childship = $model::getChildship();
			$column = $childship[ $model_name ][ 'child_column' ];
			$child->setRelation( $column, $this );
			return $this;
		}
	}
	// +----------------------------------------------------------------------------+
	//
	//                             Perform Record Action
	//
	// +----------------------------------------------------------------------------+
	
	// +--------------------------------------------------------------- +
	function setInputMethod( $method )
	{
		if( !method_exists( $this->model, $method ) ) {
			throw new ActRecException( "method {$check_input} does not exist" );
		}
		$this->input_method = $method;
		return $this;
	}
	// +--------------------------------------------------------------- +
	function doAction()
	{
		$debug = 2;
		$this->doValidate();
		if( WORDY > $debug ) 
			wtc( $this, "input_data in " . $this );
        $model = $this->model;
		if( $this->execute == self::EXEC_SAVE )       // updating data. 
		{
			if( !empty( $this->relations ) ) {
				// relate to other records by adding input_data
				if( WORDY > 5 ) wt( $this->relations, 'this is relations' );
				foreach( $this->relations as $col => $rel ) {
					if( WORDY > 5 ) wt( $rel, 'calling doRelate for '.$col );
					$this->doRelate( $col, $rel, $this->input_data );
				}
			}
			if( empty( $this->input_data ) ) { // no data to modify.
				// it's possible. like, setRelation for new record. 
				// do nothing. 
			}
			else
			if( $this->type == self::TYPE_NEW ) {
				$this->id = $model::addDatum( $this->input_data );
				// added to record, i.e. is get type now.
				$this->initRecord( $this->id );
				$this->input_data = array();
				if( WORDY ) wtc( $this, 'new added record by doAction' );
			}
			else { // self::TYPE_GET
				$model::modDatum( $this->id, $this->input_data );
				$this->data = array_merge( $this->data, $this->input_data );
				$this->input_data = array();
			}
		}
		else 
		if( $this->execute == self::EXEC_DEL )         // deleting data
		{
			if( $this->type == self::TYPE_GET ) {
				$model::delDatum( $this->id );
			}
		}
		$this->execute = self::EXEC_NONE;
		return $this;
	}
	// +--------------------------------------------------------------- +
	/**	doRelate( $col, $rec, &$data=FALSE )
	 *	save the relationship to db.
	 *
	 *	@param string $col
	 *		column name of the relation.
	 *	@param object $rec
	 *		related Record object.
	 *	@param array &$data
	 *		if $data is set, relation is implemented into $data that
	 *		is saved to db later (in doAction). if $data is not set,
	 *		this method will write to db each time it is called.
	 */
	function doRelate( $col, $rec, &$data=FALSE )
	{
		/* modify input_data to reflect relations. 
		$rule = array(
			'rec_model' => dao_name // dao name of this record.
			'rel_model' => dao_name // dao name relate to this record.
			'rec_column'=> column   // column name (like contact_id)
			'rel_column'=> column   // column name if not an ID.
		);
		*/
		if( WORDY > 5 ) wtc( $rec, "doRelate( $col, $rec, &$data )" );
		if( $rec->getType() == self::TYPE_NEW ) {
			self::linkSet( $this, $rec, 'doRelate', $col );
			return;
		}
		if( !method_exists( $this->model, 'getRelationship' ) ) return;
		$model = $this->model;
		$relationship = $model::getRelationship();
		if( empty( $relationship ) ) return;
		if( !isset( $relationship[ $col ] ) ) return;
		
		$rule = $relationship[ $col ];
		
		if( $this->getModel() == $rule[ 'rec_model' ] && 
			$rec->getModel()  == $rule[ 'rel_model' ] ) 
		{
			// relate two records. 
			// TODO: this code works for RDB kind of system. 
			//       can I move out this block to DAO?
			if( WORDY ) wtc( $rule, "relate rule for id=" . $rec->getId() );
			if( have_value( $rule[ 'rel_column' ] ) ) {
				$value = $rec->get( $rule[ 'rel_column' ] );
			}
			else {
				$value = $rec->getId();
			}
			$column = $rule['rec_column' ];
			if( $data === FALSE ) {  // modify data now.
				$data = array( $column => $value );
				$model::modDatum( $this->id, $data );
			}
			else {                   // only add relation to data.
				$data[ $column ] = $value;
			}
		}
	}
	// +--------------------------------------------------------------- +
	/**	doValidate()
	 *	validates the input. 
	 *	validation take place only if validated flag is FALSE. 
	 *	sets validated flag to TRUE if OK, or not alter the flag. 
	 *	
	 *	@return $this
	 */
    function doValidate()
    {
		// validation only if not validated yet. 
		if( $this->isValidated() ) return $this;
		
		// ignore data if empty for NEW data
        $model = $this->model;
		if( $this->type == self::TYPE_NEW && 
			method_exists( $model, 'ignore_check_for_new' ) &&
			$model::ignore_check_for_new( $this->input_data ) )
		{
			$this->type = self::TYPE_IGNORE;
			$this->execute = self::EXEC_NONE;
			return $this;
		}
		// set data to validate. 
		if( $this->type == self::TYPE_NEW ) {
			// for new data, validate input data only. 
			$data_to_validate = $this->input_data;
		}
		else {
			// for existing data, merge input and existing data. 
			$data_to_validate = 
				array_merge( $this->data, $this->input_data );
		}
		// get pgg for validation
		$pgg = new \pgg_check();
		$pgg->setVarOrder( PGG_DATA );
		$pgg->setPostVars( $data_to_validate );
		
		// validate the input
		$method  = $this->input_method;
		$err_num = $model::$method( $pgg );
		$this->input_data = $pgg->popVariables();
		$this->data       = array_merge( $this->data, $this->input_data );
		if( $err_num ) 
		{
			$this->err_msg = $pgg->popErrors();
			$this->err_num = 1;
			if( WORDY > 3 ) wordy_table( $this->data, "validate failed on {$check_input}" );
			if( WORDY > 3 ) wordy_table( $this->err_msg, "error message on {$check_input}" );
			throw new DataInvalid_DbaRecord_Exception();
		}
		// validate the relation. 
		$relationship = $model::getRelationship();
		if( !empty( $relationship ) ) 
		{
			foreach( $relationship as $column => $rec ) 
			{
				if( $relationship[ $column ][ 'required' ] && 
					!isset( $this->relations[  $column ] ) && 
					!isset( $data_to_validate[ $column ] ) ) 
				{
					$this->err_num = 1;
					if( WORDY > 3 ) wtc( $this, "validate failed on relation" );
					throw new DataInvalid_DbaRecord_Exception();
				}
			}
		}
		
		$this->validationOk();
        return $this;
    }
	// +--------------------------------------------------------------- +
    function validationOk() {
		$this->validated = TRUE;
    }
	// +--------------------------------------------------------------- +
    function resetValidated() {
		$this->validated = FALSE;
    }
	// +--------------------------------------------------------------- +
    function isValidated() {
		return $this->validated;
    }
	// +----------------------------------------------------------------------------+
	//
	//                             Methods Required by Cena
	//
	// +----------------------------------------------------------------------------+
	
	// +--------------------------------------------------------------- +
    function getModel() { // required for cena
		return $this->model;
    }
	// +--------------------------------------------------------------- +
    function getType() { // required for cena
		return $this->type;
    }
	// +--------------------------------------------------------------- +
	function getId() { // required for cena
		return $this->id;
	}
	// +----------------------------------------------------------------------------+
	//
	//                          pop functions for html output
	//
	// +----------------------------------------------------------------------------+
	
	// +--------------------------------------------------------------- +
	function getSelInstance( $column ) {
		$model = $this->model;
		return $model::getSelInstance( $column );
	}
	// +--------------------------------------------------------------- +
	function popHtml( $column, $html_type=NULL ) { // required for cena
		//if( !$html_type ) $html_type = 'NAME';
		$val   = $this->get( $column );
		$err   = $this->err_msg[ $column ];
		$model = $this->model;
		$sel   = $model::getSelInstance( $column );
		return $sel->popHtml( $html_type, $val, $err );
    }
	// +--------------------------------------------------------------- +
	function popHtmlState()  // required for cena
	{
		// determine the state of this record.
		if( $this->execute == self::EXEC_DEL ) {
			$html = '削除';
		}
		else
		if( $this->execute == self::EXEC_SAVE ) {
			if( $this->type == self::TYPE_NEW ) {
				$html = '登録';
			}
			else {
				$html = '修正';
			}
		}
		else {
			$html = '';
		}
		return $html;
    }
	// +--------------------------------------------------------------- +
	function popHtmlDelState( $html_type=NULL )  // required for cena
	{
		if( $html_type == 'EDIT' ) {
			if( $this->execute == self::EXEC_DEL ) {
				$html .= html\Tags::checkOne( $this->id_name, $id, TRUE );
			}
			else {
				$html .= html\Tags::checkOne( $this->id_name, $id, FALSE );
			}
		}
		else
		if( $html_type == 'NEW' ) {
		}
		else
		if( $html_type == 'PASS' ) {
			if( $this->execute == self::EXEC_DEL ) {
				$html .= html\Tags::inputHidden( $this->id_name, $id );
			}
		}
		else {
		}
		return $html;
	}
	// +--------------------------------------------------------------- +
}


?>