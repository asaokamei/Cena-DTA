<?php

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
		$table = static::getContactTable();
		return "DROP TABLE IF EXISTS {$table};";
	}
    // +--------------------------------------------------------------- +
	static function getCreateContact()
	{
		$table = static::getContactTable();
		$sql =<<<END_OF_SQL
CREATE TABLE {$table} (
    contact_id        SERIAL     NOT NULL,
    contact_name      text       NOT NULL DEFAULT '',
    contact_gender    char(1)    NOT NULL DEFAULT '',
    contact_type      char(1)    NOT NULL DEFAULT '',
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
		return 'contact100';
	}
    // +--------------------------------------------------------------- +
	static function getContactName( $i=0 )
	{
		static $type;
		if( !$type ) $type = new sel_contact_type();
		return  "name#{$i} " . $type->show('NAME', $i % 4 + 1 );
	}
    // +--------------------------------------------------------------- +
	static function getContactData( $i=0 )
	{
		static $type;
		if( !$type ) $type = new sel_contact_type();
		$idx  = $i + 1;
		$data = array(
			'contact_name'   => static::getContactName( $i ),
			'contact_gender' => $i % 2 + 1,
			'contact_type'   => $i % 4 + 1,
			'contact_date'   => date( 'Y-m-d' ),
		);
		return $data;
	}
    // +--------------------------------------------------------------- +
	//  コネクト（contact_110)
    // +--------------------------------------------------------------- +
	static function getDropConnect	() {
		$table = static::getConnectTable();
		return "DROP TABLE IF EXISTS {$table};";
	}
    // +--------------------------------------------------------------- +
	static function getConnectTable() {
		return 'contact110';
	}
    // +--------------------------------------------------------------- +
	static function getCreateConnect()
	{
		$table = static::getConnectTable();
		$sql =<<<END_OF_SQL
CREATE TABLE {$table} (
    connect_id        SERIAL     NOT NULL,
    connect_info      text       NOT NULL DEFAULT '',
    connect_type      char(1)    NOT NULL DEFAULT '',
	contact_id        int4       NOT NULL,
    constraint contact110_pkey PRIMARY KEY (
        connect_id
    )
) type=innodb;
END_OF_SQL;
		return $sql;
	}
    // +--------------------------------------------------------------- +
	static function getConnectData( $i=0 )
	{
		static $type;
		if( !$type ) $type = new sel_connect_type();
		$idx  = $i + 1;
		$data = array(
			'connect_info'   => "conns＃{$idx} " . $type->show('NAME',$i%4+1),
			'connect_type'   => $i % 4 + 1,
			'contact_id'     => 1 + $i % 3,
		);
		return $data;
	}
    // +--------------------------------------------------------------- +
}



?>