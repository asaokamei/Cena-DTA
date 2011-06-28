<?php
namespace CenaDta\Cena;
/**
 *	Envelope class for Cena.
 *
 *	@copyright     Copyright 2010-2011, Asao Kamei
 *	@link          http://www.workspot.jp/cena/
 *	@license       GPLv2
 */

class Envelope
{
	static $env_name  = 'cenv';
	static $env_idx   = 0;
	static $env_data  = array();
	// +-----------------------------------------------------------+
	static function makeEnvName( $increment=TRUE )
	{
		$idx  = self::$env_idx;
		$cenv = self::$env_name . "[{$idx}]";
		if( $increment ) self::incEnvIdx();
		return $cenv;
	}
	// +-----------------------------------------------------------+
	static function incEnvIdx() {
		self::$env_idx ++;
	}
	// +-----------------------------------------------------------+
	static function setCenaEnv( $cena )
	{
		$cenv = self::makeEnvName();
		$cena->setScheme( $cenv );
	}
	// +-----------------------------------------------------------+
	static function cenaToEnv( &$cena_arr )
	{
		foreach( $cena_arr as $cena ) {
			self::setCenaEnv( $cena );
		}
	}
	// +-----------------------------------------------------------+
	static function makeEnvData( $cena )
	{
		$env_data = array(
			'envelope' => 'envelope',
			'scheme'   => $cena->getScheme(),
			'model'    => $cena->getModel(),
			'type'     => $cena->getType(),
			'id'       => $cena->getId(),
			'cena_id'  => $cena->getCenaId(),
			'elements' => $cena->getData(),
			'relates'  => $cena->getRelation()
		);
		$env_data[ 'num_elem' ] = count( $env_data[ 'elements' ] );
		if( WORDY > 3 ) wordy_table( $env_data, 'envelope data' );
		return $env_data;
	}
	// +-----------------------------------------------------------+
	static function pushEnvData( $cena ) {
		$env_data = & self::makeEnvData( $cena );
		self::$env_data[ self::$env_idx ] = $env_data;
		self::incEnvIdx();
	}
	// +-----------------------------------------------------------+
	static function popEnvData() {
		return self::$env_data;
	}
	// +-----------------------------------------------------------+
	static function popEnvJsData() {
		return json_encode( self::$env_data );
	}
	// +-----------------------------------------------------------+
}

?>