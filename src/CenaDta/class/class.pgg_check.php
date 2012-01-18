<?php
// +-----------------------------------------------------------------------+
// | Copyright (c) 2002-2004, Asao Kamei                                   |
// | All rights reserved.                                                  |
// |                                                                       |
// | This program is free software; you can redistribute it and/or modify  |
// | it under the terms of the GNU General Public License as published by  |
// | the Free Software Foundation; either version 2 of the License, or     |
// | (at your option) any later version.                                   |
// |                                                                       |
// | This program is distributed in the hope that it will be useful,       |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of        |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         |
// | GNU General Public License for more details.                          |
// |                                                                       |
// | You should have received a copy of the GNU General Public License     |
// | along with this program; if not, write to the                         |
// | Free Software Foundation, Inc.                                        |
// | 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA               |
// +-----------------------------------------------------------------------+
// | Author: Asao Kamei (asao_kamei@yahoo.com)
// +-----------------------------------------------------------------------+
// class.pggCheck.php

if( !defined( "WORDY" ) ) define( "WORDY",  0 ); // very wordy...
if( !defined( "PGG_USE_SESSION" ) ) define( "PGG_USE_SESSION", FALSE );

define( "PGG_VALUE_MISSING_OK", "-1" );
define( "PGG_VALUE_MUST_EXIST", "0" );

// include necessary libraries...
$current_dir = dirname( __FILE__ );
require_once( $current_dir . "/class.ext_func.php" );
require_once( $current_dir . "/class.pgg_value.php" );

define( "PGG_ENCODE_NONE",   WEBIO_ENCODE_NONE );
define( "PGG_ENCODE_BASE64", WEBIO_ENCODE_BASE64 );
define( "PGG_ENCODE_CRYPT",  WEBIO_ENCODE_CRYPT ); // not supported yet!
if( !defined( "PGG_ENCODE_TYPE" ) ) define( "PGG_ENCODE_TYPE", WEBIO_ENCODE_BASE64 );

// Errors etc.
define( "PGG_ERRNUM_CHAR_BADLENGTH", 1001 );
define( "PGG_ERRNUM_CHAR_NOVALUE",   1002 );
define( "PGG_ERRNUM_CHAR_BADFORMAT", 1003 );
define( "PGG_ERRNUM_DATE_NOVALUE",   2001 );
define( "PGG_ERRNUM_DATE_BADDATE",   2002 );
define( "PGG_ERRNUM_NUM_NOTANUMBER", 3001 );
define( "PGG_ERRNUM_NUM_OUTOFRANGE", 3002 );
define( "PGG_ERRNUM_MULTI_MISSING",  4001 );

define( "PGG_ERRNUM_EUCJP_NOVALUE",  5001 );
define( "PGG_ERRNUM_EUCJP_YOMI",     5002 );
define( "PGG_ERRNUM_EUCJP_BADFORMAT",5003 );
define( "PGG_ERRNUM_EUCJP_MULTI_MISSING",  5004 );

// error numbers no longer used
// define( "PGG_ERRNUM_MULTI_MISSING",  4001 );

/* ================================================================= */
 class pgg_check extends pgg_value
{
    var $encode;        // hidden tags maybe encoded
    var $count_pop_hidden_tags;
    var $err_msg; // repository of error messages by error_type
	
