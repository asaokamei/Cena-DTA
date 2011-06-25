<?php
//define( 'WORDY', 4 );
require_once( dirname( __FILE__ ) . "/../../php_lib/class/class.ext_func.php" );
require_once( dirname( __FILE__ ) . "/../../php_lib/class/class.pgg_JPN.php" );
require_once( dirname( __FILE__ ) . "/../../php_lib/class5/class.htmlForm.php" );
require_once( dirname( __FILE__ ) . "/../../php_lib/dba/dba.model.php" );
require_once( dirname( __FILE__ ) . "/../../php_lib/dba/dba.record.php" );
require_once( dirname( __FILE__ ) . "./dbaTest.inc.php" );
require_once( dirname( __FILE__ ) . "./dao.contact100b.php" );
require_once( dirname( __FILE__ ) . "./dao.contact110.php" );
use cena\dba         as orm;
use cena\dba\Dba_Sql as ormSql;

class DbaDbaRec extends PHPUnit_Framework_TestCase
{
    // +--------------------------------------------------------------- +
	public function setUp() {
		$config = realpath( dirname( __FILE__ ) . '/dbaTest.ini.php' );
		$this->dao_contact = dao_contact100::getInstance( $config );
		$this->dao_connect = dao_contact110::getInstance( $config );
		$this->sql = new ormSql( $config );
		orm\Dba_DaoObjPools::clearInstance();
		orm\Dba_DaoObjPools::clearRecord();
	}
    // +--------------------------------------------------------------- +
	public function getDaoName( $idx=1 ) {
		if( $idx == 1 ) 
			return 'dao_contact100';
		else
		if( $idx == 2 ) 
			return 'dao_contact110';
	}
    // +--------------------------------------------------------------- +
	public function populateContact( $num=10 ) {
		$this->sql->execSQL( SetUp_Contact::getDropContact() );
		$this->sql->execSQL( SetUp_Contact::getCreateContact() );
		$this->sql->table(   SetUp_Contact::getContactTable() );
		for( $i = 0; $i < $num; $i ++ ) 
			$this->sql->execInsert(  SetUp_Contact::getContactData( $i ) );
	}
    // +--------------------------------------------------------------- +
	public function populateConnect( $num=10 ) {
		$this->sql->execSQL( SetUp_Contact::getDropConnect() );
		$this->sql->execSQL( SetUp_Contact::getCreateConnect() );
		$this->sql->table(   SetUp_Contact::getConnectTable() );
		for( $i = 0; $i < $num; $i ++ ) {
			$data = SetUp_Contact::getConnectData( $i );
			// wt( $data, "i:{$i}" );
			$this->sql->execInsert( $data );
		}
	}
    // +--------------------------------------------------------------- +
	//  testChildren():
    // +--------------------------------------------------------------- +
	function testChildren()
	{
		$this->populateContact();
		$this->populateConnect();
		$dao1 = self::getDaoName(1); // contact dao
		$dao2 = self::getDaoName(2); // connect dao

		$contact_id = 1;
		$contact = $dao1::getRecord( Dba_Record::TYPE_GET, $contact_id );
		$contact->loadChildren();
		$children = $contact->getChildren();
		$this->assertTrue( count( $children ) > 0 ); // make sure child data exist.

		// ### test setting children i.e. set relation in child record.

		// create new connect record.
		$new_id   = 123; // new data.
		$new_data = SetUp_Contact::getConnectData( $new_id );
		$new_connect = new Dba_Record( $dao2 );
		$new_connect->initNewRecord();
		$new_connect->set( $new_data );
		// set as children of contact, and save.
		$contact->setChildren( $dao2, $new_connect );
		$new_connect->doAction();
		// load relation of the connect, which should be the original contact.
		$new_connect->loadRelation();
		$new_contact = $new_connect->getRelation( 'contact_id' );
		$this->assertTrue( $contact ==  $new_contact ); // same.
		$this->assertTrue( $contact === $new_contact ); // identical.
	}
}


?>