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

define( "PGG_EUCJP_JP_ZENKATA",       "JP_STANDARD" ); // no hankaku-kana.
define( "PGG_EUCJP_YOMI_ZENKATA_ONLY", "ZENKATA_ONLY" );
define( "PGG_EUCJP_YOMI_HANKATA_ONLY", "HANKATA_ONLY"  );
define( "PGG_EUCJP_YOMI_ZENHIRA_ONLY", "ZENHIRA_ONLY" );
define( "PGG_EUCJP_YOMI_HANKAKU_ONLY", "HANKAKU_ONLY" );
define( "PGG_EUCJP_YOMI_ZENKAKU_ONLY", "ZENKAKU_ONLY" );
define( "PGG_EUCJP_YOMI_ZENKATA",      "ZENKATA" );
define( "PGG_EUCJP_YOMI_HANKATA",      "HANKATA"  );
define( "PGG_EUCJP_YOMI_ZENHIRA",      "ZENHIRA" );
define( "PGG_EUCJP_YOMI_HANKAKU",      "HANKAKU" );
define( "PGG_EUCJP_YOMI_ZENKAKU",      "ZENKAKU" );

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

define( 'PGG_ERRNUM_ENTER_PASSWORD', 9001 );
define( 'PGG_ERRNUM_PSWD_NOTMATCH',  9002 );
define( 'PGG_ERRNUM_PSWD_NOT_BOTH',  9003 );
define( 'PGG_ERRNUM_ID_ALREAD_USED', 9100 );

