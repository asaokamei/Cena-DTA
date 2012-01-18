<?php
//define( 'WORDY', 4 );
require_once( dirname( __FILE__ ) . "/../../Html/Form.php" );
require_once( dirname( __FILE__ ) . "/../../Dba/Model.php" );
require_once( dirname( __FILE__ ) . "/../../Dba/Record.php" );
require_once( dirname( __FILE__ ) . "./dbaTest.inc.php" );

use CenaDta\Dba as orm;

class DbaDbaRec extends PHPUnit_Framework_TestCase
{
    // +--------------------------------------------------------------- +
	public function setUp() {
		$config = UT_SetUp_Contact::getDbaIniFile();
		$this->dao_contact = UT_dao_contact100::getInstance( $config );
		$this->dao_connect = UT_dao_contact110::getInstance( $config );
		$this->sql = new orm\Sql( $config );
		orm\DaoObjPools::clearInstance();
		orm\DaoObjPools::clearRecord();
	}
    // +--------------------------------------------------------------- +
	public function getDaoName( $idx=1 ) {
		if( $idx == 1 ) 
			return 'UT_dao_contact100';
		else
		if( $idx == 2 ) 
			return 'UT_dao_contact110';
	}
    // +--------------------------------------------------------------- +
	public function populateContact( $num=10 ) {
		$this->sql->execSQL( UT_SetUp_Contact::getDropContact() );
		$this->sql->execSQL( UT_SetUp_Contact::getCreateContact() );
		$this->sql->table(   UT_SetUp_Contact::getContactTable() );
		for( $i = 0; $i < $num; $i ++ ) 
			$this->sql->execInsert(  UT_SetUp_Contact::getContactData( $i ) );
	}
    // +--------------------------------------------------------------- +
	public function populateConnect( $num=10 ) {
		$this->sql->execSQL( UT_SetUp_Contact::getDropConnect() );
		$this->sql->execSQL( UT_SetUp_Contact::getCreateConnect() );
		$this->sql->table(   UT_SetUp_Contact::getConnectTable() );
		for( $i = 0; $i < $num; $i ++ ) {
			$data = UT_SetUp_Contact::getConnectData( $i );
			// wt( $data, "i:{$i}" );
			$this->sql->execInsert( $data );
		}
	}
    // +--------------------------------------------------------------- +
	//  testRelation():
	function testValidateRelation() 
	{
		$this->populateContact();
		$this->populateConnect();
		$dao1 = self::getDaoName(1); // contact dao
		$dao2 = self::getDaoName(2); // connect dao
		
		// ### test validate for lack of relation. 
		
		$new_id   = 1234; // new data. 
		$new_data = UT_SetUp_Contact::getConnectData( $new_id );
		unset( $new_data[ 'connect_type' ] ); // make sure it fails
		$connect = $dao2::getRecord( orm\Record::TYPE_NEW, $new_data );
		try {
			$connect->doValidate();
			$this->assertTrue( FALSE, 'validation success; must fail' );
		}
		catch( orm\DataInvalid_DbaRecord_Exception $e ) {
			$this->assertTrue( TRUE, 'validation failed; that is correct.' );
            goto SUCCESS1;
		}
		$this->assertTrue( FALSE, 'wrong exception throwed. ' );
		SUCCESS1:
	}
}


?>