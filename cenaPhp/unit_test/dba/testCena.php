<?php
define( 'WORDY', 0 );
require_once( dirname( __FILE__ ) . "/../../class/class.ext_func.php" );
require_once( dirname( __FILE__ ) . "/../../class/class.pgg_JPN.php" );
require_once( dirname( __FILE__ ) . "/../../Html/Form.php" );
require_once( dirname( __FILE__ ) . "/../../Dba/record.php" );
require_once( dirname( __FILE__ ) . "/../../Cena/master.php" );
require_once( dirname( __FILE__ ) . "/../../Cena/record.php" );
require_once( dirname( __FILE__ ) . "./dbaTest.inc.php" );
require_once( dirname( __FILE__ ) . "./dao.contact100b.php" );
require_once( dirname( __FILE__ ) . "./dao.contact110.php" );

use CenaDta\Dba as orm;
use CenaDta\Cena as cena;

class CenaRec extends PHPUnit_Framework_TestCase
{
    // +--------------------------------------------------------------- +
	public function setUp() {
		$config = realpath( dirname( __FILE__ ) . '/dbaTest.ini.php' );
		$this->dao_contact = dao_contact100::getInstance( $config );
		$this->dao_connect = dao_contact110::getInstance( $config );
		$this->sql = new orm\Sql( $config );
		orm\DaoObjPools::clearInstance();
		orm\DaoObjPools::clearRecord();
		cena\Cena::clearCenas();
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
    // +--------------------------------------------------------------- +
	function testBasics() 
	{
		$dao = self::getDaoName();
		$obj = $dao::getInstance();
		$this->populateContact();
		$id = 1;
		
		// ### Cena_Record basic functions. 
		
		// Cena using dao -> dba_record -> cena_record.
		$data = $obj->getDatum( $id );
		$rec1 = $dao::convertDataToRecord( $data );
		$cena = cena\Cena::getCenaByRec( $rec1 );
		$this->assertEquals( $cena->getId(), $id ); // id is the same. 
		
		// test object pooling of Cena master. 
		$model = $cena->getModel();
		$type  = $cena->getType();
		$id2   = $cena->getId();
		$cena2 = cena\Cena::getCena( $model, $type, $id2 );
		$this->assertTrue(  $cena ==  $cena2 ); // same. 
		$this->assertTrue(  $cena === $cena2 ); // identical.
		
		// more test on object pooling. 
		$cena_id = $cena->getCenaId();
		$cena3 = cena\Cena::getCenaByCenaId( $cena_id );
		
		$this->assertTrue(  $cena ==  $cena3 ); // same. 
		$this->assertTrue(  $cena === $cena3 ); // identical.
		
		// bit of testing on getCena, again. 
		$id4 = 2;
		$cena4 = cena\Cena::getCena( $model, $type, $id4 );
		$this->assertEquals( $cena4->getId(), $id4 ); // id is the same. 
		
		// test on verifyCenaId
		$cena_id = $cena->getCenaId();
		$this->assertTrue( cena\Cena::verifyCenaId( $cena_id ) ); // good
		$cena_id = $cena4->getCenaId();
		$this->assertTrue( cena\Cena::verifyCenaId( $cena_id ) ); // also good
		$this->assertFalse( cena\Cena::verifyCenaId( 'Cena.model.id' ) ); // bad form...
		
		// more test on getCenaByCenaId.
		$id5 = 3;
		$cena_id = $cena->getScheme() . ".{$model}.{$type}.{$id5}";
		$cena5 = cena\Cena::getCenaByCenaId( $cena_id );
		$this->assertEquals( $cena5->getCenaId(), $cena_id ); // id is the same. 
	}
    // +--------------------------------------------------------------- +
	//  testBasics2():
    // +--------------------------------------------------------------- +
	function testBasics2() 
	{
		$dao = self::getDaoName();
		$obj = $dao::getInstance();
		$this->populateContact();
		$id = 1;
		
		// ### test clearing cena object pool. 
		
		// Cena using dao -> dba_record -> cena_record.
		$data = $obj->getDatum( $id );
		$rec1 = $dao::convertDataToRecord( $data );
		$cena = cena\Cena::getCenaByRec( $rec1 );
		$this->assertEquals( $cena->getId(), $id ); // id is the same. 
		
		cena\Cena::clearCenas();
		
		// test object pooling of Cena master. 
		$model = $cena->getModel();
		$type  = $cena->getType();
		$id2   = $cena->getId();
		$cena2 = cena\Cena::getCena( $model, $type, $id2 );
		$this->assertTrue(  $cena ==  $cena2 ); // same. 
		$this->assertFalse( $cena === $cena2 ); // identical.
		
	}
    // +--------------------------------------------------------------- +
	//  testGetSet():
    // +--------------------------------------------------------------- +
	function testGetSet() 
	{
		$dao = self::getDaoName();
		$obj = $dao::getInstance();
		$this->populateContact();
		$id = 1;
		
		// ### test get/set 
		
		$data = $obj->getDatum( $id );
		$rec1 = $dao::convertDataToRecord( $data );
		$cena = cena\Cena::getCenaByRec( $rec1 );
		
		$name  = 'contact_name';
		$value = $cena->getData( $name );
		
		// set $name property using do_cena/post input. 
		
		$input     = array(); // this mimics the post input. 
		$test      = 'test this name';
		$cena_name = $cena->getCenaName( cena\Record::ACT_SET, $name );
		parse_str( "$cena_name=$test", $input ); 
		cena\Cena::do_cena( $records, 'doAction', $input );
		
		// check the cena object's value. 
		$name2 = $records['dao_contact100'][0]->getData( $name );
		$this->assertEquals( $test, $name2 ); // same name. 
		// check the database value. 
		$data2 = $obj->getDatum( $id );
		$this->assertEquals( $test, $data2[ $name ] ); // same name. 
		
		// ### test validation
		
		$input     = array(); // this mimics the post input. 
		$test2     = '';      // empty name.  this must be an error. 
		$cena_name = $cena->getCenaName( cena\Record::ACT_SET, $name );
		parse_str( "$cena_name=$test2", $input ); 
		cena\Cena::do_cena( $records, 'doAction', $input );
		
		// check the cena object's value. 
		$name2 = $records['dao_contact100'][0]->getData( $name );
		echo $records['dao_contact100'][0]->err_msg;
		$this->assertEquals( $test2, $name2 ); // same name. 
		
		// check the database value. 
		$data2 = $obj->getDatum( $id );
		$this->assertNotEquals( $test2, $data2[ $name ] ); // not overwritten with ''. 
		$this->assertEquals(    $test,  $data2[ $name ] ); // same as before. 
		
	}
    // +--------------------------------------------------------------- +
	//  testRelPost():
    // +--------------------------------------------------------------- +
	function testRelPost() 
	{
		$max  = 10;
		$dao1 = self::getDaoName();
		$obj1 = $dao1::getInstance();
		$dao2 = self::getDaoName(2);
		$obj2 = $dao2::getInstance();
		$this->populateContact( $max );
		$this->populateConnect( $max );
		$connect_id1 = 1;
		$connect_id2 = 2;
		
		// ### test relations
		
		$connect1 = cena\Cena::getCena( $dao2, orm\Record::TYPE_GET, $connect_id1 );
		$connect2 = cena\Cena::getCena( $dao2, orm\Record::TYPE_GET, $connect_id2 );
		$connect1->loadRelation();
		$connect2->loadRelation();
		
		$rel1 = $connect1->getRelation();
		$rel2 = $connect2->getRelation();
		
		$this->assertTrue( !empty( $rel1[ 'contact_id' ] ) ); // have relation. 
		$this->assertTrue( !empty( $rel2[ 'contact_id' ] ) ); // have relation. 
		
		$contact1 = cena\Cena::getCenaByCenaId( $rel1[ 'contact_id' ] );
		$contact2 = cena\Cena::getCenaByCenaId( $rel2[ 'contact_id' ] );
		$child1 = $contact1->getChildren();
		
		$this->assertTrue( empty( $child1 ) ); // need to load, first. 
		
		$contact1->loadChildren();
		$contact2->loadChildren();
		
		$child1 = $contact1->getChildren();
		$child2 = $contact2->getChildren();
		$this->assertContains( $connect1->getCenaId(), $child1[ 'dao_contact110' ] );
		$this->assertContains( $connect2->getCenaId(), $child2[ 'dao_contact110' ] );
		
		// ### test setRelation (rel) method. 
		
		// first of all, make sure $connect2 and $contact1 are not related. 
		$cena_id1 = $contact1->getCenaId();
		$this->assertNotContains( $connect2->getCenaId(), $child1[ 'dao_contact110' ] );
		
		// relate $connect2 to $contact1. and save it. 
		$cena_name = $connect2->getCenaName( cena\Record::ACT_REL, 'contact_id' );
		//echo "$cena_name=$cena_id1";
		parse_str( "$cena_name=$cena_id1", $input ); 
		cena\Cena::do_cena( $records, 'doAction', $input );
		
		// re-read all $contact1 if it has $connect2 as children. 
		cena\Cena::clearCenas();
		$new_contact1 = cena\Cena::getCenaByCenaId( $cena_id1 );
		$new_contact1->loadChildren();
		$new_child1 = $new_contact1->getChildren();
		//wtc( $new_child1 );
		$this->assertContains( $connect2->getCenaId(), $new_child1[ 'dao_contact110' ] );
		
	}
    // +--------------------------------------------------------------- +
	//  testRelNew():
    // +--------------------------------------------------------------- +
	function testRelNew() 
	{
		$max  = 10;
		$dao1 = self::getDaoName();
		$obj1 = $dao1::getInstance();
		$dao2 = self::getDaoName(2);
		$obj2 = $dao2::getInstance();
		$this->populateContact( $max );
		$this->populateConnect( $max );
		$connect_id1 = 1;
		$connect_id2 = 2;
		
		// ### test New record with relation. 
		
		// create new contact data record ($rec1).
		$id1   = $max + 1;
		$data1 = SetUp_Contact::getContactData( $id1 );
		$rec1  = $dao1::getNewRecord( $data1 );
		$cena1 = cena\Cena::getCenaByRec( $rec1 ); 
		
		// create new connect data record ($rec2).
		$id2   = $max + 1;
		$data2 = SetUp_Contact::getConnectData( $id2 );
		$rec2  = $dao2::getNewRecord( $data2 );
		$cena2 = cena\Cena::getCenaByRec( $rec2 );
		
		// construct post data from the data records ($rec1 & $rec2).
		$args  = array();
		foreach( $data1 as $column => $value ) {
			$cena_name = $cena1->getCenaName( 'set', $column );
			$args[] = "$cena_name=$value\n";
		}
		$cena_body = cena\Cena::makeCenaName( $dao2, orm\Record::TYPE_NEW, $id2 );
		foreach( $data2 as $column => $value ) {
			if( $column == 'contact_id' ) {
				$value = $cena1->getCenaId();
				$cena_name = $cena2->getCenaName( 'rel', $column );
			}
			else {
				$cena_name = $cena2->getCenaName( 'set', $column );
			}
			$args[] = "$cena_name=$value\n";
		}
		$args = implode( '&', $args );
		parse_str( $args, $input ); // got it. 
		// wtc( $input, 'input' );
		
		// perform Cena's doAction. 
		cena\Cena::do_cena( $records, 'doAction', $input );
		// Record::linkNow();
		//wtc( $records, 'records' );
		
		// now saved to database. reload the Cena_Records, and do some checks.
		$cena1 = $records[ $dao1 ][0];
		$cena2 = $records[ $dao2 ][0];
		
		$this->assertEquals( $data1[ 'contact_name' ], $cena1->getData( 'contact_name' ) );
		$this->assertEquals( $data2[ 'connect_info' ], $cena2->getData( 'connect_info' ) );
		
		$cena2->loadRelation();
		$relate1 = $cena2->getRelation();
		//wtc( $relate1, 'relate1' );
		$this->assertEquals( $relate1[ 'contact_id' ], $cena1->getCenaId() );
		
		// ### test setRelation
		
		// get id3 for contact. 
		$id3 = 2;
		$rec3 = $dao1::getRecord( orm\Record::TYPE_GET, $id3 );
		$cena3 = cena\Cena::getCenaByRec( $rec3 );
		
		// set the relationship. 
		$cena2->setRelation( 'contact_id', $cena3->getCenaId() );
		//echo 'check1: ', $cena2->getRelation( 'contact_id' ) . '<br />';
		//echo 'check1: ', $cena2->record . '<br />';
		
		// save the new record ($cena3). 
		$cena2->record->doAction(); 
		$cena2->loadRelation();
		$rel_cena3 = $cena2->getRelation( 'contact_id' );
		$this->assertEquals( $rel_cena3, $cena3->getCenaId() );
	}
    // +--------------------------------------------------------------- +
	//  testRelationAndChildren():
    // +--------------------------------------------------------------- +
	function testRelationAndChildren() 
	{
		$max  = 10;
		$dao1 = self::getDaoName();
		$obj1 = $dao1::getInstance();
		$dao2 = self::getDaoName(2);
		$obj2 = $dao2::getInstance();
		$this->populateContact( $max );
		$this->populateConnect( $max );
		$contact_id1 = 1;
		$connect_id2 = 2;
		
		// ### test {get|set}{Relation|Children} of Cena_Record.
		
		$cena1  = cena\Cena::getCena( $dao1, orm\Record::TYPE_GET, $contact_id1 );
		$cena1->loadChildren();
		$children = $cena1->getChildren();
		// wtc( $children, 'children of cena1' . $cena1 );
		
		// make sure $connect_id2 is NOT in the children. 
		$child2 = cena\Cena::getCena( $dao2, orm\Record::TYPE_GET, $connect_id2 );
		$this->assertNotContains( $child2->getCenaId(), $children[ 'dao_contact110' ] );
		
		// now make child2 as children of cena1. 
		$cena1->setChildren( 'dao_contact110', $child2->getCenaId() );
		$child2->record->doAction(); // save it. 
		
		// relaod children, and check.
		$cena1->loadChildren();
		$children = $cena1->getChildren();
		// wtc( $children, 'children of cena1' . $cena1 );
		$this->assertContains( $child2->getCenaId(), $children[ 'dao_contact110' ] );
	}
    // +--------------------------------------------------------------- +
}


?>