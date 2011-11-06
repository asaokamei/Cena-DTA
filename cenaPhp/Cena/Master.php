<?php
namespace CenaDta\Cena;
/**
 *	Cena Master class for driving Cena. 
 *
 *	@copyright     Copyright 2010-2011, Asao Kamei
 *	@link          http://www.workspot.jp/cena/
 *	@license       GPLv2
 */
use CenaDta\Dba as orm;

require_once( dirname( __FILE__ ) . '/Envelope.php' );

class CenaException extends \Exception {}

class Cena
{
	const  CENA_SEP = '.';
	static $scheme  = 'Cena';  // mother name of everything
	static $all_rec = array(); // singleton for cena records
	static $models  = array(); // indicate models to process
	static $act_map = array(); // map action to records.
	
	static $use_envelope  = FALSE; // set Cenv Scheme for Cena Records. 
	static $push_envelope = FALSE; // push Cena Records to Env.
	static $set_relations = FALSE; // automatically set relations.
	// +----------------------------------------------------------------------------+
	//
	//                             define behavior of cena
	//
	// +----------------------------------------------------------------------------+
	
	// +--------------------------------------------------------------- +
	/**
	 *	sets $push_envelope flag; if the flag is true, Record's 
	 *	are pushed to envelope (Envelope::pushEnvData) each time 
	 *	new Record is created in getCena. 
	 *	
	 *	@param boolean $use
	 *		set to TRUE to push; set to FALSE if not. 
	 */
	static function pushEnvelope( $use=TRUE )
	{
		if( $use ) {
			self::$push_envelope = TRUE;
		}
		else {
			self::$push_envelope = FALSE;
		}
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	sets $use_envelope flag; if the flag is true, Record's 
	 *	scheme name is overwritten to use Cena/Envelope (Cenv) each time 
	 *	new Record is created in getCena. 
	 *	
	 *	@param boolean $use
	 *		set to TRUE to use Cenv; set to FALSE if not. 
	 */
	static function useEnvelope( $use=TRUE )
	{
		if( $use ) {
			self::$use_envelope = TRUE;
		}
		else {
			self::$use_envelope = FALSE;
		}
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	sets $set_relations flag; if the flag is true, Record's 
	 *	relation is populated using loadRelation each time 
	 *	new Record is created in getCena. 
	 *	
	 *	TODO: rename to loadRelation?
	 *	
	 *	@param boolean $use
	 *		set to TRUE to use Cenv; set to FALSE if not. 
	 */
	static function setRelation( $use=TRUE )
	{
		if( $use ) {
			self::$set_relations = TRUE;
		}
		else {
			self::$set_relations = FALSE;
		}
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	clears Record's object pool. 
	 *	
	 *	@param string $model
	 *		clears only the object pool for the model if specified. 
	 */
	static function clearCenas( $model=NULL ) 
	{
		if( have_value( $model ) && isset( self::$all_rec[ $model ] ) ) {
			self::$all_rec[ $model ] = array();
		}
		else {
			self::$all_rec = array();
		}
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	specify models to process. uses as well as for determining 
	 *	the order of models to process.
	 *	
	 *	@param array $models
	 *		list of model names. if string is given, treat it as 
	 *		single array as array( $models );
	 */
	static function set_models( $models ) {
		if( !is_array( $models ) ) $models = array( $models );
		self::$models = $models;
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	adds model to process.
	 *	
	 *	@param string $models
	 *		name of model to process.
	 */
	static function add_models( $model ) {
		self::$models[] = $model;
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	sets map for action. $map should be in 
	 *	$map = array(
	 *	   'set' => 'setProperty',
	 *	   'rel' => 'setRelation',
	 *	);
	 *	
	 *	@param string $model
	 *		specify the model name to set the action map. 
	 *	@param array $map
	 *		action map for the specified model. 
	 */
	static function set_action_map( $model, $map ) {
		self::$act_map[ $model ] = $map;
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	gets the mapped action name for the model. 
	 *	example 'rel' => 'setRelation'
	 *	
	 *	@param string $model
	 *		model name of the action. 
	 *	@param string $act
	 *		action name to map.
	 *	@return string
	 *		returns mapped action name.
	 *		returns the $act as is if map was not found. 
	 */
	static function get_method_from_action( $model, $act ) {
		$action = $act;
		if( isset( self::$act_map[ $model ][ $act ] ) ) {
			$action = self::$act_map[ $model ][ $act ];
		}
		return $action;
	}
	// +--------------------------------------------------------------- +
	static function set_actions( $cena, $actions )
	{
		if( !empty( $actions ) ) 
		foreach( $actions as $act => $data ) {
			$cena->$act( $data );
		}
		return $this;
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	get Record object of $model, $type, and $id. 
	 *	do the object pooling for the above three information. 
	 *	
	 *	static flags:
	 *	these flags when cena is found in the object pool. 
	 *	
	 *	self::$set_relations
	 *		populate related objected (uses loadRelation).
	 *	self::$use_envelope
	 *		use Evnelope style (Cenv). 
	 *	self::$push_envelope
	 *		pushes the record the Envelope for output later. 
	 *	
	 *	@param string $model
	 *		name of the model (dao's class name).
	 *	@param string $type
	 *		type of the record (get or new).
	 *	@param string $id
	 *		id (primary key) of the record. 
	 *	@param string $rec=FALSE
	 *		Dba_Record object (or comforms to Cena's requirement).
	 *		if omitted, record is created by getRecord method.  
	 *	@return object
	 *		returns Record object, or 
	 */
	static function getCena( $model, $type, $id, $rec=FALSE )
	{
		if( isset( self::$all_rec[ $model ][ $type ][ $id ] ) ) {
			$cena = self::$all_rec[ $model ][ $type ][ $id ];
			if( WORDY > 5 ) echo "Cena::getCena( $model, $type, $id ) found cena. \n<br />";
		}
		else {
			if( !have_value( $rec ) ) {
				if( !class_exists( $model ) ) {
					throw new Exception( "class {$model} does not exists" );
				}
				if( !method_exists( $model, 'getRecord' ) ) 
					throw new Exception( "missing getRecord method in {$model}" );
				$rec  = $model::getRecord( $type, $id );
			}
			$cena = new Record( $rec );
			if( self::$set_relations ) {
				$cena->loadRelation();
			}
			if( self::$use_envelope ) {
				Envelope::setCenaEnv( $cena );
			}
			if( self::$push_envelope ) {
				Envelope::pushEnvData( $cena );
			}
			self::$all_rec[ $model ][ $type ][ $id ] = $cena;
			if( WORDY > 5 ) echo "Cena::getCena( $model, $type, $id ) created new cena\n<br />";
		}
		if( WORDY ) echo "Cena::getCena( $model, $type, $id ),\n$rec ) => \n$cena <br />\n";
		return $cena;
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	get Record object based on Dba_Record object. get's its
	 *	model, type, and id, from the $rec, and use getCena method 
	 *	to obtain Record object. 
	 *	throws Exception if verifyRecord failed. 
	 *	
	 *	@param object/array $rec
	 *		Dba_Record object (or cena comforming object). 
	 *		can be an array of Dba_Record objects. 
	 *	@return object
	 *		returns Record object, or 
	 *		array of objects if input is an array. 
	 *		
	 */
	static function getCenaByRec( $rec )
	{
		if( empty( $rec ) ) return FALSE;
		if( is_array( $rec ) ) {
			$cenas = array();
			foreach( $rec as $r ) {
				$cenas[] = self::getCenaByRec( $r, $setRel );
			}
			return $cenas;
		}
		if( !self::verifyRecord( $rec ) ) {
			throw new CenaException( 'getCenaByRec: no getModel in $rec' );
		}
		$model  = $rec->getModel();
		$type   = $rec->getType();
		$id     = $rec->getId();
		$cena   = self::getCena( $model, $type, $id, $rec );
		return $cena;
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	verifies if $cena_id is a valid cena_id.
	 *	
	 *	@param string $cena_id
	 *		should be a valid cena_id
	 *	@return boolean
	 *		returns true if it is valid. 
	 */
	static function verifyCenaId( $cena_id )
	{
		$verified = FALSE;
		if( is_string( $cena_id ) && 
			substr( $cena_id, 0, strlen( self::$scheme ) ) == self::$scheme ) {
			$list   = explode( self::CENA_SEP, $cena_id );
			if( count( $list ) > 3 ) { // must have 4 items to get Record.
				$verified = TRUE;
			}
		}
		return $verified;
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	verifies if $rec comforms as Cena record. i.e. must have 
	 *	get{Model|Type|Id} method, at least. 
	 *	
	 *	@returns boolean
	 *		true if verified. 
	 */
	static function verifyRecord( $rec ) {
		if( method_exists( $rec, 'getModel' ) && 
			method_exists( $rec, 'getType'  ) && 
			method_exists( $rec, 'getId'    ) ) {
			return TRUE;
		}
		// throw new CenaException( 'getCenaByRec: no getModel in $rec' );
		return FALSE;
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	returns a Record object from cena_id.
	 *	
	 *	@param string $cena_id
	 *		cena_id string. 
	 *	@return object
	 *		Record object if $cena_id is valid. 
	 *		if not, returns input $cena_id. 
	 */
	// 
	// +--------------------------------------------------------------- +
	static function getCenaByCenaId( $cena_id )
	{
		if( self::verifyCenaId( $cena_id ) ) {
			$list   = explode( self::CENA_SEP, trim( $cena_id ) );
			$scheme = $list[0];
			$model  = $list[1];
			$type   = $list[2];
			$id     = $list[3];
			$cena = self::getCena( $model, $type, $id );
			return $cena;
		}
		return $cena_id; // return the input as is...
	}
	// +--------------------------------------------------------------- +
	/**	
	 *	returns cena_id from a cena or active_record instance. 
	 *	throws CenaException if verifyRecord failed. 
	 *	
	 *	@param object $cena
	 *		Record object
	 *	@return string
	 *		returns cena_id of the $cena record. 
	 */
	static function getCenaId( $cena )
	{
		if( !self::verifyRecord( $cena ) ) {
			if( WORDY ) wt( $cena, 'cena record: ', $cena );
			throw new CenaException( 'does not comform to cena record' );
		}
		$model  = $cena->getModel();
		$type   = $cena->getType();
		$id     = $cena->getId();
		return self::makeCenaId( $model, $type, $id );
	}
	// +-----------------------------------------------------------+
	/**	
	 *	creates cena_id (i.e. cena.model.get.123) from $model, 
	 *	$type, $id, and optionaly $scheme. 
	 *	
	 *	@param string $model
	 *	@param string $type
	 *	@param string $id
	 *	@param string $scheme
	 *	@return string
	 *		returns cena_id.
	 */
	static function makeCenaId( $model, $type, $id, $scheme=NULL ) {
		if( !have_value( $scheme ) ) $scheme = self::$scheme;
		$info = array( Cena::$scheme, $model, $type, $id );
		return implode( Cena::CENA_SEP, $info );
	}
	// +-----------------------------------------------------------+
	/**	
	 *	returns cena_name from a cena instance. the instance must 
	 *	have get{Scheme|Model|Type|Id} method to operate. 
	 *	
	 *	@see Cena::verifyRecord()
	 *	@param object $cena
	 *		cena instance, or instances comform to verifyRecord.
	 *	@return string
	 *		cena_name.
	 */
	static function getCenaName( $cena )
	{
		if( !self::verifyRecord( $cena ) ) throw new CenaException();
		if( method_exists( $cena, 'getScheme' ) ) {
			$scheme = $cena->getScheme();
		}
		else {
			$scheme = self::$scheme;
		}
		$model  = $cena->getModel();
		$type   = $cena->getType();
		$id     = $cena->getId();
		return self::makeCenaName( $model, $type, $id, $scheme );
	}
	// +-----------------------------------------------------------+
	static function get_cena_input( &$input ) {
		return $input[ self::$scheme ];
	}
	// +-----------------------------------------------------------+
	static function makeCenaName( $model, $type, $id, $scheme=NULL ) {
		if( !have_value( $scheme ) ) $scheme = self::$scheme;
		return "{$scheme}[{$model}][{$type}][{$id}]";
	}
	// +-----------------------------------------------------------+
	static function do_cena( &$cena_recs, $doAct=NULL, &$input=NULL )
	{
		if( WORDY ) echo "do_cena for $doAct <br>\n";
		$cena_recs = array();
		if( $input === NULL ) {
			$input = &$_REQUEST;
		}
		$num_err = 0;
		if( isset( $input[ Envelope::$env_name ] ) ) {
			foreach( $input[ Envelope::$env_name ] as $cena_in ) {
				$num_err += self::proc_cena( $cena_recs, $doAct, $cena_in );
			}
		}
		if( isset( $input[ self::$scheme ] ) ) {
			$cena_in  = $input[ self::$scheme ];
			$num_err += self::proc_cena( $cena_recs, $doAct, $cena_in );
		}
		return $num_err;
	}
	// +-----------------------------------------------------------+
	static function proc_cena( &$cena_recs, $doAct=NULL, &$cena_in=NULL )
	{
		if( WORDY ) wt( $cena_in,  "do_cena for $doAct " );
		if( empty( self::$models ) ) {
			$do_models = array_keys( $cena_in );
		}
		else {
			$do_models = self::$models;
		}
		if( WORDY > 3 ) wtc( self::$models, 'do for models' );
		
        $num_err = 0;
		foreach( $do_models as $model ) // for all models
		{
			if( !have_value( $cena_in, $model) ) continue;
			$get_types = $cena_in[ $model ];
			if( WORDY ) wt( $get_types, "model: $model" );
			foreach( $get_types as $type => $get_ids ) // for all types
			{
				foreach( $get_ids as $id => $actions ) // for all ids
				{
					$cena = Cena::getCena( $model, $type, $id );
					$cena->manipulate( $actions );
					try {
						if( have_value( $doAct ) ) 
						$cena->do_function( $doAct );
					} 
					catch( orm\DataInvalid_DbaRecord_Exception $e ) {
						$num_err ++;
					}
					$cena_recs[ $model ][] = $cena;
				} // end loop on ids
			} // end loop on types
		} // end loop on models
		if( WORDY ) wt( $cena_recs, 'cena recs' );
		return $num_err;
	}
	// +--------------------------------------------------------------- +
}


?>