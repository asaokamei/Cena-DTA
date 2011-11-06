<?php
namespace CenaDta\Dba;
/**
 *	Helper class for Dao (Data Access Object).
 *
 *	@copyright     Copyright 2010-2011, Asao Kamei
 *	@link          http://www.workspot.jp/cena/
 *	@license       GPLv2
 */

class DaoHelper
{
	static $stdArrayAndDba_separator = '|';
	// +--------------------------------------------------------------- +
    function getJoinedTable( $table, $join_list )
    {
		// example of joined table settings.
		// ex1: column_id=table2:column_id 
		//      => JOIN table2 USING( column_id )
		// ex2: column_id?table2:other_id 
		//      => LEFT OUTER JOIN TABLE2 ON( column_id=other_id )
		if( !have_value( $join_list ) ) return $table;
		
		if( !is_array( $join_list ) ) {
			$join_list = array( $join_list );
		}
		foreach( $join_list as $cond ) 
		{
			if( preg_match( '/(\w+)\s*([=|?])\s*(\w+):(\w+)/', $cond, $matches ) ) {
				if( WORDY > 5 ) wt( $matches, $cond );
				$join = ( $matches[2] == '?' ) ? 'LEFT OUTER JOIN ': 'JOIN ';
				if( $matches[1] == $matches[4] ) {
					$using = " USING( " . $matches[4] . ' )';
				}
				else {
					$using = " ON( " . $matches[1] . ' = ' . $matches[4] . ' )';
				}
				$table .= "\n  " . $join . $matches[3] . $using;
			}
		}
		if( WORDY > 3 ) echo "getJoinedTable( $table, $join_list ) => \n$table <br>\n";
		return $table;
    }
	// +--------------------------------------------------------------- +
	/** standarize data for multiple check/select. 
	 *	converts from array to string when insert/update database.
	 *	converts from string to array when reading data.
	 *	if the name is not set in the data, data will not be altered.
	 *
	 *	@param array &$data
	 *		data to be standarized.
	 *	@param string $name
	 *		name of variable to be standarized.
	 *	@param string $act
	 *		calling method used for conversion.
	 *		for 'modDatum', 'addDatum', converts from array to string.
	 *	@return $this
	 */
	function stdArrayAndDba( &$data, $name, $act )
	{
		// convert checked value (array) and db column (list string)
		if( !isset( $data[ $name ] ) ) return ;
		$separator = self::$stdArrayAndDba_separator;
		$val       = & $data[ $name ];
		$writings  = array( 'modDatum', 'update', 'addDatum', 'insert' );
		
		if( in_array( $act, $writings ) ) { // writing to DB.
			if( is_array( $val ) ) {        // value is an array.
				$val = $separator . implode( $separator, $val ) . $separator;
			}
		}
		else {                                          // read from DB.
			if( !is_array( $val ) &&                    // value is a string.
				substr( $val, 0, 1 ) == $separator &&   // starts with separator.
				substr( $val, -1, 1 ) == $separator ) { // ends with separator.
				$val = explode( $separator, substr( $val, 1, -1 ) );
			}
		}
		return ;
	}
    // +----------------------------------------------------------------------------+
	//
	//                          helper for SQL statement
	//
    // +----------------------------------------------------------------------------+
	
