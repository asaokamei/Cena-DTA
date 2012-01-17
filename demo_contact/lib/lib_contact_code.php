<?php
use CenaDta\Html as html;
// lib_contact_code.php generated by fmDB3/wsjp_proj_libfile.php on 2010-06-03

// +----------------------------------------------------------------------+
// Constants for contact
// +----------------------------------------------------------------------+

// コネクト種別 (connect_type)
define( 'CONNECT_TYPE_TEL',     '1' );  // 
define( 'CONNECT_TYPE_EMAIL',   '2' );  // 
define( 'CONNECT_TYPE_HP',      '3' );  // 
define( 'CONNECT_TYPE_TWITTER', '4' );  // 

// 性別 (gender)
define( 'GENDER_MALE',     '1' );  // 男性
define( 'GENDER_FEMALE',   '2' );  // 女性

// 分類 (contact_type)
define( 'CONTACT_TYPE_FRIEND',    '1' );  // 友達
define( 'CONTACT_TYPE_WORK',      '2' );  // 仕事
define( 'CONTACT_TYPE_PRIVATE',   '3' );  // 家族
define( 'CONTACT_TYPE_OTHER',     '4' );  // その他


// +----------------------------------------------------------------------+
// コネクト種別
// +----------------------------------------------------------------------+
class sel_connect_type extends html\formSelect
{
	static $item_list = array( CONNECT_TYPE_TEL, CONNECT_TYPE_EMAIL, CONNECT_TYPE_HP, CONNECT_TYPE_TWITTER );
    /* -------------------------------------------------------- */
	function sel_connect_type( $var_name="", $opt1=NULL, $opt2=NULL, $ime="OFF" )
	{
		if( $var_name ) $this->name = $var_name;
		else            $this->name = "connect_type";
		$this->style           = "SELECT";
		$this->add_head_option = '';
		$this->default_items   = '';
		$this->err_msg_empty   = "未選択";
		$this->item_data[] = array( CONNECT_TYPE_TEL, 	  'TEL' );
		$this->item_data[] = array( CONNECT_TYPE_EMAIL,   'mail' );
		$this->item_data[] = array( CONNECT_TYPE_HP, 	  'ウェブ' );
		$this->item_data[] = array( CONNECT_TYPE_TWITTER, 'ツイッタ' );
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
// 性別
// +----------------------------------------------------------------------+
class sel_gender extends html\formRadio
{
	static $item_list = array( GENDER_MALE, GENDER_FEMALE );
    /* -------------------------------------------------------- */
	function sel_gender( $var_name="", $opt1=NULL, $opt2=NULL, $ime="OFF" )
	{
		if( $var_name ) $this->name = $var_name;
		else            $this->name = "contact_type";
		$this->style           = "RADIO_HOR";
		$this->add_head_option = '';
		$this->default_items   = '';
		$this->err_msg_empty   = "未選択";
		$this->item_data[] = array( GENDER_MALE, 	'男性' );
		$this->item_data[] = array( GENDER_FEMALE, 	'女性' );
	}
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
// 分類
// +----------------------------------------------------------------------+
class sel_contact_type extends html\formSelect
{
	static $item_list = array( CONTACT_TYPE_FRIEND, CONTACT_TYPE_WORK, CONTACT_TYPE_PRIVATE, CONTACT_TYPE_OTHER );
    /* -------------------------------------------------------- */
	function sel_contact_type( $var_name="", $opt1=NULL, $opt2=NULL, $ime="OFF" )
	{
		if( $var_name ) $this->name = $var_name;
		else            $this->name = "contact_type";
		$this->style           = "SELECT";
		$this->add_head_option = '';
		$this->default_items   = '';
		$this->err_msg_empty   = "未選択";
		$this->item_data[] = array( CONTACT_TYPE_FRIEND, 	'友達' );
		$this->item_data[] = array( CONTACT_TYPE_WORK, 	    '仕事' );
		$this->item_data[] = array( CONTACT_TYPE_PRIVATE, 	'家族' );
		$this->item_data[] = array( CONTACT_TYPE_OTHER, 	'その他' );
	}
    /* -------------------------------------------------------- */
}

?>