<?php

class htmlSuiteTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite( 'Dba_{Sql|Sql-quoted|Dao}.php tests' );
		$folder = dirname( __FILE__ ) . '/';
		$suite->addTestFile( $folder . 'testSql.php' );
		$suite->addTestFile( $folder . 'testSqlQuoted.php' );
		$suite->addTestFile( $folder . 'testDaoB.php' );
		return $suite;
	}
}
?>