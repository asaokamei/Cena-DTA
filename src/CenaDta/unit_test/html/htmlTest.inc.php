<?php
use CenaDta\Html as html;

function get_htmlFormTest_item() {
	$item_data = array();
	$item_data[] = array( 1, 	  'TEL' );
	$item_data[] = array( 2,   'mail' );
	$item_data[] = array( 3, 	  'ウェブ' );
	$item_data[] = array( 4, 'ツイッタ' );
	return $item_data;
}

// +----------------------------------------------------------------------+
class sel_connect_type extends html\formSelect
{
    /* -------------------------------------------------------- */
	function __construct( $var_name="", $opt1=NULL, $opt2=NULL, $ime="OFF" )
	{
		if( $var_name ) $this->name = $var_name;
		else            $this->name = "connect_type";
		$this->add_head_option = 'head added';
		$this->default_items   = '';
		$this->err_msg_empty   = "未選択";
		$this->item_data       = get_htmlFormTest_item();
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class sel_connect_radio extends html\formRadio
{
    /* -------------------------------------------------------- */
	function __construct( $var_name="", $opt1=NULL, $opt2=NULL, $ime="OFF" )
	{
		if( $var_name ) $this->name = $var_name;
		else            $this->name = "connect_type";
		$this->add_head_option = 'head added';
		$this->default_items   = '';
		$this->err_msg_empty   = "未選択";
		$this->item_sep        = '<+>';
		$this->item_data       = get_htmlFormTest_item();
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class sel_connect_check extends html\formCheck
{
    /* -------------------------------------------------------- */
	function __construct( $var_name="", $opt1=NULL, $opt2=NULL, $ime="OFF" )
	{
		if( $var_name ) $this->name = $var_name;
		else            $this->name = "connect_type";
		$this->add_head_option = 'head added';
		$this->default_items   = '';
		$this->err_msg_empty   = "未選択";
		$this->item_sep        = '<+>';
		$this->item_data       = get_htmlFormTest_item();
	}
    /* -------------------------------------------------------- */
}



?>