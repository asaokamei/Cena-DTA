<?php
//define( 'WORDY', 4 );
require_once( dirname( __FILE__ ) . "/testSql.php" );
use CenaDta\Dba\Sql as ormSql;

class DbaTestQuoted extends DbaTest
{
    // +--------------------------------------------------------------- +
	public function setUp() {
		$config = UT_SetUp_Contact::getDbaIniFile();
		$this->sql = new ormSql( $config );
		$this->use_prepared = FALSE; // use quote
	}
    // +--------------------------------------------------------------- +
}

?>