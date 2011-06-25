<?php
//define( 'WORDY', 4 );
require_once( dirname( __FILE__ ) . "/../../class/class.ext_func.php" );
require_once( dirname( __FILE__ ) . "/../../class/class.pgg_JPN.php" );
require_once( dirname( __FILE__ ) . "/../../Html/Form.php" );
require_once( dirname( __FILE__ ) . "/../../Dba/Model.php" );
require_once( dirname( __FILE__ ) . "/../../Dba/Record.php" );
require_once( dirname( __FILE__ ) . "./dbaTest.inc.php" );
require_once( dirname( __FILE__ ) . "./dao.contact100b.php" );
require_once( dirname( __FILE__ ) . "./dao.contact110.php" );

use CenaDta\Dba as orm;

class DbaDbaRec extends PHPUnit_Framework_TestCase
{
    // +--------------------------------------------------------------- +
	public function setUp() {
		$config = realpath( dirname( __FILE__ ) . '/dbaTest.ini.php' );
		$this->dao_contact = dao_contact100::getInstance( $config );
		$this->dao_connect = dao_contact110::getInstance( $config );
		$this->sql = new orm\Dba_Sql( $config );
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
	//  testBasics():
	//  to test basic functions of Dba_Record.
    // +--------------------------------------------------------------- +
	function testBasics() 
	{
		$dao = self::getDaoName();
		$this->populateContact();
		$id = 1;
		
		// ### Dba_Record basic functions. 
		
		$rec1   = $dao::getRecord( orm\Dba_Record::TYPE_GET, $id );
		$this->assertEquals( $rec1->getModel(), $dao );
		$this->assertEquals( $rec1->getType() , orm\Dba_Record::TYPE_GET );
		$this->assertEquals( $rec1->getId(),    $id );
		
		// ### test if popHtml returns html forms.
		
		$name  = 'contact_name';
		$text  = $rec1->get( $name );
		$elem  = $rec1->popHtml( $name, 'EDIT' );
		$this->assertContains( " type=\"text\"",        $elem );
		$this->assertContains( " name=\"{$name}\"",     $elem );
		$this->assertContains( " value=\"{$text}\"",    $elem );
		
	}
    // +--------------------------------------------------------------- +
	//  testObjectPooling():
    // +--------------------------------------------------------------- +
	function testObjectPooling() 
	{
		$dao = self::getDaoName();
		$this->populateContact();
		$id = 1;
		
		// ### Dba_Record object pooling. 
		
		// get the same record twice. 
		$rec1 = $dao::getRecord( orm\Dba_Record::TYPE_GET, $id );
		$rec2 = $dao::getRecord( orm\Dba_Record::TYPE_GET, $id );
		// they must be *identical* because of object pooling. 
		$this->assertTrue( $rec1 ==  $rec2 ); // same.
		$this->assertTrue( $rec1 === $rec2 ); // identical. 
		$this->assertEquals( $rec1->getId(), $id ); // id is the same. 
		
		// ### Dba_Record from existing data 
		
		// get the record from data. 
		$data = $this->dao_contact->getDatum( $id );
		$rec3 = $dao::convertDataToRecord( $data );
		// same data, and convertDataToRecord pools the object.
        // this behavior changed from Cena 0.3. 
		$this->assertTrue( $rec3 ==  $rec1 ); // same. 
		$this->assertTrue( $rec3 === $rec1 ); // identical.
		// however, the record object is pooled. 
		$rec4 = $dao::getRecord( orm\Dba_Record::TYPE_GET, $id );
		$this->assertTrue( $rec3 ==  $rec4 ); // same.
		$this->assertTrue( $rec3 === $rec4 ); // identical. 
		
		// ### test creating new records
		
		$id   = 2;
		$obj  = $dao::getInstance();
		$new1 = new orm\Dba_Record( $dao, $id, orm\Dba_Record::TYPE_NEW );
		$new2 = new orm\Dba_Record( $obj, $id, orm\Dba_Record::TYPE_NEW );
		$this->assertTrue(  $new1 ==  $new2 ); // same.
		$this->assertFalse( $new1 === $new2 ); // not identical. 
		// but id and types must be the same. 
		$this->assertEquals( $new1->getId(),   $id );
		$this->assertEquals( $new1->getType(), orm\Dba_Record::TYPE_NEW );
		
		// check automatic new id is working. 
		$new3 = new orm\Dba_Record( $dao );
		$new3->initNewRecord();
		$new4 = new orm\Dba_Record( $dao );
		$new4->initNewRecord();
		$this->assertNotEquals( $new3->getId(), $new4->getId() );
		
		// check for new record based on existing data. 
		$name = 'test #rec';
		$data = array( 'contact_name' => $name );
		$new5 = new orm\Dba_Record( $dao, $data, orm\Dba_Record::TYPE_NEW );
		$this->assertEquals( $new5->get( 'contact_name' ),   $name );
		$this->assertNotEquals( $new4->getId(), $new5->getId() );
		
		// new record on existing data with id specified. 
		$id = 'test new id';
		$data[ $obj->getIdName() ] = $id;
		$new6 = new orm\Dba_Record( $dao, $data, orm\Dba_Record::TYPE_NEW );
		$this->assertEquals( $new6->get( 'contact_name' ),   $name );
		$this->assertEquals( $new6->getId(),                 $id );
	}
    // +--------------------------------------------------------------- +
	//  testGetSetData():
    // +--------------------------------------------------------------- +
	function testGetSetData() 
	{
		$dao = self::getDaoName();
		$obj = $dao::getInstance();
		$this->populateContact();
		$id = 1;
		
		// ### test get/set. 
		
		// get the record and data. 
		$data = SetUp_Contact::getContactData( $id - 1 );
		$rec1 = $dao::getRecord( orm\Dba_Record::TYPE_GET, $id );
		$this->assertEquals( $rec1->get( 'contact_name' ),   $data[ 'contact_name' ] );
		
		// set contact name with other name.
		$name = 'test rec name';
		$rec1->set( 'contact_name', $name );
		$rec1->doAction();
		
		$data2 = $obj->getDatum( $id );
		$this->assertEquals( $name,   $data2[ 'contact_name' ] );
		
	}
    // +--------------------------------------------------------------- +
	//  testValidation():
    // +--------------------------------------------------------------- +
	function testValidationTypeGet() 
	{
		$dao = self::getDaoName();
		$obj = $dao::getInstance();
		$this->populateContact();
		$id = 2;
		
		// get the record and data. 
		$data = SetUp_Contact::getContactData( $id - 1 );
		$rec1 = $dao::getRecord( orm\Dba_Record::TYPE_GET, $id );
		$this->assertEquals( $rec1->get( 'contact_name' ),   $data[ 'contact_name' ] );
		
		// ### test validation
		
		// make sure contact_name and contact_type are required. 
		$dao::$col_checks[ 'contact_name' ][1] = PGG_VALUE_MUST_EXIST;
		$dao::$col_checks[ 'contact_type' ][1] = PGG_VALUE_MUST_EXIST;
		
		$name = 'some value';
		$rec1->set( 'contact_name', $name );
		// the doValidation must be successful; 
		try {
			$rec1->doValidate();
			$this->assertTrue( TRUE ); // correct behavior. 
		}
		catch( Exception $e ) {
			$this->assertTrue( FALSE ); // should not throw any other exception. 
		}
		
		$name = '';
		$rec1->set( 'contact_name', $name );
		// the doValidation must fail; contact_name is required. 
		try {
			$rec1->doValidate();
			$this->assertTrue( FALSE ); // should not come here...
		}
		catch( orm\DataInvalid_DbaRecord_Exception $e ) {
			$this->assertTrue( TRUE ); // correct behavior. 
		}
		catch( Exception $e ) {
			$this->assertTrue( FALSE ); // should not throw any other exception. 
		}
		
		$date = 'bad date';
		$name = 'some value';
		$rec1->set( 'contact_name', $name );
		$rec1->set( 'contact_date', $date );
		// the doValidation must fail; contact_date is in wrong format. 
		try {
			$rec1->doValidate();
			$this->assertTrue( FALSE ); // should not come here...
		}
		catch( orm\DataInvalid_DbaRecord_Exception $e ) {
			$this->assertTrue( TRUE ); // correct behavior. 
		}
		catch( Exception $e ) {
			$this->assertTrue( FALSE ); // should not throw any other exception. 
		}
		
	}
    // +--------------------------------------------------------------- +
	//  testValidation():
    // +--------------------------------------------------------------- +
	function testValidationTypeNew() 
	{
		$dao = self::getDaoName();
		
		// ### test new data validation
		
		// unlike TYPE_GET, this should fail because other items are not 
		$new1 = new orm\Dba_Record( $dao, $data, orm\Dba_Record::TYPE_NEW );
		$name = 'test rec name';
		$new1->set( 'contact_name', $name );
		try {
			$new1->doValidate();
			$this->assertTrue( FALSE ); // should not come here...
		}
		catch( orm\DataInvalid_DbaRecord_Exception $e ) {
			$this->assertTrue( TRUE ); // correct behavior. 
		}
		catch( Exception $e ) {
			$this->assertTrue( FALSE ); // should not throw any other exception. 
		}
		
		$new1->set( 'contact_name', $name );
		$new1->set( 'contact_gender', '1' );
		$new1->set( 'contact_type', '1' );
		$new1->set( 'contact_date', date( 'Y-m-d' ) );
		// the doValidation must be successful; 
		try {
			$new1->doValidate();
			$this->assertTrue( TRUE ); // correct behavior. 
		}
		catch( Exception $e ) {
			$this->assertTrue( FALSE ); // should not throw any other exception. 
		}
	}
    // +--------------------------------------------------------------- +
	//  testValidation():
    // +--------------------------------------------------------------- +
	function testCleanInstances() 
	{
		$dao = self::getDaoName();
		$this->populateContact();
		$id = 1;
		
		// ### Dba_Dao object pooling. 
		
		orm\Dba_DaoObjPools::clearInstance();
		$obj1 = $dao::getInstance();
		$obj2 = $dao::getInstance();
		$this->assertTrue(   $obj1 ==  $obj2 ); // same.
		$this->assertTrue(   $obj1 === $obj2 ); // identical. 
		
		orm\Dba_DaoObjPools::clearInstance();
		$obj3 = $dao::getInstance();
		$this->assertTrue(   $obj1 ==  $obj3 ); // same.
		$this->assertFalse(  $obj1 === $obj3 ); // not identical. 
		
		// ### Dba_Record object pooling. 
		
		orm\Dba_DaoObjPools::clearRecord();
		// get the same record twice. 
		$rec1 = $dao::getRecord( orm\Dba_Record::TYPE_GET, $id );
		$rec2 = $dao::getRecord( orm\Dba_Record::TYPE_GET, $id );
		// they must be *identical* because of object pooling. 
		$this->assertTrue(   $rec1 ==  $rec2 ); // same.
		$this->assertTrue(   $rec1 === $rec2 ); // identical. 
		$this->assertEquals( $rec1->getId(), $id ); // id is the same. 
		
		orm\Dba_DaoObjPools::clearRecord();
		$rec3 = $dao::getRecord( orm\Dba_Record::TYPE_GET, $id );
		// they are not *identical* after clean up the object pooling. 
		$this->assertTrue(   $rec3 ==  $rec2 ); // same.
		$this->assertFalse(  $rec3 === $rec2 ); // not identical. 
		$this->assertEquals( $rec3->getId(), $id ); // id is the same. 
		
	}
    // +--------------------------------------------------------------- +
	//  testRelation():
    // +--------------------------------------------------------------- +
	function testRelation() 
	{
		$this->populateContact();
		$this->populateConnect();
		$dao1 = self::getDaoName(1); // contact dao
		$dao2 = self::getDaoName(2); // connect dao
		
		$connect_id = 1;
		$connect = $dao2::getRecord( orm\Dba_Record::TYPE_GET, $connect_id );
		$connect->loadRelation();
		$contact1 = $connect->getRelation( 'contact_id' );
		$contact1_id = $contact1->getId();
		
		// ### test setting new relation.
		
		$contact2_id = $contact1_id + 1; 
		$contact2 = $dao1::getRecord( orm\Dba_Record::TYPE_GET, $contact2_id );
		$connect->setRelation( 'contact_id', $contact2 );
		$connect->doAction();
		
		orm\Dba_DaoObjPools::clearRecord();
		
		$connect2 = $dao2::getRecord( orm\Dba_Record::TYPE_GET, $connect_id );
		$connect2->loadRelation();
		$contact3 = $connect2->getRelation( 'contact_id' );
		$contact3_id = $contact3->getId();
		$this->assertEquals(    $contact3_id, $contact2_id ); // id of the new contact.
		$this->assertNotEquals( $contact3_id, $contact1_id ); // not the original id. 
	}
    // +--------------------------------------------------------------- +
	//  testLateLinkRelation():
    // +--------------------------------------------------------------- +
	function testLateLinkRelation() 
	{
		$this->populateContact();
		$this->populateConnect();
		$dao1 = self::getDaoName(1); // contact dao
		$dao2 = self::getDaoName(2); // connect dao
		
		orm\Dba_DaoObjPools::clearRecord();
		
		$connect_id = 1;
		$connect = $dao2::getRecord( orm\Dba_Record::TYPE_GET, $connect_id );
		$connect->loadRelation();
		$contact_old = $connect->getRelation( 'contact_id' );
		
		// ### test late linking
		
		$new_id   = 1234; // new data. 
		$new_data = SetUp_Contact::getContactData( $new_id );
		$new_contact = new orm\Dba_Record( $dao1 );
		$new_contact->initNewRecord();
		$new_contact->set( $new_data );
		
		// link to none-saved new contact data. 
		$connect->setRelation( 'contact_id', $new_contact ); 
		$connect->doAction(); // not linked, yet!
		
		$new_contact->doAction();
		orm\Dba_Record::linkNow();
		
		// read the connect, again. 
		orm\Dba_DaoObjPools::clearRecord();
		$connect = $dao2::getRecord( orm\Dba_Record::TYPE_GET, $connect_id );
		$connect->loadRelation();
		$new_contact2 = $connect->getRelation( 'contact_id' );
		
		$contact_old_id  = $contact_old->getId();
		$contact_id2     = $new_contact2->getId();
		$this->assertNotEquals( $old_contact_id, $contact_id2 ); // not the original id. 
		
		$name = 'contact_name';
		$this->assertEquals( $new_contact2->get( $name ), $new_data[ $name ] );
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
		$contact = $dao1::getRecord( orm\Dba_Record::TYPE_GET, $contact_id );
		$contact->loadChildren();
		$children = $contact->getChildren();
		$this->assertTrue( count( $children ) > 0 ); // make sure child data exist. 
		
		// ### test setting children i.e. set relation in child record.
		
		// create new connect record. 
		$new_id   = 123; // new data. 
		$new_data = SetUp_Contact::getConnectData( $new_id );
		$new_connect = new orm\Dba_Record( $dao2 );
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
    // +--------------------------------------------------------------- +
	//  testValidateRelation():
    // +--------------------------------------------------------------- +
	function testValidateRelation() 
	{
		$this->populateContact();
		$this->populateConnect();
		$dao1 = self::getDaoName(1); // contact dao
		$dao2 = self::getDaoName(2); // connect dao
		
		// ### test validate for lack of relation. 
		
		$new_id   = 1234; // new data. 
		$new_data = SetUp_Contact::getConnectData( $new_id );
		unset( $new_data[ 'contact_id' ] ); // make sure it fails
		$connect = $dao2::getRecord( orm\Dba_Record::TYPE_NEW, $new_data );
		try {
			$connect->doValidate();
			$this->assertTrue( FALSE, 'validation success; must fail' );
		}
		catch( orm\DataInvalid_DbaRecord_Exception $e ) {
			$this->assertTrue( TRUE, 'validation failed; that is correct.' );
		}
		catch( Exception $e ) {
			$this->assertTrue( FALSE, 'wrong exception throwed. ' );
		}
		
		// ### test validate for relation in input_data. 
		
		$new_id   = 1234; // new data. 
		$new_data = SetUp_Contact::getConnectData( $new_id );
		$connect = $dao2::getRecord( orm\Dba_Record::TYPE_NEW, $new_data );
		try {
			$connect->doValidate();
		}
		catch( Exception $e ) {
			$this->assertTrue( FALSE, 'validation failed; must be OK' );
		}
		
		// ### test validate for relation in setRelation. 
		
		$new_id   = 1234; // new data. 
		$new_data = SetUp_Contact::getConnectData( $new_id );
		unset( $new_data[ 'contact_id' ] ); // make sure it fails
		$contact = $dao2::getRecord( orm\Dba_Record::TYPE_GET, 1 );
		$connect = $dao2::getRecord( orm\Dba_Record::TYPE_NEW, $new_data );
		$connect->setRelation( 'contact_id', $contact );
		try {
			$connect->doValidate();
		}
		catch( Exception $e ) {
			$this->assertTrue( FALSE, 'validation failed; must be OK' );
		}
	}
    // +--------------------------------------------------------------- +
}


?>