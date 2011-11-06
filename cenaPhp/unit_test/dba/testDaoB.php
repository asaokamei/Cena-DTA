<?php
//define( 'WORDY', 4 );
require_once( dirname( __FILE__ ) . "/../../Dba/Dao.php" );
require_once( dirname( __FILE__ ) . "/dbaTest.inc.php" );
use CenaDta\Dba as orm;

class DbaDaoTest extends PHPUnit_Framework_TestCase
{
    // +--------------------------------------------------------------- +
	public function setUp() {
		$config = UT_SetUp_Contact::getDbaIniFile();
		$this->dao_contact = UT_dao_contact100::getInstance( $config );
		$this->dao_connect = UT_dao_contact110::getInstance( $config );
		$this->sql = new orm\Sql( $config );
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
	//  testBasics():
	//  to test basic functions of DAO.
	//  namely, addDatum, modDatum, and getDatum.
    // +--------------------------------------------------------------- +
	function testBasics() 
	{
		$num = 10;
		$this->populateContact( $num ); // added 10 contacts
		$this->populateConnect( $num );
		$this->assertEquals( $this->dao_contact->getIdName(), 'contact_id' );
		$this->assertEquals( $this->dao_connect->getIdName(), 'connect_id' );
		
		// ### test tableName
		$table1 = UT_dao_contact100::tableName();
		$table2 = $this->dao_contact->table;
		$this->assertEquals( $table1, $table2 );
		$this->assertEquals( $table1, $this->dao_contact->tableName );
		
		$table1 = UT_dao_contact110::tableName();
		$table2 = $this->dao_connect->table;
		$this->assertEquals( $table1, $table2 );
		
		// ### test getDatum
		$data = $this->dao_contact->getDatum( 1 );
		$name = UT_SetUp_Contact::getContactName( 0 );
		$this->assertEquals( $name, $data[ 'contact_name' ] );
		
		// ### test addDatum
		$data1 = UT_SetUp_Contact::getContactData( 11 );
		$id    = $this->dao_contact->addDatum( $data1 );
		$data2 = $this->dao_contact->getDatum( $id );
		$this->assertEquals( $data1, $data2 );
		
		// ### test modDatum
		$name = 'test modDatum test';
		$vals = array( 'contact_name' => $name );
		$this->dao_contact->modDatum( $id, $vals );
		$data2 = $this->dao_contact->getDatum( $id );
		$this->assertEquals( $name, $data2[ 'contact_name' ] );
		
		// test dao_connect
		
		// ### test getDatum
		$data = $this->dao_connect->getDatum( 1 );
		$info = UT_SetUp_Contact::getConnectData( 0 );
		$this->assertEquals( $info[ 'connect_info' ], $data[ 'connect_info' ] );
		
		// ### test addDatum
		$data1 = UT_SetUp_Contact::getConnectData( 11 );
		$id    = $this->dao_connect->addDatum( $data1 );
		$data2 = $this->dao_connect->getDatum( $id );
		$this->assertEquals( $data1, $data2 );
		
		// ### test modDatum
		$name = 'test modDatum test2';
		$vals = array( 'connect_info' => $name );
		$this->dao_connect->modDatum( $id, $vals );
		$data2 = $this->dao_connect->getDatum( $id );
		$this->assertEquals( $name, $data2[ 'connect_info' ] );
	}
    // +--------------------------------------------------------------- +
	//  testSqlAdd():
	//  to test DAO behaving Dba_Sql module. 
	//  this test is identical to testSql::testAdd()
    // +--------------------------------------------------------------- +
	function testSqlAdd() 
	{
		$num = 10;
		$this->populateContact( $num ); // added 10 contacts
		
		// ### get all data.
		$num_found = $this->dao_contact->find()->fetchAll( $data );
		$this->assertEquals( $num, $num_found );
		$this->assertEquals( UT_SetUp_Contact::getContactName(4), $data[4][ 'contact_name' ] );
		
		// ### test find method
		$this->dao_contact->find( array( 
				array( 'contact_gender', '1' ), 
			  ) )
			->fetchAll( $data );
		$this->assertEquals( $num/2, count( $data ) );
		// wt( $data, 'find '.$data[0]['contact_gender'] );
		for( $i = 0; $i < count( $data ); $i ++ ) {
			$this->assertEquals( '1', $data[$i]['contact_gender'], "err@{$i}" );
		}
		
		// ### test comples find and order method
		$this->dao_contact->order( 'contact_id DESC' ) 
			->find( array( 
				array( 'contact_gender', '1' ), 
				array( 'contact_type', '2', '>=' ) 
			  ) )
			->fetchAll( $data );
		for( $i = 0; $i < count( $data ); $i ++ ) {
			$this->assertEquals( '1', $data[$i]['contact_gender'], "err@{$i}" );
			$this->assertTrue( $data[$i]['contact_type'] >= 3,     "err@{$i}" );
		}
		// wt( $data, 'find2 ' );
		
		// ### test execSelectCount method
		$this->dao_contact
			->clear()
			->where( 'contact_gender', '1' )
			->execSelectCount( $count )
			->fetchAll( $data );
		$this->assertEquals( $count, count( $data ) );
		// wt( $data, 'execSelectCount ' );
		
		// ### test limit method
		$this->dao_contact
			->clear()
			->where( 'contact_gender', '1' )
			->limit( 3, 1 )
			->execSelect()
			->fetchAll( $data );
		$this->assertEquals( 3, count( $data ) );
		$this->assertEquals( 3, $data[0]['contact_id'] );
		$this->assertEquals( 5, $data[1]['contact_id'] );
		$this->assertEquals( 7, $data[2]['contact_id'] );
		// wt( $data, 'limit ' );
		
		// ### test execSelectCount method
		$this->dao_contact
			->clear()
			->setWhere()
			->group( 'contact_gender' ) 
			->order( 'contact_gender' ) 
			->columns( 'contact_gender, count(*) AS count' )
			->execSelect()
			->fetchAll( $data )
		;
		$this->assertEquals( 2, count( $data ) );
		$this->assertEquals( 5, $data[0]['count']  );
		$this->assertEquals( 5, $data[1]['count']  );
		// wt( $data, 'group ' );
		
		// ### test execSelectCount method
		$this->dao_contact
			->clear()
			->setWhere()
			->where( 'contact_type', array( '2', '3' ), 'BETWEEN' )
			->order( 'contact_type, contact_id' ) 
			->execSelect()
			->fetchAll( $data )
		;
	}
    // +--------------------------------------------------------------- +
	//  testSelecters():
	//  to test select instance related functions.
	//  test on popHtml, getSelInstance, and getSelector. 
    // +--------------------------------------------------------------- +
	function testSelecters() 
	{
		$num = 10;
		$this->populateContact( $num ); // added 10 contacts
		$err = array();
		
		// ### test selector for contact_name
		$data = $this->dao_contact->getDatum( 1 );
		$name = $this->dao_contact->popHtml( 'contact_name', 'NAME', $data, $err );
		$this->assertEquals( $name, $data[ 'contact_name' ] );
		
		$form = $this->dao_contact->popHtml( 'contact_name', 'EDIT', $data, $err );
		$this->assertContains( "<input ",    $form );
		$this->assertContains( "type=\"text\"",    $form );
		$this->assertContains( "name=\"contact_name\"",   $form );
		$this->assertContains( "value=\"{$name}\"",       $form );
		
		// ### test selector for contact_gender (radio)
		$sel  = new sel_gender();
		$sep  = $sel->item_sep;
		$data = $this->dao_contact->getDatum( 1 );
		$gend = $this->dao_contact->popHtml( 'contact_gender', 'NAME', $data, $err );
		
		$gender = $sel->show( 'NAME', $data[ 'contact_gender' ] );
		$this->assertEquals( $gend, $gender );
		
		$form = $this->dao_contact->popHtml( 'contact_gender', 'EDIT', $data, $err );
		$form = strtolower( str_replace( array( "\n", "\r" ), '', $form ) );
		$f    = explode( $sep, $form );
		$val1 = $sel->item_data[0][0];
		$nam1 = $sel->item_data[0][1];
		$val2 = $sel->item_data[1][0];
		$nam2 = $sel->item_data[1][1];
		$this->assertContains( "<input",                    $f[0] );
		$this->assertContains( "type=\"radio\"",            $f[0] );
		$this->assertContains( "name=\"contact_gender\"",   $f[0] );
		$this->assertContains( "value=\"$val1\"",           $f[0] );
		$this->assertContains( ">$nam1</label>",            $f[0] );
		$this->assertContains( "<input",                    $f[1] );
		$this->assertContains( "type=\"radio\"",            $f[1] );
		$this->assertContains( "name=\"contact_gender\"",   $f[1] );
		$this->assertContains( "value=\"$val2\"",           $f[1] );
		$this->assertContains( ">$nam2</label>",            $f[1] );
		
		// ### test selector for contact_type (select)
		$sel  = new sel_contact_type();
		$data = $this->dao_contact->getDatum( 1 );
		$type = $this->dao_contact->popHtml( 'contact_type', 'NAME', $data, $err );
		
		$conttype = $sel->show( 'NAME', $data[ 'contact_type' ] );
		$this->assertEquals( $type, $conttype );
		
		$form = $this->dao_contact->popHtml( 'contact_type', 'EDIT', $data, $err );
		$form = strtolower( str_replace( array( "\n", "\r" ), '', $form ) );
		$val1 = $sel->item_data[0][0];
		$nam1 = $sel->item_data[0][1];
		$val2 = $sel->item_data[1][0];
		$nam2 = $sel->item_data[1][1];
		//echo "" .html_safe( $form ) . "";
		$this->assertContains( "<select",                    $form );
		$this->assertContains( "name=\"contact_type\"",   $form );
		$this->assertContains( "<option value=\"{$val1}\" selected=\"selected\">{$nam1}</option>",   $form );
		$this->assertContains( "<option value=\"{$val2}\">{$nam2}</option>",   $form );
	}
    // +--------------------------------------------------------------- +
	//  testSelecters():
	//  to test instance generation (getInstance, getRecord)
    // +--------------------------------------------------------------- +
	function testInstances() 
	{
		$num = 10;
		$this->populateContact( $num ); // added 10 contacts
		$dao_name  = $this->getDaoName();
		$dao_name2 = $this->getDaoName(2);
		
		// ### normal constructor
		// this test no longer valid in Cena 0.3
		// $dao  = new $dao_name();
		// $dao2 = new $dao_name();
		// $this->assertFalse( $dao === $dao2 );
		
		// ### singleton
		$dao1 = $dao_name::getInstance();
		$dao2 = $dao_name::getInstance();
		$dao3 = $dao_name2::getInstance();
		$dao4 = $dao_name2::getInstance();
		$this->assertTrue(  $dao1 === $dao2 );
		$this->assertTrue(  $dao3 === $dao4 );
		$this->assertFalse( $dao1 === $dao3 );
		
		// ### test addDatum
		$data1 = UT_SetUp_Contact::getContactData( 11 );
		$id    = $dao2->addDatum( $data1 );
		$data2 = $dao2->getDatum( $id );
		$this->assertEquals( $data1, $data2 );
		
		$data3 = UT_SetUp_Contact::getConnectData( 11 );
		$id    = $dao3->addDatum( $data3 );
		$data4 = $dao3->getDatum( $id );
		$this->assertEquals( $data3, $data4 );
		
	}
    // +--------------------------------------------------------------- +
	//  testSelecters():
	//  to test stdDatum
    // +--------------------------------------------------------------- +
	function testStdDatum() 
	{
		$num = 10;
		$this->populateContact( $num ); // added 10 contacts
		$data1 = $this->dao_contact->getDatum( 1 );
		
		$std = function ( &$data, $method ) {
			if( $method == 'select' ) {
				$data[ 'contact_type_name' ] = 
					UT_dao_contact100::popHtml( 'contact_type', 'NAME', $data );
			}
		};
		// test using stdDatumFunc
		$this->dao_contact->setStdDatum( $std );
		$data2 = $this->dao_contact->getDatum( 1 );
		$this->assertEquals( 
			$data2[ 'contact_type_name' ], 
			UT_dao_contact100::popHtml( 'contact_type', 'NAME', $data1 ) 
		);
		unset( $data2[ 'contact_type_name' ] );
		$this->assertEquals( $data1, $data2 );
		
		// test overwriting stdDatum method
		$this->dao_contact->stdDatum = $std;
		$data2 = $this->dao_contact->getDatum( 1 );
		$this->assertEquals( 
			$data2[ 'contact_type_name' ], 
			UT_dao_contact100::popHtml( 'contact_type', 'NAME', $data1 ) 
		);
		unset( $data2[ 'contact_type_name' ] );
		$this->assertEquals( $data1, $data2 );
	}
   // +--------------------------------------------------------------- +
	// test ideas
	// test check_input
	// test joined table (getJoinedTable, getInfo)
	// test history dao
	// test insert_max, insert_code?
	// test {add|mod}_{date|time} stuff
	// test restrictions (setRestrictAccess, daoRestrictWhere, ).
    // +--------------------------------------------------------------- +
}


?>