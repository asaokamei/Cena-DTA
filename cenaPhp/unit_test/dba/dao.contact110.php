<?php
require_once( dirname( __FILE__ ) . "/../../Dba/Model.php" );
use CenaDta\Dba\Model as ormModel;
// +----------------------------------------------------------------------+
// + コンタクトDAO
// +----------------------------------------------------------------------+

class dao_contact110 extends ormModel
{
	static $dao_table = "utest110_connect"; // "the" table name. 
	static $dao_pkey  = 'connect_id';
	static $col_names;
	static $col_sels;
	static $col_checks;
	static $col_info = array(
		'new_date'     => FALSE,
		'new_time'     => FALSE,
		'new_datetime' => FALSE,
		'mod_date'     => FALSE,
		'mod_time'     => FALSE,
		'mod_datetime' => FALSE,
		'del_flag'     => FALSE,
		'del_value'    => FALSE,
	);
	// +----------------------------------------------------------+
	static function setColumns()
	{
		self::$col_names = array(
			'contact_id' => '連絡方法ID',
			'connect_info' =>  '連絡方法',
			'connect_type' =>  '連絡種別',
		);
		self::$col_checks = array(
			'connect_info' => 
				array( 'pushChar', PGG_VALUE_MUST_EXIST ), 
			'connect_type' => 
				array( 'pushChar', PGG_VALUE_MUST_EXIST, sel_connect_type::$item_list ), 
		);
		self::$col_sels = array(
			'connect_id' => 
				array( 'formHidden', '', '', 'OFF' ),
			'connect_info' => 
				array( 'formText', '15', '', 'ON' ),
			'connect_type' => 
				array( 'sel_connect_type', '', '', 'OFF' ),
		);
	}
	// +----------------------------------------------------------+
	static function getRecordByContactId( $contact_id, &$records )
	{
		$this->where( 'contact_id', $contact_id )
		     ->execSelect()
		     ->fetchRecords( $records );
		;
		return $this;
	}
	// +----------------------------------------------------------+
	static function getRelationship()
	{
		$relate = array();
		$relate[ 'contact_id' ] =	array( 
			'rec_model'  => 'dao_contact110', 
			'rec_column' => 'contact_id', 
			'rel_model'  => 'dao_contact100', 
			'rel_column' => NULL,
			'required'   => TRUE,
		);
		return $relate;
	}
	// +----------------------------------------------------------+
	static function check_input_prep( &$pgg )
	{
		return $pgg->errGetNum();
	}
	// +----------------------------------------------------------+
	static function ignore_check_for_new( $data )
	{
		if( !have_value( $data[ 'connect_info' ] ) ) return TRUE;
		return FALSE;
	}
	// +----------------------------------------------------------+
	static function check_input( &$pgg )
	{
		/* Checking User Input */
		self::checkInput( $pgg, 'connect_info' );
		self::checkInput( $pgg, 'connect_type' );
		
		return $pgg->errGetNum();
	}
	// +----------------------------------------------------------+
}
?>