define( 'PGG_ERRNUM_OTHER_ERROR',    9999 );

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
        if( WORDY ) echo "<br><i><b>pggJPN instance created...</b></i><br>\n";
		$this->pgg_value();    // call constructor... 
		
        $err_msg_jpn = array( // Japanese error messages 
            PGG_ERRNUM_CHAR_BADLENGTH => "<font color=red>←　入力文字数が間違っています。</font>",
            PGG_ERRNUM_CHAR_NOVALUE =>   "<font color=red>←　値を入力してください。</font>",
            PGG_ERRNUM_CHAR_BADFORMAT => "<font color=red>←　値に問題があります。</font>",
            PGG_ERRNUM_DATE_NOVALUE =>   "<font color=red>←　日付を入力してください。</font>",
            PGG_ERRNUM_DATE_BADDATE =>   "<font color=red>←　無効な日付です。</font>",
            PGG_ERRNUM_NUM_NOTANUMBER => "<font color=red>←　半角数字で入力してください。</font>",
            PGG_ERRNUM_MULTI_MISSING =>  "<font color=red>←　全項目を入力してください。</font>",
            PGG_ERRNUM_EUCJP_NOVALUE =>  "<font color=red>←　値を入力してください。</font>",
            PGG_ERRNUM_EUCJP_YOMI =>     "<font color=red>←　カタカナのみで入力してください。</font>",
            PGG_ERRNUM_EUCJP_BADFORMAT =>"<font color=red>←　入力に問題があります。</font>",
            PGG_ERRNUM_EUCJP_MULTI_MISSING =>  "<font color=red>←　全項目を入力してください。</font>",
            PGG_ERRNUM_ID_ALREAD_USED =>  "<font color=red>←　すでにIDは使われています。</font>",
			PGG_ERRNUM_ENTER_PASSWORD => "<font color=red>←　パスワードを入力してください</font>", 
			PGG_ERRNUM_PSWD_NOTMATCH  => "<font color=red>←　確認と一致していません</font>", 
			PGG_ERRNUM_PSWD_NOT_BOTH  => "<font color=red>←　確認用も入力してください</font>", 
			PGG_ERRNUM_NUM_OUTOFRANGE => "<font color=red>←　数字の指定範囲以外です</font>", 
        );
        
        $this->err_msg = $err_msg_jpn;
        
        if( PGG_USE_SESSION ) {
            $this->pggCheck_ID = "pggCheck_id";
            $this->loadSession();
        }
        return TRUE;
    }
    /* ------------------------------------------------------------ */
    function setupCell()
    {
        $this->err_msg = array( // Japanese error messages 
            PGG_ERRNUM_CHAR_BADLENGTH => "<span style=\"color:red;\">←入力文字数が間違い</span>",
            PGG_ERRNUM_CHAR_NOVALUE =>   "<span style=\"color:red;\">←値が未入力</span>",
            PGG_ERRNUM_CHAR_BADFORMAT => "<span style=\"color:red;\">←値が不正です</span>",
            PGG_ERRNUM_DATE_NOVALUE =>   "<span style=\"color:red;\">←日付を入力</span>",
            PGG_ERRNUM_DATE_BADDATE =>   "<span style=\"color:red;\">←無効な日付</span>",
            PGG_ERRNUM_NUM_NOTANUMBER => "<span style=\"color:red;\">←半角数字で入力</span>",
            PGG_ERRNUM_MULTI_MISSING =>  "<span style=\"color:red;\">←全項目を入力</span>",
            PGG_ERRNUM_EUCJP_NOVALUE =>  "<span style=\"color:red;\">←未入力</span>",
            PGG_ERRNUM_EUCJP_YOMI =>     "<span style=\"color:red;\">←カタカナのみで入力</span>",
            PGG_ERRNUM_EUCJP_BADFORMAT =>"<span style=\"color:red;\">←入力に問題あり</span>",
            PGG_ERRNUM_EUCJP_MULTI_MISSING =>  "<span style=\"color:red;\">←全項目を入力</span>",
            PGG_ERRNUM_ID_ALREAD_USED =>  "<span style=\"color:red;\">←すでにIDは使われています</span>",
			PGG_ERRNUM_ENTER_PASSWORD => "<span style=\"color:red;\">←パスワード入力</span>", 
			PGG_ERRNUM_PSWD_NOTMATCH  => "<span style=\"color:red;\">←確認と不一致です</span>", 
			PGG_ERRNUM_PSWD_NOT_BOTH  => "<span style=\"color:red;\">←確認用が未入力</span>", 
			PGG_ERRNUM_NUM_OUTOFRANGE => "<span style=\"color:red;\">←数字の指定範囲外</span>", 
        );
    }
    /* ------------------------------------------------------------ */
    //                   push related functions                     //
    /* ------------------------------------------------------------ */
    function checkChar( $val, $length, $ereg_expr=NULL )
    {
        return $this->checkEucJP( $val, $length, $ereg_expr );
    }
    /* ------------------------------------------------------------ */
    function checkEucJP( &$val, $length, $ereg_expr=NULL, $convert_str=PGG_EUCJP_JP_ZENKATA )
    {
        if( WORDY ) echo "<i>pgg_check::checkEucJP( $val, $length, $ereg_expr, $convert_str )...</i><br>\n";
        // convert to proper {zen|han}-kaku and {kata|hira}-kana string. 
        $val = $this->mbConvert( $val, $convert_str );
        recurse_trim( $val );
        
        if( !have_value( $val ) && $length != PGG_VALUE_MISSING_OK ) {
            return PGG_ERRNUM_EUCJP_NOVALUE;
        }
        // check if the value uses only specified characters...
        if( have_value( $val ) && have_value( $ereg_str ) && !$this->checkMbCharacters( $val, $convert_str ) ) {
            return PGG_ERRNUM_EUCJP_YOMI;
        }
        // check if the value is in proper format...
        if( have_value( $val ) && have_value( $ereg_expr ) ) {
			if( is_array( $ereg_expr ) ) $ereg_expr = implode( '|', $ereg_expr );
			if( function_exists( "mb_ereg" ) ) {
				if( !recurse_ereg( $val, $ereg_expr, TRUE ) ) return PGG_ERRNUM_EUCJP_BADFORMAT;
			}
			else {
				if( !recurse_ereg( $val, $ereg_expr ) ) return PGG_ERRNUM_EUCJP_BADFORMAT;
			}
		}
        
        return NULL;
    }
    /* ------------------------------------------------------------ */
    function checkCharLength( $var_name, $min=FALSE, $max=FALSE, $err_type=PGG_ERRNUM_CHAR_BADLENGTH, $err_msg=NULL )
    {
		// 全角文字数のチェック。
		// 配列データには適用不可！
		if( WORDY > 3 ) echo "checkMinChars( $var_name, $length, $err_type, $err_msg )...<br>\n";
		if( WORDY && is_array( $this->all_variables[ $var_name ] ) ) {
			echo "<font color=red>checkMinCharsは配列に適用できません</font><br>";
		}
		$string = $this->all_variables[ $var_name ];
		
		$string = mb_convert_kana( $string, "s" );  // 全角スペースを半角スペースに変換
		$string = str_replace( ' ', '', $string ); 
		$string_count = mb_strlen( $string );
		//  echo "$string_count<br>\n";
		$result = TRUE;
		if( $min && $string_count < $min ) {
			$result = FALSE;
			$m_err[] = "{$min}文字以上";
		}
		if( $max && $string_count > $max ) {
			$result = FALSE;
			$m_err[] = "{$max}文字以下";
		} 
		if( !$result ){
			if( !$err_msg ) {
				$m_err = implode( '、', $m_err );
				$err_msg = "<font color=red>←　入力は{$m_err}としてください</font>";
			}
			$string = $this->pushError( $var_name, $string, $err_type, $err_msg );
		}
		return $result;
    }
    /* ------------------------------------------------------------ */
    function checkMbCharacters( $value, $convert_str )
    {
        if( WORDY ) echo "<i>pgg_check::checkMbCharacters( $value, $convert_str )...</i><br>\n";
		if( !$value ) return TRUE;
        switch( $convert_str ) 
        {
            case PGG_EUCJP_YOMI_ZENKATA_ONLY:
                $ereg_str = "^[　ー−‐ァ-ヶ]*$";
                break;
            case PGG_EUCJP_YOMI_HANKATA_ONLY:
                $ereg_str = "^[ -ヲ-゜]*$";
                break;
            case PGG_EUCJP_YOMI_ZENHIRA_ONLY:
                $ereg_str = "^[　ー−‐ぁ-ん]*$";
                break;
            case PGG_EUCJP_YOMI_HANKAKU_ONLY:
                $ereg_str = "^[ !-~]*$";
                break;
            case PGG_EUCJP_YOMI_ZENKAKU_ONLY:
                $ereg_str = "^*$"; // supposed to convert anything...
                break;
            default:
                $ereg_str = "";
                break;
        }
        if( $ereg_str && function_exists( "mb_ereg" ) ) {
			mb_regex_encoding( 'UTF-8' );
            return recurse_ereg( $value, $convert_str, TRUE );
        }
        return TRUE;
    }
    /* ------------------------------------------------------------ */
    function mbConvert( $value, $convert_str )
    {
        if( WORDY ) echo "<i>pgg_check::mbConvert( $value, $convert_str )...</i>";
		if( !$value ) return $value;
        switch( $convert_str ) {
            case PGG_EUCJP_YOMI_ZENKATA_ONLY:
                $mb_convert_str = "KVSC";
                break;
            case PGG_EUCJP_YOMI_ZENKATA:
                $mb_convert_str = "KVC";
                break;
            case PGG_EUCJP_YOMI_HANKATA_ONLY:
                $mb_convert_str = "khs";
				$do_trim = TRUE;
                break;
            case PGG_EUCJP_YOMI_HANKATA:
                $mb_convert_str = "kh";
                break;
            case PGG_EUCJP_YOMI_ZENHIRA_ONLY:
                $mb_convert_str = "HVSc";
                break;
            case PGG_EUCJP_YOMI_ZENHIRA:
                $mb_convert_str = "HVc";
                break;
            case PGG_EUCJP_YOMI_HANKAKU_ONLY:
                $mb_convert_str = "aKs";
				$do_trim = TRUE;
                break;
            case PGG_EUCJP_YOMI_HANKAKU:
                $mb_convert_str = "aK";
                break;
            case PGG_EUCJP_YOMI_ZENKAKU_ONLY:
                $mb_convert_str = "ASKV"; // supposed to convert anything...
                break;
            case PGG_EUCJP_YOMI_ZENKAKU:
                $mb_convert_str = "ASV"; // supposed to convert anything...
                break;
            case PGG_EUCJP_JP_ZENKATA:
                $mb_convert_str = "KV";
				break;
            default:
                $mb_convert_str = "";
                break;
        }
        $value = recurse_mb_convert_kana( $value, $mb_convert_str );
        if( WORDY ) echo " -->value=$value ($mb_convert_str)<br>\n";
        return $value;
    }
    /* ------------------------------------------------------------ */
    //      MULTI-BYTE and JAPANESE LANGUAGE SPECIFIC ROUTINES      //
    /* ------------------------------------------------------------ */
    function pushChar( 
		$var_name, 
		$length      = PGG_VALUE_MISSING_OK, 
		$ereg_expr   = NULL, 
		$default_val = '', 
		$err_msg     = NULL 
	)
    {
        if( WORDY ) echo "<br><i><strong>pggCheck::push_char</strong>( $var_name, $length, $ereg_expr, $default_val, $err_msg )</i><br>\n";
		return $this->pushEucJP( 
			$var_name, 
			$length, 
			PGG_EUCJP_JP_ZENKATA, // for $convert_str
			$ereg_expr,
			$default_val, 
			$err_msg
		);
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
		return $this->eucjpMult( 
			$var_name, $repeat_num, $connector, $length, 
			PGG_EUCJP_JP_ZENKATA,  // for $convert_str
			$ereg_expr, $default_val, $err_msg
		);
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
		$err_type = $this->checkEucJP( $date, $length, $ereg_expr, PGG_EUCJP_YOMI_HANKAKU_ONLY );
		// check for proper date
		if( !have_value( $err_type ) && have_value( $date ) ) { 
			if( !recurse_check_date( $dbar, $date ) ) {
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
    function pushYM( 
		$var_name, 
		$length=PGG_VALUE_MISSING_OK, 
		$dbar        = '-', 
		$default_val = NULL, 
		$err_msg     = NULL 
	)
    {
        if( WORDY ) echo "<br><i><strong>pggCheck::pushYM</strong>( $var_name, $length, $dbar, $default_val, $err_msg )...</i><br>\n";
        $date = $this->getValue( $var_name );
		if( !$date ) 
		{
			$year  = $this->getValue( "{$var_name}_year"  );
			$month = $this->getValue( "{$var_name}_month" );
			if( "$year" == "" || "$month" == "" ) 
			{
				$year  = $this->getValue( "{$var_name}_y"  );
				$month = $this->getValue( "{$var_name}_m" );
				if( "$year" == "" || "$month" == "" ) {}
				else
				{
					$date = "{$year}{$dbar}{$month}";
				}
			}
			else 
			{
				$date = "{$year}{$dbar}{$month}";
			}
		}
		$err_type = $this->checkEucJP( $date, $length, $ereg_expr, PGG_EUCJP_YOMI_HANKAKU_ONLY );
		// check for proper date
		if( !have_value( $err_type ) && have_value( $date ) ) { 
			if( !recurse_check_date( $dbar, $date. '-01' ) ) {
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
    function pushTime( 
		$var_name, 
		$length=PGG_VALUE_MISSING_OK, 
		$dbar        = ':', 
		$default_val = NULL, 
		$err_msg     = NULL 
	)
    {
        if( WORDY ) echo "<br><i><strong>pggCheck::push_date</strong>( $var_name, $length, $dbar, $default_val, $err_msg )...</i><br>\n";
        $time = $this->getValue( $var_name );
		if( !$time ) 
		{
			$hour  = $this->getValue( "{$var_name}_hour"  );
			$min   = $this->getValue( "{$var_name}_min" );
			$sec   = $this->getValue( "{$var_name}_sec"  );
			if( "$hour" == "" || "$min" == "" || "$sec" == "" ) 
			{
				$hour  = $this->getValue( "{$var_name}_h"  );
				$min = $this->getValue( "{$var_name}_m" );
				$sec  = $this->getValue( "{$var_name}_s"  );
				if( "$hour" == "" || "$min" == "" || "$sec" == "" ) 
				{
					$hour  = $this->getValue( "{$var_name}_1"  );
					$min = $this->getValue( "{$var_name}_2" );
					$sec  = $this->getValue( "{$var_name}_3"  );
					if( "$hour" == "" || "$min" == "" || "$sec" == "" ) {}
					else
					{
						$time = "{$hour}{$dbar}{$min}{$dbar}{$sec}";
					}
				}
				else
				{
					$time = "{$hour}{$dbar}{$min}{$dbar}{$sec}";
				}
			}
			else 
			{
				$time = "{$hour}{$dbar}{$min}{$dbar}{$sec}";
			}
		}
		$err_type = $this->checkEucJP( $time, $length, $ereg_expr, PGG_EUCJP_YOMI_HANKAKU_ONLY );
		// check for proper date
		if( !have_value( $err_type ) && have_value( $time ) ) { 
		}
		if( $err_type ) { // found error. do error stuff. 
           	$time = $this->pushError( $var_name, $time, $err_type, $err_msg );
		}
		else {
			$time = $this->pushValue( $var_name, $time, $default_val );
		}
		
        return $time;
    }
    /* ------------------------------------------------------------ */
    function pushDateTime( 
		$var_name, 
		$length=PGG_VALUE_MISSING_OK, 
		$default_val = NULL, 
		$err_msg     = NULL 
	)
    {
        if( WORDY ) echo "<br><i><strong>pggCheck::push_date</strong>( $var_name, $length, $default_val, $err_msg )...</i><br>\n";
        $datetime = $this->getValue( $var_name );
		if( !$datetime ) 
		{
			$year  = $this->getValue( "{$var_name}_year"  );
			$month = $this->getValue( "{$var_name}_month" );
			$date  = $this->getValue( "{$var_name}_date"  );
			$hour  = $this->getValue( "{$var_name}_hour"  );
			$min   = $this->getValue( "{$var_name}_min" );
			$sec   = $this->getValue( "{$var_name}_sec"  );
			if( $year && $month && $date && $hour && $min && $sec ) {
				$datetime = "{$year}-{$month}-{$date} {$hour}:{$min}:{$sec}";
			}
			else {
				$datetime = NULL;
			}
		}
		$err_type = $this->checkEucJP( $date, $length, $ereg_expr, PGG_EUCJP_YOMI_HANKAKU_ONLY );
		// check for proper date
		if( !have_value( $err_type ) && have_value( $datetime ) ) { 
			list( $date, $time ) = explode( ' ', $datetime );
			if( !recurse_check_date( '-', $date ) ) {
				$err_type = PGG_ERRNUM_DATE_BADDATE;
			}
		}
		if( $err_type ) { // found error. do error stuff. 
           	$date = $this->pushError( $var_name, $datetime, $err_type, $err_msg );
		}
		else {
			$date = $this->pushValue( $var_name, $datetime, $default_val );
		}
		
        return $date;
    }
    /* ------------------------------------------------------------ */
    function eucjpNum( 
		$var_name, 
		$length=PGG_VALUE_MISSING_OK, 
		$ereg_expr   = NULL, 
		$min=NULL, 
		$max=NULL,		
		$default_val = 0, 
		$err_msg     = NULL 
		)
    {
        if( WORDY ) echo "<br><i><strong>pggCheck::push_num</strong>( $var_name, $length, $min, $max )</i><br>\n";
        $val = $this->getValue( $var_name );
        
        $err_type = $this->checkEucJP( $val, $length, $ereg_expr, PGG_EUCJP_YOMI_HANKAKU_ONLY );
        if( $err_type ) 
        {
            return $this->pushError( $var_name, $val, $err_type );
        }   
        if( $val && !recurse_evaluation( $val, 'is_numeric' ) ) {
            $err_type = PGG_ERRNUM_NUM_NOTANUMBER;
        }
        if( $val && $min && $max && ( $val < $min || $val > $max ) ) {
            $err_type = PGG_ERRNUM_NUM_OUTOFRANGE;
        }
        if( $val && $min && $val < $min ) {
            $err_type = PGG_ERRNUM_NUM_OUTOFRANGE;
        }
        if( $val && $max && $val > $max ) {
            $err_type = PGG_ERRNUM_NUM_OUTOFRANGE;
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
    function pushMail( 
		$var_name, 
		$length=PGG_VALUE_MISSING_OK, 
		$default_val = '', 
		$err_msg     = NULL  
		)
    {
        if( WORDY ) echo "<br><i><strong>pggCheck::eucjp_mail</strong>( $var_name, $length )</i><br>\n";
        $val = $this->getValue( $var_name );
        
        $err_type = $this->checkEucJP( $val, $length, 
            "[a-zA-Z0-9_.-]+@[a-zA-Z0-9_.-]+\.[a-zA-Z]+", PGG_EUCJP_YOMI_HANKAKU_ONLY );
        if( $err_type ) 
        {
            return $this->pushError( $var_name, $val, $err_type, $err_msg );
        }
        $val = strtolower( $val );
        $this->pushValue( $var_name, $val, $default_val );
        
        return $val;
    }
    /* ------------------------------------------------------------ */
	function pushDual( $var_name, $var_name2=NULL, $err_msg=NULL, $filter=array() )
	{
		$right_val = $this->all_variables[ $var_name ];
		if( !$var_name2 ) $var_name2 = $var_name . '_2';
		$check_val = $this->pushChar( $var_name2 );
		
		if( !empty( $filter ) ) 
		if( $filter[ 'tolower' ] == TRUE ) {
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
    function pushEucJP( 
        $var_name, 
        $length=PGG_VALUE_MISSING_OK, 
        $convert_str=PGG_EUCJP_JP_ZENKATA, 
        $ereg_expr  =NULL,
		$default_val='', 
		$err_msg    =NULL 
		)
    {
        if( WORDY ) echo "<br><i><strong>pgg_check::pushEucJP( $var_name, $length, $convert_str, $ereg_expr )...</strong></i><br>\n";
        $val = $this->getValue( $var_name );
        if( WORDY > 3 ) echo "--> found  '{$val}'<br>\n";
        
        $err_type = $this->checkEucJP( $val, $length, $ereg_expr, $convert_str );
        if( WORDY > 3 ) echo "--> error_type '{$err_type}'<br>\n";
		
		if( $err_type ) { // found error. do error stuff. 
           	$val = & $this->pushError( $var_name, $val, $err_type, $err_msg );
		}
		else {
	        if( WORDY > 3 ) echo ">>is_null=" . is_null( $val ) . "<br>\n";
			$val = & $this->pushValue( $var_name, $val, $default_val );
		}
        if( WORDY > 3 ) echo "--> returning '{$val}'<br>\n";
        return $val;
    }
    /* ------------------------------------------------------------ */
    function eucjpZenkaku( $var_name, $length=PGG_VALUE_MISSING_OK, $ereg_expr=NULL, $default='', $err_msg=NULL )
    {
        return $this->pushEucJP( $var_name, $length, PGG_EUCJP_YOMI_ZENKAKU_ONLY, $ereg_expr, $default, $err_msg );
    }
    /* ------------------------------------------------------------ */
    function eucjpHankaku( $var_name, $length=PGG_VALUE_MISSING_OK, $ereg_expr=NULL, $default='', $err_msg=NULL )
    {
        return $this->pushEucJP( $var_name, $length, PGG_EUCJP_YOMI_HANKAKU_ONLY, $ereg_expr, $default, $err_msg );
    }
    /* ------------------------------------------------------------ */
    function eucjpKanaOnly( $var_name, $length=PGG_VALUE_MISSING_OK, $ereg_expr=NULL, $default='', $err_msg=NULL )
    {
        return $this->pushEucJP( $var_name, $length, PGG_EUCJP_YOMI_ZENKATA_ONLY, $ereg_expr, $default, $err_msg );
    }
    /* ------------------------------------------------------------ */
    function eucjpHiraOnly( $var_name, $length=PGG_VALUE_MISSING_OK, $ereg_expr=NULL, $default='', $err_msg=NULL )
    {
        return $this->pushEucJP( $var_name, $length, PGG_EUCJP_YOMI_ZENHIRA_ONLY, $ereg_expr, $default, $err_msg );
    }
    /* ------------------------------------------------------------ */
    function eucjpMult( 
        $var_name, 
        $repeat_num, 
        $connector, 
        $length=PGG_VALUE_MISSING_OK, 
        $convert_str=PGG_EUCJP_JP_ZENKATA, 
        $ereg_expr=NULL,
		$default_val = '', 
		$err_msg     = NULL 
		)
    {
        if( WORDY ) echo "<br><i><strong>pggCheck::eucjp_mult</strong>( $var_name, $length, $connector, $length, $convert_str, $ereg_expr )</i><br>\n";
        $multiple_value = "";
        if( !$multiple_value = $this->getValue( $var_name ) ) 
        {
            for( $i = 1; $i <= $repeat_num; $i++ ) 
            {
                $var = $var_name . sprintf( "_%d", $i );
                $val = $this->getValue( $var );
                if( $multiple_value && $val ) $multiple_value .= $connector . $val;
                elseif( $val ) $multiple_value = $val;
                if( WORDY > 3 ) echo "-->$i: {$multiple_value}, $var = $val<br>\n";
            }
        }
        $err_type = $this->checkEucJP(  $multiple_value, $length, $ereg_expr, $convert_str );
        
		if( $err_type ) { // found error. do error stuff. 
           	$val = $this->pushError( $var_name, $multiple_value, $err_type, $err_msg );
		}
		else {
			$val = $this->pushValue( $var_name, $multiple_value, $default_val );
		}
        return $multiple_value;
    }
    /* ------------------------------------------------------------ */
}
/* ================================================================= */



?>