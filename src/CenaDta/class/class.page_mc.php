<?php

class page_MC
{
	var $data = array();
	var $default;
	var $methods = array();
	var $execute;
	
	// for nextAct
	var $next_act      = FALSE;      // what's next? 
	var $next_act_name = 'next_act'; // name of control variable.
	var $next_buttuon  = array();    // button title collection.
	var $default_but   = 'top';
	// +-----------------------------------------------------------+
	function main()
	{
		if( WORDY ) wordy_table( $this->method, 'page_MC::methods' );
		foreach( $this->methods as $method )
		{
			$func = $method[0];
			$arg  = $method[1];
			$val  = $method[2];
			if( isset( $_REQUEST[ $arg ] ) && $val !== FALSE ) {
				if( $val === TRUE || 
				    $val === $_REQUEST[ $arg ] ) {
					$this->execute( $method );
					return;
				}
			}
			else
			if( $val === FALSE && !isset( $_REQUEST[ $arg ] ) ) {
				$this->execute( $method );
				return;
			}
		}
		$this->execute( array( $this->default ) );
	}
	// +-----------------------------------------------------------+
	function execute( $method )
	{
		try {
			if( WORDY ) wordy_table( $method, 'executing method' );
			$func = $method[0];
			$this->execute = $func;
			$func( $this, $method );
		}
		catch( AppException $e ) {
			msg_box::error( $e->getMessage() );
		}
	}
	// +-----------------------------------------------------------+
	function addData( $name, $value ) {
		$this->data[ $name ] = $value; 
		return $this;
	}
	// +-----------------------------------------------------------+
	function getData( $name, & $data ) {
		$data = isset( $this->data[ $name ] ) ? $this->data[ $name ] : FALSE; 
		return $this;
	}
	// +-----------------------------------------------------------+
	function & data() {
		return $this->data;
	}
	// +-----------------------------------------------------------+
	function setDefault( $func, $title=NULL ) {
		$this->default = $func;
		if( have_value( $title ) ) $this->default_but = $title;
		return $this;
	}
	// +-----------------------------------------------------------+
	function setFunc( $func, $arg, $val=TRUE ) {
		$this->methods[] = array( $func, $arg, $val ); 
		return $this;
	}
	// +-----------------------------------------------------------+
	// nextAct methods
	// +-----------------------------------------------------------+
	function setAct( $func, $val, $title=NULL ) {
		$this->setFunc( $func, $this->next_act_name, $val );
		$this->next_buttuon[ $val ] = $title;
		return $this;
	}
	// +-----------------------------------------------------------+
	function setNext( $val ) {
		$this->next_act = $val;
		return $this;
	}
	// +-----------------------------------------------------------+
	function getButtonTitle() {
		if( have_value( $this->next_buttuon, $this->next_act ) ) {
			return $this->next_buttuon[ $this->next_act ];
		}
		return $this->default_but;
	}
	// +-----------------------------------------------------------+
	function getNextActHiddenTag() {
		if( $this->next_act )
			return 
				"<input type=\"hidden\" name=\"{$this->next_act_name}\"" . 
				" value=\"{$this->next_act}\">\n";
	}
	// +-----------------------------------------------------------+
}



?>