	// +--------------------------------------------------------------- +
	function stdArrayAndDbaExample( &$data, $act ) {
		DaoHelper::stdArrayAndDba( $data, 'check_this', $act );
		DaoHelper::stdArrayAndDba( $data, 'check_that', $act );
		return $this;
	}
	// +--------------------------------------------------------------- +
    function set_data_value( &$data, $key, $val ) {
        if( have_value( $key ) && !isset( $data[ $key ] ) ) {
		   $data[ $key ] = $val;
		}
    }
	// +--------------------------------------------------------------- +
    function update_datetime( $col_info, &$data ) {
        self::set_data_value( $data, $col_info[ 'mod_date' ],     date( "Y-m-d" ) );
        self::set_data_value( $data, $col_info[ 'mod_time' ],     date( "H:i:s" ) );
        self::set_data_value( $data, $col_info[ 'mod_datetime' ], date( "Y-m-d H:i:s" ) );
    }
	// +--------------------------------------------------------------- +
    function insert_datetime( $col_info, &$data ) {
		self::update_datetime( $col_info, $data );
		self::set_data_value( $data, $col_info[ 'new_date' ],     date( "Y-m-d" ) );
        self::set_data_value( $data, $col_info[ 'new_time' ],     date( "H:i:s" ) );
        self::set_data_value( $data, $col_info[ 'new_datetime' ], date( "Y-m-d H:i:s" ) );
    }
	// +--------------------------------------------------------------- +
    function make_max( $dao, $table, $id_name, $data )
    {
		$id_info = array( 
			'name' => $id_name,
			'leng' => 0,
			'head' => ''
		);
		return self::make_code( $dao, $table, $id_info, $data );
    }
	// +--------------------------------------------------------------- +
	/**
	 *	example of id_info
	 *	array(
	 *		'name'     => 'contact_id',
	 *		'leng'     => '8',
	 *		'head'     => 'CN',
	 *		'head_col' => 'contact_type',
	 *		'func'     => 'outer_func',
	 *	);
	 *	will produce CNYxxxxx, where CN is fixed, Y is contact_type,
	 *	and xxxxx is serial number.
	 */
    function make_code( $dao, $table, $id_info, $data )
    {
        if( WORDY > 2 ) echo "<br>baseDAO::make_code( $table, $id_info )<br>\n";
        $code_name     = $id_info[ "name" ]; // primary key name
        $code_func     = $id_info[ "func" ]; // specify a function to determine code
        $code_leng     = $id_info[ "leng" ]; // length of code
        $code_head     = $id_info[ "head" ]; // header characters
        $code_head_col = $id_info[ "head_col" ]; // use other data value
        
        if( $code_func ) return $code_func( $table, $id_info, $data, $this->sql );
        
        if( have_value( $code_head_col ) ) {
			if( $code_head ) {
	            $code_head = $data[ $code_head_col ] . $code_head;
			}
			else {
	            $code_head = $data[ $code_head_col ];
			}
        }
        $head_length = strlen( $code_head );
        if( WORDY ) echo " -> ( $code_name, $code_leng, $code_head )<br>\n";
        
        if( $code_leng <= $head_length ) return FALSE;
        
        $dao->clear();
        if( $code_head ) {
	        $dao->execSQL( "SELECT MAX( {$code_name} ) AS max FROM {$this->table} WHERE {$code_name} LIKE '{$code_head}%';" );
        } 
        else {
	        $dao->execSQL( "SELECT MAX( {$code_name} ) AS max FROM {$this->table};" );        
        }
        $max = $dao->fetchRow(0);
        
        if( !$max ) {
            return FALSE;
        }
		else
		if( !have_value( $code_leng ) || $code_leng == 0 ) {
			$max = $max[ 'max' ];
			if( !$max ) $max = 0;
			$max = $max + 1;
		}
        else {
			$max = $max[ 'max' ];
			if( !$max ) $max = 1;
            $max = substr( (string) $max, $head_length );
            $fmt = sprintf( "%d", $code_leng - $head_length );
            $fmt = $code_head . "%0" . $fmt . "d";
            $max = sprintf( $fmt, ($max + 1) );
            if( WORDY > 3 ) echo "--> $max using $fmt...<br>\n";
        }
        return $max;
    }
    // +----------------------------------------------------------------------------+
	//
	//                          helper for HTML form output
	//
    // +----------------------------------------------------------------------------+
	
	// +--------------------------------------------------------------- +
	function disp_html( $class, $html_type, $td, $err=array() )
	{
		html_forms::setDefault( 'class', 'htmlForms' );
		$columns = $class::getColumn();
		
		$html    = '<table class="tblHover" width="100%">';
		foreach( $columns as $var_name => $col_name ) {
			$value = $class::popHtml( $var_name, $html_type, $td, $err );
			$html .= "<tr>\n";
			$html .= "  <th width=\"25%\">{$col_name}</th>\n";
			$html .= "  <td>{$value}</td>\n";
			$html .= "</tr>\n";
		}
		$html  .= '</table>';
		return $html;
	}
	// +--------------------------------------------------------------- +
	function disp_html_row( $html_type, $data, $err=array() )
	{
		html_forms::setDefault( 'class', 'htmlForms' );
		$columns = $class::getColumn();
		
		$html    = '<table class="tblHover" width="100%">';
		foreach( $columns as $var_name => $col_name ) {
			$html .= "<thead>\n";
			$html .= "  <th>{$col_name}</th>\n";
			$html .= "</thead>\n";
		}
		foreach( $columns as $var_name => $col_name ) {
			$value = $class::popHtml( $var_name, $html_type, $td, $err );
			if( $var_name == $class::idName() ) {
				$crud   = "{$class}_crud.php";
				$value  = "<a href=\"{$crud}\">{$value}</a>";
				$value .= "[<a href=\"{$crud}?act=mod\">mod</a>][<a href=\"{$crud}?act=del\">del</a>]";
			}
			$html .= "<tbody>\n";
			$html .= "  <td>{$value}</td>\n";
			$html .= "</tbody>\n";
		}
		$html  .= '</table>';
		return $html;
	}
	// +--------------------------------------------------------------- +
	function disp_record_row( $html_type, $records )
	{
		html_forms::setDefault( 'class', 'htmlForms' );
		$columns = $class::getColumn();
		
		$html    = '<table class="tblHover" width="100%">';
		$html .= "<thead>\n";
		foreach( $columns as $var_name => $col_name ) {
			$html .= "  <th>{$col_name}</th>\n";
		}
		$html .= "</thead>\n";
		$html .= "<tbody>\n";
		for( $i = 0; $i < count( $records ); $i++ ) { 
			$html .= "<tr>\n";
			$rec = $records[ $i ]; 
			foreach( $columns as $var_name => $col_name ) {
				$value = $rec->popHtml( $var_name, $html_type );
				$html .= "  <td>{$value}</td>\n";
			}
			$html .= "</tr>\n";
		}
		$html .= "</tbody>\n";
		$html  .= '</table>';
		return $html;
	}
    // +----------------------------------------------------------------------------+
}


?>