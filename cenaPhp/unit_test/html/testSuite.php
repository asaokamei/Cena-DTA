<?php

class htmlSuiteTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite( 'all html{Prop|Tags|Form}.php tests' );
		$folder = dirname( __FILE__ ) . '/';
		$suite->addTestFile( $folder . 'testProp.php' );
		$suite->addTestFile( $folder . 'testTags.php' );
		$suite->addTestFile( $folder . 'testForm.php' );
		return $suite;
	}
}
?>