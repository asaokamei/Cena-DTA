<?php
// +----------------------------------------------------------------------+
// + コンタクトDAO
// +----------------------------------------------------------------------+

require_once( '../cenaPhp/Dba/Model.php' );
use CenaDta\Dba as orm;

class dao_contact110 extends orm\Model
{
	static $dao_table = "contact110"; // "the" table name. 
	static $dao_pkey  = 'connect_id';
	static $col_names;
	static $col_sels;
	static $col_checks;
	function __construct( $config=NULL )
	{
		if( !have_value( $config ) ) { $config = dirname( __FILE__ ) . '/dba.ini.php'; };
		parent::__construct( $config );
		$this->id_name    = "connect_id";
		$this->dao_table  = self::$dao_table;
		$this->table      = $this->dao_table;
		$this->clear();
		self::setColumns();
		
		$this->del_flag   = '';
		$this->del_value  = '';
		
		$this->new_date = '';
		$this->new_time = '';
		$this->mod_date = '';
		$this->mod_time = '';
	}
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
				array( 'CenaDta\\Html\\formHidden', '', '', 'OFF' ),
			'connect_info' => 
				array( 'CenaDta\\Html\\formText', '15', '', 'ON' ),
			'connect_type' => 
				array( 'sel_connect_type', '', '', 'OFF' ),
		);
	}
	// +----------------------------------------------------------+
	// for REST/AR
	// rest[ scheme ][ model ][ type ][ id ][ action ] = data
	// +----------------------------------------------------------+
	// +----------------------------------------------------------+
	static function getRelationship()
	{
		$relate = array();
		$relate[ 'contact_id' ] =	array( 
			'rec_model'  => 'dao_contact110', 
			'rel_model'  => 'dao_contact100', 
			'rec_column' => 'contact_id', 
			'rel_column' => NULL
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
	/** gets Dba_Record object given an id (primary key) 
		@param string $id
			id (primary key) of the data.
		@param object $records
			returns an array of 
	 */
	static function getRecordByContactId( $contact_id, &$records )
	{
		$this->where( 'contact_id', $contact_id )
		     ->fetchRecords( $records );
		;
		return $this;
	}
	// +----------------------------------------------------------+
}
?>