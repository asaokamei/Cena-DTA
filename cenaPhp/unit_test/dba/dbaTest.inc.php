<?php
//require_once( dirname( __FILE__ ) . "/../../php_lib/class5/class.util.php" );
use CenaDta\Html as html;

require_once( dirname( __FILE__ ) . "/../../class/class.ext_func.php" );
require_once( dirname( __FILE__ ) . "/../../class/class.pgg_JPN.php" );
require_once( dirname( __FILE__ ) . "/../../Html/Form.php" );

$demo_folder = realpath( dirname( __FILE__ ) . '/../../../demo/' );
require_once( $demo_folder . '/lib_contact_code.php' );
require_once( $demo_folder . '/setup_contact.php' );
require_once( $demo_folder . '/dao.contact100.php' );
require_once( $demo_folder . '/dao.contact110.php' );

define( 'DBA_INI_FILE', $demo_folder . '/dba.ini.php' ); 

class UT_SetUp_Contact extends SetUp_Contact 
{
    // +--------------------------------------------------------------- +
	static function getContactTable() {
		return 'ut_contact100';
	}
    // +--------------------------------------------------------------- +
	static function getConnectTable() {
		return 'ut_contact110';
	}
    // +--------------------------------------------------------------- +
	static function getDbaIniFile() {
		return DBA_INI_FILE;
	}
}

class UT_dao_contact100 extends dao_contact100
{
	static $dao_table = "ut_contact100"; // "the" table name. 
    static $config_name = DBA_INI_FILE;
	// +----------------------------------------------------------+
	function __construct( $config=NULL )
	{
		$config = UT_SetUp_Contact::getDbaIniFile();
        parent::__construct( $config );
    }
	// +----------------------------------------------------------+
	static function getChildship()
	{
		// $child[ model_name ] = array(
		//    ...
		// )
		$child = array();
		$child[ 'UT_dao_contact110' ] = array( 
			'rec_model'    => 'UT_dao_contact100', 
			'rec_column'   => NULL, 
			'child_model'  => 'UT_dao_contact110', 
			'child_column' => 'contact_id', 
		);
		return $child;
	}
	// +----------------------------------------------------------+
}

class UT_dao_contact110 extends dao_contact110
{
	static $dao_table = "ut_contact110"; // "the" table name. 
    static $config_name = DBA_INI_FILE;
	// +----------------------------------------------------------+
	function __construct( $config=NULL )
	{
		$config = UT_SetUp_Contact::getDbaIniFile();
        parent::__construct( $config );
    }
	// +----------------------------------------------------------+
	static function getRelationship()
	{
		$relate = array();
		$relate[ 'contact_id' ] =	array( 
			'rec_model'  => 'UT_dao_contact110', 
			'rel_model'  => 'UT_dao_contact100', 
			'rec_column' => 'contact_id', 
			'rel_column' => NULL
		);
		return $relate;
	}
	// +----------------------------------------------------------+
}


?>