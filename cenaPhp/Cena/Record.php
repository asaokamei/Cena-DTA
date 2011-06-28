<?php
namespace CenaDta\Cena;
/**
 *	Cena Record class. 
 *
 *	@copyright     Copyright 2010-2011, Asao Kamei
 *	@link          http://www.workspot.jp/cena/
 *	@license       GPLv2
 */

use CenaDta\Html as html;

require_once( dirname( __FILE__ ) . '/Master.php' );

class CenaRecord_Exception extends \Exception {}


class Record
{
	const ACT_SET  = 'set';
	const ACT_GET  = 'get';
	const ACT_DEL  = 'del';
	const ACT_REL  = 'rel';
	
	var $fingerp = NULL; // finger print for identifying object...
	var $scheme  = NULL;
	var $record  = NULL;
	var $model   = NULL;
	var $type    = NULL;
	var $id      = NULL;
	var	$act_map = array( // map Cena's act to Record's method.
			'set' => 'setProperty',
			'rel' => 'setRelation',
			'del' => 'delRecord',
			'get' => 'getProperty',
		);
	// +--------------------------------------------------------------- +
	/**	__construct( $rec )
		constructor for Record. 
		
		@param object $rec
			Dba_Record object for its record. 
	 */
	function __construct( $rec ) {
		$this->record  = $rec;
		$this->scheme  = Cena::$scheme;
		$this->fingerp = Cena::getCenaId( $rec );
		//$this->updateRecInfo();
	}
    // +----------------------------------------------------------------------------+
	function __toString() {
		return "Record(#" . spl_object_hash( $this ).  ")::fingerp={$this->fingerp} ";
	}
	// +-----------------------------------------------------------+
	/**	getRecord()
		returns its Dba_Record object. 
		
		@returns object
			Dba_Record object. 
	 */
	function getRecord() {
		return $this->record;
	}
	// +-----------------------------------------------------------+
	/**	setScheme( $scheme )
		sets scheme for the Record object. 
		
		@param string $scheme
			name of scheme to set. 
		@return $this
	 */
	function setScheme( $scheme ) {
		$this->scheme = $scheme;
		return $this;
	}
	// +----------------------------------------------------------------------------+
	//
	//                                   relation
	//
	// +----------------------------------------------------------------------------+
	
	// +--------------------------------------------------------------- +
	/**	loadRelation() 
		load relation for the record
		
		@return $this
	 */
	function loadRelation() 
	{
		// get related records from dba record.
		$this->record->loadRelation(); // load relation from db. 
		return $this;
	}
	// +--------------------------------------------------------------- +
	/**	getRelation( $cena_id=TRUE )
		get the related cena_id or Record object. 
		
		TODO: specify $column to retrieve. 
		
		@param boolean $cena_id
			returns cena_id if true (default). 
			returns Record object if set to false.
		@return string/object
			returns cena_id or Record object
			depending on $cena_id. 
	 */
	function getRelation( $name=FALSE, $cena_id=TRUE ) 
	{
		// get related cena_id. 
		$relations = $this->record->getRelation( $name );
		if( empty( $relations ) ) {
			$rel_cena  = array(); // no relations. 
		}
		else
		if( is_array( $relations ) ) {
			foreach( $relations as $col =>$rec ) {
				if( $cena_id ) {
					$rel_cena[ $col ] = Cena::getCenaId( $rec );
				}
				else {
					$rel_cena[ $col ] = Cena::getCenaByRec( $rec );
				}
			}
		}
		else {
			if( $cena_id ) {
				$rel_cena = Cena::getCenaId( $relations );
			}
			else {
				$rel_cena = Cena::getCenaByRec( $relations );
			}
		}
		return $rel_cena;
	}
	// +--------------------------------------------------------------- +
	function setRelation( $column, $cena ) 
	{
		if( WORDY ) echo "Record::setRelation( $column, $cena )<br />\n";
		if( is_string( $cena ) ) {
			$cena = trim( $cena );
			if( Cena::verifyCenaId( $cena ) ) {
				$cena = Cena::getCenaByCenaId( $cena );
			}
		}
		$this->record->setRelation( $column, $cena->record );
		return $this;
	}
	// +----------------------------------------------------------------------------+
	//
	//                                   children
	//
	// +----------------------------------------------------------------------------+
	
