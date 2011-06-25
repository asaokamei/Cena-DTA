<?php
//define( 'WORDY', 4 );
require_once( dirname( __FILE__ ) . "/../../class/class.ext_func.php" );
require_once( dirname( __FILE__ ) . "/../../Html/Form.php" );
require_once( dirname( __FILE__ ) . "/../../Dba/Sql.php" );
require_once( dirname( __FILE__ ) . "./dbaTest.inc.php" );
use CenaDta\Dba\Sql as ormSql;

class DbaTest extends PHPUnit_Framework_TestCase
{
    // +--------------------------------------------------------------- +
	public function setUp() {
		$config = realpath( dirname( __FILE__ ) . '/dbaTest.ini.php' );
		$this->sql = new ormSql( $config );
	}
    // +--------------------------------------------------------------- +
	public function setSql4Contact() {
		$this->sql->clear();
		$this->sql->table( SetUp_Contact::getContactTable() )->order( 'contact_id' );
	}
    // +--------------------------------------------------------------- +
	public function populateContact( $num=10 ) {
		$this->sql->execSQL( SetUp_Contact::getDropContact() );
		$this->sql->execSQL( SetUp_Contact::getCreateContact() );
		$this->sql->table( SetUp_Contact::getContactTable() );
		for( $i = 0; $i < 10; $i ++ ) 
			$this->sql->execInsert(  SetUp_Contact::getContactData( $i ) );
	}
    // +--------------------------------------------------------------- +
	function testTemp() {
	}
    // +--------------------------------------------------------------- +
	function testAdd() {
		$num = 10;
		$this->populateContact( $num ); // added 10 contacts
		
		// ### get all data.
		$this->setSql4Contact();
		$num_found = $this->sql->find()->fetchAll( $data );
		$this->assertEquals( $num, $num_found );
		$this->assertEquals( SetUp_Contact::getContactName(4), $data[4][ 'contact_name' ] );
		
		// ### test find method
		$this->setSql4Contact();
		$this->sql->find( array( 
				array( 'contact_gender', '1' ), 
			  ) )
			->fetchAll( $data );
		$this->assertEquals( $num/2, count( $data ) );
		// wt( $data, 'find '.$data[0]['contact_gender'] );
		for( $i = 0; $i < count( $data ); $i ++ ) {
			$this->assertEquals( '1', $data[$i]['contact_gender'], "err@{$i}" );
		}
		
		// ### test comples find and order method
		$this->setSql4Contact();
		$this->sql->order( 'contact_id DESC' ) 
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
		$this->setSql4Contact();
		$this->sql->where( 'contact_gender', '1' )
			->execSelectCount( $count )
			->fetchAll( $data );
		$this->assertEquals( $count, count( $data ) );
		// wt( $data, 'execSelectCount ' );
		
		// ### test limit method
		$this->setSql4Contact();
		$this->sql->where( 'contact_gender', '1' )
			->limit( 3, 1 )
			->execSelect()
			->fetchAll( $data );
		$this->assertEquals( 3, count( $data ) );
		$this->assertEquals( 3, $data[0]['contact_id'] );
		$this->assertEquals( 5, $data[1]['contact_id'] );
		$this->assertEquals( 7, $data[2]['contact_id'] );
		// wt( $data, 'limit ' );
		
		// ### test execSelectCount method
		$this->setSql4Contact();
		$this->sql->setWhere()
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
		$this->setSql4Contact();
		$this->sql->setWhere()
			->where( 'contact_type', array( '2', '3' ), 'BETWEEN' )
			->order( 'contact_type, contact_id' ) 
			->execSelect()
			->fetchAll( $data )
		;
	}
    // +--------------------------------------------------------------- +
	function testPrepare() {
		$num = 10;
		$this->populateContact( $num ); // added 10 contacts
		
		// ### get all data.
		$this->setSql4Contact();
		$num_found = $this->sql->find()->fetchAll( $data );
		$this->assertEquals( $num, $num_found );
		$this->assertEquals( SetUp_Contact::getContactName(4), $data[4][ 'contact_name' ] );
		
		// ### test find method
		$this->setSql4Contact();
		$this->sql->find( array( 
				array( 'contact_gender', '1' ), 
			  ) )
			->fetchAll( $data );
		$this->assertEquals( $num/2, count( $data ) );
		// wt( $data, 'find '.$data[0]['contact_gender'] );
		for( $i = 0; $i < count( $data ); $i ++ ) {
			$this->assertEquals( '1', $data[$i]['contact_gender'], "err@{$i}" );
		}
		
		// ### test comples find and order method
		$this->setSql4Contact();
		$this->sql->order( 'contact_id DESC' ) 
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
		$this->setSql4Contact();
		$this->sql->prepareWhere( 'contact_gender', '1' )
			->execSelectCount( $count )
			->fetchAll( $data );
		$this->assertEquals( $count, count( $data ) );
		// wt( $data, 'execSelectCount ' );
		
		// ### test limit method
		$this->setSql4Contact();
		$this->sql->prepareWhere( 'contact_gender', '1' )
			->limit( 3, 1 )
			->execSelect()
			->fetchAll( $data );
		$this->assertEquals( 3, count( $data ) );
		$this->assertEquals( 3, $data[0]['contact_id'] );
		$this->assertEquals( 5, $data[1]['contact_id'] );
		$this->assertEquals( 7, $data[2]['contact_id'] );
		// wt( $data, 'limit ' );
		
		// ### test execSelectCount method
		$this->setSql4Contact();
		$this->sql->setWhere()
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
		$this->setSql4Contact();
		$this->sql->setWhere()
			->prepareWhere( 'contact_type', array( '2', '3' ), 'BETWEEN' )
			->order( 'contact_type, contact_id' ) 
			->execSelect()
			->fetchAll( $data )
		;
	}
    // +--------------------------------------------------------------- +
}


?>