    var $session_vars;
    var $cookie_vars;
    var $post_vars;
    var $get_vars;

    
    /* ------------------------------------------------------------ */
    function pgg_check()
    {
        if( WORDY ) echo "<br><i><b>pggCheck instance created...</b></i><br>\n";
		$this->pgg_value();    // call constructor... 
		
        $err_msg_eng = array( 
            PGG_ERRNUM_CHAR_BADLENGTH => "<font color=red>Length not right</font>",
            PGG_ERRNUM_CHAR_NOVALUE =>   "<font color=red>Value not set</font>",
            PGG_ERRNUM_CHAR_BADFORMAT => "<font color=red>Wrong format</font>",
            PGG_ERRNUM_DATE_NOVALUE =>   "<font color=red>Date not set</font>",
            PGG_ERRNUM_DATE_BADDATE =>   "<font color=red>Date is invalid</font>",
            PGG_ERRNUM_NUM_NOTANUMBER => "<font color=red>Not a valid number</font>",
            PGG_ERRNUM_MULTI_MISSING => "<font color=red>Missing entry</font>",
        );
        
        $this->err_msg = $err_msg_eng;
        
        if( PGG_USE_SESSION ) {
            $this->pggCheck_ID = "pggCheck_id";
            $this->loadSession();
        }
        return TRUE;
    }
    /* ------------------------------------------------------------ */
    //                   push related functions                     //
    /* ------------------------------------------------------------ */
    function checkChar( $val, $length, $ereg_expr=NULL )
    {
        if( WORDY > 3 ) echo "<i>pggCheck::checkChar( $val, $length, $ereg_expr )</i><br>\n";
        
        $err_type = NULL;
        if( $length > 0  && !recurse_check_strlen( $val, $length ) ) {
            $err_type =  PGG_ERRNUM_CHAR_BADLENGTH;
        }
        if( $length == 0 && !recurse_check_strlen( $val, 0 ) ) {
            $err_type =  PGG_ERRNUM_CHAR_NOVALUE;
        }
        if( $ereg_expr ) {
            if( !recurse_ereg( $val, $ereg_expr )   ) 
            $err_type =  PGG_ERRNUM_CHAR_BADFORMAT;
        }
        return $err_type;
    }
    /* ------------------------------------------------------------ */
    function pushChar( 
		$var_name, 
		$length      = PGG_VALUE_MISSING_OK, 
		$ereg_expr   = NULL, 
		$default_val = NULL, 
		$err_msg     = NULL 
	)
    {
        if( WORDY ) echo "<br><i><strong>pggCheck::push_char</strong>( $var_name, $length, $ereg_expr, $default_val, $err_msg )</i><br>\n";
        $val = $this->getValue( $var_name );
        
		$err_type = $this->checkChar( $val, $length, $ereg_expr );
		
		if( $err_type ) { // found error. do error stuff. 
           	$val = $this->pushError( $var_name, $val, $err_type, $err_msg );
		}
		else {
			$val = $this->pushValue( $var_name, $val, $default_val );
		}
        return $val;
    }
    /* ------------------------------------------------------------ */
    function pushMult( 
		$var_name, 
		$repeat_num, 
		$connector, 
		$length      = PGG_VALUE_MISSING_OK, 
		$ereg_expr   = NULL, 
		$default_val = NULL, 
		$err_msg     = NULL 
	)
    {
        if( WORDY ) echo "<br><i><strong>pggCheck::push_mult</strong>( $var_name, $repeat_num, $connector, $length, $ereg_expr )...</i><br>\n";
        $multiple_value = $this->getValue( $var_name );
        if( !have_value( $multiple_value ) )
        {
            for( $i = 1; $i <= $repeat_num; $i++ ) 
            {
                $var = $var_name . sprintf( "_%d", $i );
                $val = $this->getValue( $var );
                if( have_value( $multiple_value ) && have_value( $val ) ) {
					$multiple_value .= $connector . $val;
				}
                elseif( have_value( $val ) ) {
					$multiple_value = $val;
				}
                if( WORDY > 3 ) echo "-->$i: $var = $val ($multiple_value)<br>\n";
            }
        }
		if( $err_type ) { // found error. do error stuff. 
           	$multiple_value = $this->pushError( $var_name, $multiple_value, $err_type, $err_msg );
		}
		else {
			$multiple_value = $this->pushValue( $var_name, $multiple_value, $default_val );
		}
        return $multiple_value;
    }
    /* ------------------------------------------------------------ */
    function pushDate( 
		$var_name, 
		$length=PGG_VALUE_MISSING_OK, 
		$dbar        = '-', 
		$default_val = NULL, 
		$err_msg     = NULL 
	)
    {
        if( WORDY ) echo "<br><i><strong>pggCheck::push_date</strong>( $var_name, $length, $dbar, $default_val, $err_msg )...</i><br>\n";
        $date = $this->getValue( $var_name );
		if( !$date ) 
		{
			$year  = $this->getValue( "{$var_name}_year"  );
			$month = $this->getValue( "{$var_name}_month" );
			$date  = $this->getValue( "{$var_name}_date"  );
			if( "$year" == "" || "$month" == "" || "$date" == "" ) 
			{
				$year  = $this->getValue( "{$var_name}_y"  );
				$month = $this->getValue( "{$var_name}_m" );
				$date  = $this->getValue( "{$var_name}_d"  );
				if( "$year" == "" || "$month" == "" || "$date" == "" ) {}
				else
				{
					$date = "{$year}{$dbar}{$month}{$dbar}{$date}";
				}
			}
			else 
			{
				$date = "{$year}{$dbar}{$month}{$dbar}{$date}";
			}
		}
		$err_type = $this->checkChar( $date, $length, $ereg_expr );
		// check for proper date
		if( !have_value( $err_type ) && have_value( $date ) ) { 
			list( $year, $month, $day ) = explode( $dbar, $date );
			if( !checkdate( $month, $day, $year ) ) {
				$err_type = PGG_ERRNUM_DATE_BADDATE;
			}
		}
		if( $err_type ) { // found error. do error stuff. 
           	$date = $this->pushError( $var_name, $date, $err_type, $err_msg );
		}
		else {
			$date = $this->pushValue( $var_name, $date, $default_val );
		}
		
        return $date;
    }
    /* ------------------------------------------------------------ */
    function pushNum( 
		$var_name, 
		$length=PGG_VALUE_MISSING_OK, 
		$ereg_expr   = NULL, 
		$min=NULL, 
		$max=NULL,		
		$default_val = NULL, 
		$err_msg     = NULL 
	)
    {
        if( WORDY ) echo "<br><i><strong>pggCheck::push_num</strong>( $var_name, $length, $default_val, $err_msg )</i><br>\n";
        $val = $this->getValue( $var_name );
        
		$err_type = $this->checkChar( $val, $length, $ereg_expr );
        if( !have_value( $err_type ) && !is_numeric( $val ) ) {
            $err_type = PGG_ERRNUM_NUM_NOTANUMBER;
		}
        if( !recurse_evaluation( $val, 'is_numeric' ) ) {
            $err_type = PGG_ERRNUM_NUM_NOTANUMBER;
        }
        if( $min && $max && ( $val < $min || $val > $max ) ) {
            $err_type = PGG_ERRNUM_NUM_NOTANUMBER;
        }
        if( $min && $val < $min ) {
            $err_type = PGG_ERRNUM_NUM_NOTANUMBER;
        }
        if( $max && $val > $max ) {
            $err_type = PGG_ERRNUM_NUM_NOTANUMBER;
        }
		
		if( $err_type ) { // found error. do error stuff. 
           	$val = $this->pushError( $var_name, $val, $err_type, $err_msg );
		}
		else {
			$val = $this->pushValue( $var_name, $val, $default_val );
		}
        return $val;
    }
    /* ------------------------------------------------------------ */
	function pushDual( $var_name, $var_name2=NULL, $err_msg=NULL, $filter=array() )
	{
		$right_val = $this->all_variables[ $var_name ];
		if( !$var_name2 ) $var_name2 = $var_name . '_2';
		$check_val = $this->pushChar( $var_name2 );
		
		if( !empty( $filter ) ) 
		if( $filter[ 'tolower' ] ) {
			$check_val = strtolower( $check_val );
		}
		
		if( have_value( $right_val ) && !have_value( $check_val ) ) {
            return $this->pushError( $var_name, $right_val, PGG_ERRNUM_PSWD_NOT_BOTH, $err_msg );
		}
		else if( $right_val != $check_val ) {
            return $this->pushError( $var_name, $right_val, PGG_ERRNUM_PSWD_NOTMATCH, $err_msg );
		}
		return $check_val;
	}
    /* ------------------------------------------------------------ */
    function pushMail( 
		$var_name, 
		$length=PGG_VALUE_MISSING_OK, 
		$default_val = NULL, 
		$err_msg     = NULL  )
    {
        if( WORDY ) echo "<br><i><strong>starting eucjp_mail</strong>( $var_name, $length, $default_val, $err_msg )...</i><br>\n";
        $val = $this->getValue( $var_name );
        
        $err_type = $this->checkChar( $val, $length, "[a-zA-Z0-9_.-]+@[a-zA-Z0-9_.-]+\.[a-zA-Z]+" );
        if( $err_type ) 
        {
            return $this->pushError( $var_name, $val, $err_type, $err_msg );
        }
        $val = strtolower( $val );
        $this->pushValue( $var_name, $val, $default_val );
        
        return $val;
    }
}
/* ================================================================= */



?>