	// +--------------------------------------------------------------- +
	/**	loadChildren() 
		load children for the record.
		
		@return $this
	 */
	function loadChildren() {
		$this->record->loadChildren();
		return $this;
	}
	// +--------------------------------------------------------------- +
	/**	getChildren( $model_name=FALSE, $cena_id=TRUE )
		get the child cena_id or Record object. 
		
		@param string $model
			specify the name of model of children. if not specified, 
			returns all the children. 
		@param boolean $cena_id
			returns cena_id if true (default). 
			returns Record object if set to false.
		@return array of string/object
			returns an array of cena_id or Record object
			depending on $cena_id. 
	 */
	function getChildren( $model_name=FALSE, $cena_id=TRUE ) 
	{
		$children = $this->record->getChildren( $model_name );
		if( empty( $children ) ) {
			return $children;
		}
		if( WORDY > 3 ) wtc( $children, "getChildren( $model_name, $cena_id )" );
		$cena_child = array();
		if( $model_name === FALSE ) {
			$model_list = array_keys( $children );
		}
		else {
			$model_list = array( $model_name );
			$children = array( $model_name => $children );
		}
		foreach( $model_list as $model ) {
			foreach( $children[ $model ] as $rec ) {
				if( !isset( $cena_child[ $model ] ) ) $cena_child[ $model ] = array();
				if( $cena_id ) {
					$cena_child[ $model ][] = Cena::getCenaId( $rec );
				}
				else {
					$cena_child[ $model ][] = Cena::getCenaByRec( $rec );
				}
			}
		}
		if( $model_name !== FALSE ) $cena_child = $cena_child[ $model_name ];
		return $cena_child;
	}
	// +--------------------------------------------------------------- +
	/**	setChildren( $model, $cena ) 
		sets children records. 
		
		@param string $model
			model name of children record.
		@param string $cena
			cena_id of the children. or Record object. 
	 */
	function setChildren( $model, $cena ) 
	{
		if( WORDY ) echo "Record::setChildren( $column, $cena )<br />\n";
		if( is_string( $cena ) ) {
			if( Cena::verifyCenaId( $cena ) ) {
				$cena = Cena::getCenaByCenaId( $cena );
			}
		}
		$this->record->setChildren( $model, $cena->record );
		return $this;
	}
	// +----------------------------------------------------------------------------+
	//
	//                           manipulation of cena record
	//
	// +----------------------------------------------------------------------------+
	
