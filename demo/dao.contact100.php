<?php
// +----------------------------------------------------------------------+
// + コンタクトDAO
// +----------------------------------------------------------------------+

class dao_contact100 extends \CenaDta\Dba\Model
{
	static $dao_table = "contact100"; // "the" table name. 
	static $dao_pkey  = 'contact_id';
    static $config_name = '';
	static $col_names;
	static $col_sels;
	static $col_checks;
    // +----------------------------------------------------------+
	function __construct( $config=NULL )
	{
        self::_init();
		if( !have_value( $config ) ) { self::$config_name = dirname( __FILE__ ) . '/dba.ini.php'; };
		//parent::__construct( $config );
		$this->id_name    = "contact_id";
		$this->dao_table  = static::$dao_table;
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
    static function _init() {
        static::$dao_table = 'contact100';
        static::$dao_pkey  = 'contact_id';
        static::$config_name = dirname( __FILE__ ) . '/dba.ini.php';
        static::setupColumns();
    }
	// +----------------------------------------------------------+
	static function setColumns()
	{
		self::$col_names = array(
			'contact_id' => 'コンタクトID',
			'contact_name' =>  '名前',
			'contact_gender' =>  '性別',
			'contact_type' =>  '分類',
			'contact_date' =>  '日付',
		);
		self::$col_checks = array(
			'contact_name' => 
				array( 'pushChar', PGG_VALUE_MUST_EXIST ), 
			'contact_gender' => 
				array( 'pushChar', PGG_VALUE_MUST_EXIST, sel_gender::$item_list ), 
			'contact_type' => 
				array( 'pushChar', PGG_VALUE_MUST_EXIST, sel_contact_type::$item_list ), 
			'contact_date' => 
				array( 'pushDate', PGG_VALUE_MUST_EXIST ), 
		);
		self::$col_sels = array(
			'contact_id' => 
				array( 'CenaDta\\Html\\formHidden', '', '', 'OFF' ),
			'contact_name' => 
				array( 'CenaDta\\Html\\formText', '15', '', 'ON' ),
			'contact_gender' => 
				array( 'sel_gender', '', '', 'OFF' ),
			'contact_type' => 
				array( 'sel_contact_type', '', '', 'OFF' ),
			'contact_date' => 
				array( 'CenaDta\\Html\\formText', '12', '12', 'OFF' ),
		);
	}
	// +----------------------------------------------------------+
	static function getChildship()
	{
		// $child[ model_name ] = array(
		//    ...
		// )
		$child = array();
		$child[ 'dao_contact110' ] = array( 
			'rec_model'    => 'dao_contact100', 
			'rec_column'   => NULL, 
			'child_model'  => 'dao_contact110', 
			'child_column' => 'contact_id', 
		);
		return $child;
	}
	// +----------------------------------------------------------+
	static function check_input_prep( &$pgg )
	{
		return $pgg->errGetNum();
	}
	// +----------------------------------------------------------+
	static function check_input( &$pgg )
	{
		/* Checking User Input */
		//$pgg->pushChar( 'contact_id',        PGG_VALUE_MISSING_OK, PGG_REG_NUMBER ); // コンタクトID
		self::checkInput( $pgg, 'contact_name' );
		self::checkInput( $pgg, 'contact_gender' );
		self::checkInput( $pgg, 'contact_type' );
		self::checkInput( $pgg, 'contact_date' );
		
		return $pgg->errGetNum();
	}
	// +----------------------------------------------------------+
	static function ignore_check_for_new( $data )
	{
		if( !have_value( $data[ 'contact_name' ] ) ) return TRUE;
		return FALSE;
	}
	// +----------------------------------------------------------+
}
?>