<?php
//require_once( dirname( __FILE__ ) . "/../../php_lib/class5/class.util.php" );
use CenaDta\Html as html;

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

/* fmDB3 definitions:

contact100 

コンタクトID	contact_id	SERIAL	NOT		htmlHidden			OFF	pushChar		PGG_REG_NUMBER
名前	contact_name	text	NOT	''	htmlText	15		ON	pushChar		
性別	contact_gender	char(1)	NOT	''	htmlText			OFF	pushChar		PGG_REG_AL_NUM
分類	contact_type	char(1)	NOT	''	htmlText			OFF	pushChar		PGG_REG_AL_NUM
#table_name: contact100
#table_title: コンタクト
#primary_key: contact_id


*/
class SetUp_Contact
{
    // +--------------------------------------------------------------- +
	//  コンタクト（contact_100)
    // +--------------------------------------------------------------- +
	static function getDropContact() {
		$table = self::getContactTable();
		return "DROP TABLE IF EXISTS {$table};";
	}
    // +--------------------------------------------------------------- +
	static function getCreateContact()
	{
		$table = self::getContactTable();
		$sql =<<<END_OF_SQL
CREATE TABLE {$table} (
    contact_id        SERIAL     NOT NULL,
    contact_name      text       NOT NULL DEFAULT '',
    contact_gender    char(1)    NOT NULL DEFAULT '',
    contact_type      char(1)    NOT NULL DEFAULT '',
	contact_tag       text       NOT NULL DEFAULT '',
	contact_date      date       DEFAULT NULL,
    constraint contact100_pkey PRIMARY KEY (
        contact_id
    )
) type=innodb;
END_OF_SQL;
		return $sql;
	}
    // +--------------------------------------------------------------- +
	static function getContactTable() {
		return 'utest100_contact';
	}
    // +--------------------------------------------------------------- +
	static function getContactName( $i=0 )
	{
		static $type;
		if( !$type ) $type = new sel_contact_type();
		return  "name#{$idx} " . $type->show('NAME', $i % 4 + 1 );
	}
    // +--------------------------------------------------------------- +
	static function getContactData( $i=0 )
	{
		$idx  = $i + 1;
		$data = array(
			'contact_name'   => self::getContactName( $i ),
			'contact_gender' => $i % 2 + 1,
			'contact_type'   => $i % 4 + 1,
			'contact_date'   => date( 'Y-m-d' ),
			'contact_tag'    => "i={$i}, tag@" . ($i%3+1),
		);
		return $data;
	}
    // +--------------------------------------------------------------- +
	//  コネクト（contact_110)
    // +--------------------------------------------------------------- +
	static function getDropConnect	() {
		$table = self::getConnectTable();
		return "DROP TABLE IF EXISTS {$table};";
	}
    // +--------------------------------------------------------------- +
	static function getConnectTable() {
		return 'utest110_connect';
	}
    // +--------------------------------------------------------------- +
	static function getCreateConnect()
	{
		$table = self::getConnectTable();
		$sql =<<<END_OF_SQL
CREATE TABLE {$table} (
    connect_id        SERIAL     NOT NULL,
	contact_id        int4       NOT NULL,
    connect_info      text       NOT NULL DEFAULT '',
    connect_type      char(1)    NOT NULL DEFAULT '',
    constraint contact110_pkey PRIMARY KEY (
        connect_id
    )
) type=innodb;
END_OF_SQL;
		return $sql;
	}
    // +--------------------------------------------------------------- +
	static function getConnectData( $i=0, $target=3 )
	{
		static $type;
		if( !$type ) $type = new sel_connect_type();
		$idx  = $i + 1;
		$data = array(
			'connect_info'   => "conns＃{$idx} " . $type->show('NAME',$i%4+1),
			'connect_type'   => $i % 4 + 1,
			'contact_id'     => 1 + $i % $target,
		);
		return $data;
	}
    // +--------------------------------------------------------------- +
}



?>