	// +--------------------------------------------------------------- +
	function do_function( $function ) {
		// apply function to dba record.
		// TODO: this is potentially very dangerous. 
		//       must limit the function.
		$this->record->$function();
	}
	// +--------------------------------------------------------------- +
	function setActions( $actions ) {
		if( WORDY ) echo "<font color=brown>setActions( \$actions ) use manipulate method.</font>";
		return $this->manipulate( $actions );
	}
	// +--------------------------------------------------------------- +
	/**	manipulate( $actions )
		manipulate the Record. 
		input action and meta-data for associated Record. 
		
		@param array $actions
			manipulation meta data.
			array(
				'act' => array( data...),
			);
		@return $this
	 */
	function manipulate( $actions )
	{
		if( WORDY > 0 ) wtc( $actions, 'manipulate cena:' . $this );
		if( have_value( $actions ) && is_array( $actions ) ) 
		foreach( $actions as $act => $data ) 
		{
			if( !isset( $this->act_map[ $act ] ) ) {
				continue;
			}
			$method = $this->act_map[ $act ];
			if( WORDY > 5 ) wtc( $data, "manipulate " . $this->getCenaId() . " act:{$act}=>{$method}" );
			if( is_array( $data ) ) { 
				foreach( $data as $name => $value ) {
					$this->$method( $name, $value );
				}
			}
			else {
				$this->$method( $data );
			}
			if( WORDY > 5 ) wtc( $this->record, "setActions in " . $this->getCenaId() . " method:{$method}" );
		}
		return $this;
	}
	// +--------------------------------------------------------------- +
	/**	getCenaId()
		returns cena_id for this Record object. 
		example: Cena.model.type.101
		
		@return string
			returns cena_id. 
	 */
	function getCenaId() {
		return Cena::getCenaId( $this );
	}
	// +--------------------------------------------------------------- +
	/**	makeCenaName()
		returns main part of cena_name for this Record object. 
		example: Cena[model][type][101]
		
		@return string
			returns cena_name. 
	 */
	function makeCenaName() {
		return Cena::getCenaName( $this );
	}
	// +--------------------------------------------------------------- +
	/**	getScheme()
		returns scheme name for this Record object. 
		example: Cena
		
		@return string
			returns scheme name. 
	 */
    function getScheme() { // 
		return $this->scheme;
    }
	// +--------------------------------------------------------------- +
	/**	getModel()
		returns model name for this Record object. 
		example: dao_model_name
		
		@return string
			returns model name. 
	 */
    function getModel() { // 
		return $this->record->getModel();
    }
	// +--------------------------------------------------------------- +
	/**	getType()
		returns type for this Record object. 
		example: NEW or GET
		
		@return string
			returns type. 
	 */
    function getType() { // 
		return $this->record->getType();
    }
	// +--------------------------------------------------------------- +
	/**	getId()
		returns id (primary key) for this Record object. 
		example: 101
		
		@return string
			returns id (primary key). 
	 */
	function getId() { // 
		return $this->record->getId();
	}
	// +--------------------------------------------------------------- +
	/**	getData( $name=FALSE )
		returns data (property) of its record. 
		
		TODO: rename to getProperty? get? maybe keep it as is?
		
		@param string $name
			specify name of data to retrieve. 
			if not specified, returns all data. 
		@return mix
			returns data as specified by $name. 
	 */
	function getData( $name=FALSE ) {
		return $this->record->get( $name );
	}
	// +--------------------------------------------------------------- +
	/**	getProperty( $name=FALSE )
		returns data (property) of its record. same as getData method. 
		see getData method for details. 
	 */
	function getProperty( $name=FALSE ) {
		return $this->getData( $name );
	}
	// +--------------------------------------------------------------- +
	/**	setProperty( $name, $value )
		sets property (data) for the cena record. 
		please refer to Dba_Record::setProperty for detailed 
		behavior of this method. 
		
		@param string $name
			name of property.
		@param string $value
			value of property.
		@return $this;
	 */
	function setProperty( $name, $value ) {
		$this->record->setProperty( $name, $value );
		return $this;
	}
	// +--------------------------------------------------------------- +
	/**	delRecord( $data=FALSE, $val=FALSE )
		delete current record. 
		
		@param mixed $data
			dummy argument.
		@param mixed $data
			dummy argument.
		@return $this
	 */
	function delRecord( $name=FALSE, $value=FALSE ) {
		$this->record->delRecord( $name, $value );
		return $this;
	}
	// +--------------------------------------------------------------- +
	/**	getCenaName( $action=NULL, $column=NULL ) 
		returns cena name for given action and column name. 
		example: Cena[model][type][101][act][column]
		
		@param string $action
			name of action
		@param string $action
			name of column (or property)
		@return string
			return cena_name. 
	 */
	function getCenaName( $action=NULL, $column=NULL ) 
	{
		$name = $this->makeCenaName();
		if( $action ) $name .= "[{$action}]";
		if( $column ) $name .= "[{$column}]";
		return $name;
	}
	// +----------------------------------------------------------------------------+
	//
	//                          pop functions for html output
	//
	// +----------------------------------------------------------------------------+
	
	// +--------------------------------------------------------------- +
	function popIdHidden() 
	{
		$name = $this->getCenaName();
		$id   = $this->getCenaId();
		return html\Tags::inputHidden( $name, $id );
	}
	// +--------------------------------------------------------------- +
	function popRelations() 
	{
		$html = NULL;
		if( $relate = $this->record->getRelation() ) 
		{
			$cena_term = $this->getCenaName( self::ACT_REL );
			html\Tags::setCenaTerm( $cena_term );
			foreach( $relate as $col => $rec ) {
				$cena_id  = Cena::getCenaId( $rec );
				$html    .= html\Tags::inputHidden( $col, $cena_id );
			}
			html\Tags::setCenaTerm( FALSE );
		}
		return $html;
	}
	// +--------------------------------------------------------------- +
	function popHtmlState( $html_type ) 
	{
		$cena_term = $this->getCenaName( self::ACT_DEL );
		html\Tags::setCenaTerm( $cena_term );
		$html = 
			$this->record->popHtmlState() . 
			$this->record->popHtmlDelState( $html_type );
		html\Tags::setCenaTerm( FALSE );
		return $html;
	}
	// +--------------------------------------------------------------- +
	function popHtml( $column, $html_type=NULL ) 
	{
		$cena_term = $this->getCenaName( self::ACT_SET );
		html\Tags::setCenaTerm( $cena_term );
		$html = $this->record->popHtml( $column, $html_type );
		html\Tags::setCenaTerm( FALSE );
		return $html;
	}
	// +--------------------------------------------------------------- +
}


?>