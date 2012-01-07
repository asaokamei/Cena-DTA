<?php
namespace CenaDta\Util;

class Filter
{
    // +--------------------------------------------------------------- +
    // FILTER METHODS!!!
    // +--------------------------------------------------------------- +
    function trim( &$value ) {
        $value = trim( $value );
        return TRUE;
    }
    // +--------------------------------------------------------------- +
    /** sets default if empty. 
     */
    function setDefault( &$value, $option, &$loop=NULL ) {
        if( !Util::isValue( $value ) ) { // no value. set default...
            if( !is_array( $option ) ) { // default value specified.
                $value = $option;
            }
            else
            if( isset( $option[ 'value' ] ) ) { 
                $value = $option[ 'value' ];
            }
            if( $option[ 'break' ] ) {
                $loop = 'break';
            }
        }
        return TRUE;
    }
    // +--------------------------------------------------------------- +
    /** removes null (char(0)). 
     */
    function noNull( &$value, $option=NULL ) {
        $value = str_replace( "\0", '', $value );
        return TRUE;
    }
    // +--------------------------------------------------------------- +
    /** completely filters out if bad encoding. 
     */
    function encoding( &$value, $option=NULL ) {
        if( is_array( $option ) && isset( $option[ 'charset' ] ) ) {
            $charset = $option[ 'charset' ];
        }
        else
        if( Util::isValue( $option ) ) {
            $charset = $option;
        }
        else
        if( mb_internal_encoding() ) {
            $charset = mb_internal_encoding();
        }
        else {
            $charset = self::$default_charset;
        }
        if( !mb_check_encoding( $value, $charset ) ) {
            $value = '';
        }
        return TRUE;
    }
    // +--------------------------------------------------------------- +
    /** verifies if required value has a value. 
     */
    function required( $value, $option, &$loop=NULL ) 
    {
        if( Util::isValue( $value ) ) { // have value. must be OK...
            return TRUE;
        }
        $required = FALSE;
        if( !is_array( $option ) && $option ) { // required value not there.
            $required = TRUE;
        }
        else
        if( isset( $option[ 'required' ] ) && $option[ 'required' ] ) {
            $required = TRUE;
        }
        if( $required ) {
            if( isset( $option[ 'loop' ] ) && $option[ 'loop' ] == 'break' ) {
                $loop = 'break';
            }
            return FALSE;
        }
        return TRUE;
    }
    // +--------------------------------------------------------------- +
    function string( &$value, $option=array() ) {
        if( WORDY > 5 ) echo "string( $value, $option )";
        if( $option == 'lower' ) {
            $value = strtolower( $value );
        }
        else
        if( $option == 'upper' ) {
            $value = strtoupper( $value );
        }
        else
        if( $option == 'capital' ) {
            $value = strcapital( $value );
        }
        if( WORDY > 5 ) echo " --> result=$value <br>";
        return TRUE;
    }
    // +--------------------------------------------------------------- +
    function sanitizeEmail( &$value, $option=array() ) {
        $value = self::sanitize( $value, FILTER_SANITIZE_EMAIL, $option );
        return TRUE;
    }
    // +--------------------------------------------------------------- +
    function sanitize( &$value, $option ) {
        if( !filter_var( $value, $option  ) ) {
            $value = '';
        }
        return TRUE;
    }
    // +--------------------------------------------------------------- +
    // VERIFIERS METHODS!!!
    // +--------------------------------------------------------------- +
    /** check if value is a code, number, alpahbets, and '-' & '_'.
     */
    function code( $val, $option ) {
        return self::pattern( $val, '[-_0-9a-zA-Z]*' );
    }
    // +--------------------------------------------------------------- +
    /** check if date is a valid date with checkdate function. 
     *  assume date is in 'YYYY-MM-DD' format. 
     *  specify $option[ dbar ] (i.e. '/'.) if date is 'YYYY/MM/DD'. 
     */
    function checkDate( $val, $option ) {
        if( !Util::isValue( $val ) ) return FALSE;
        $dbar = '-';
        if( isset( $option[ 'dbar' ] ) ) $dbar = $option[ 'dbar' ];
        $items = explode( $dbar, $val );
        if( count( $items ) == 3 ) {
            return @checkdate( $items[1], $items[2], $items[0] );
        }
        return FALSE;
    }
    // +--------------------------------------------------------------- +
    /** checks for easy mail format. 
     */
    function checkMail( $val, $option ) {
        return self::pattern( $val, "[a-zA-Z0-9_.-]+@[a-zA-Z0-9_.-]+\.[a-zA-Z]+" );
    }
    // +--------------------------------------------------------------- +
    /** checks for length of character. 
     *  $option specifies the exact length of char if in numeric.
     *  $option[ min ] speficies the minimum length. 
     *  $option[ max ] speficies the maximum length. 
     */
    function maxlength( $val, $option ) {
        $ok  = TRUE;
        $len = strlen( $val );
        if( is_array( $option ) ) {
            if( isset( $option[ 'min' ] ) && $len < $option[ 'min' ] ) {
                $ok = FALSE;
            }
            if( isset( $option[ 'max' ] ) && $len > $option[ 'max' ] ) {
                $ok = FALSE;
            }
        }
        else {
            if( $len != $option ) {
                $ok = FALSE;
            }
        }
        return $ok;
    }
    // +--------------------------------------------------------------- +
    /**
     */
    function min( $val, $option ) {
        $ok = TRUE;
        if( $val < $option ) {
            $ok = FALSE;
        }
        return $ok;
    }
    // +--------------------------------------------------------------- +
    /**
     */
    function max( $val, $option ) {
        $ok = TRUE;
        if( $val > $option ) {
            $ok = FALSE;
        }
        return $ok;
    }
    // +--------------------------------------------------------------- +
    /**
     */
    function range( $val, $option ) {
        $ok  = TRUE;
        if( !is_numeric( $val ) ) return FALSE;
        if( isset( $option[ 'min' ] ) && $val < $option[ 'min' ] ) {
            $ok = FALSE;
        }
        if( isset( $option[ 'max' ] ) && $val > $option[ 'max' ] ) {
            $ok = FALSE;
        }
        if( isset( $option[ 'lt' ] ) && $val < $option[ 'lt' ] ) {
            $ok = FALSE;
        }
        if( isset( $option[ 'gt' ] ) && $val > $option[ 'gt' ] ) {
            $ok = FALSE;
        }
        if( isset( $option[ 'le' ] ) && $val <= $option[ 'le' ] ) {
            $ok = FALSE;
        }
        if( isset( $option[ 'ge' ] ) && $val >= $option[ 'ge' ] ) {
            $ok = FALSE;
        }
        return $ok;
    }
    // +--------------------------------------------------------------- +
    /**
     */
    function sameas( $val, $option ) {
        $ok = FALSE;
        if( $val == $option ) {
            $ok = TRUE;
        }
        return $ok;
    }
    // +--------------------------------------------------------------- +
    /**
     */
    function sameempty( $val, $option ) {
        $ok = TRUE;
        if( $val ) {
            $ok = FALSE;
        }
        return $ok;
    }
    // +--------------------------------------------------------------- +
    /**
     */
    function pattern( $val, $option ) {
        if( WORDY ) print "pattern( $val, $option )<br />\n";
        $ok = preg_match( "/^{$option}\$/", $val );
        return !!$ok;
    }
    // +--------------------------------------------------------------- +
}

class FilterJa extends Filter
{
    // +--------------------------------------------------------------- +
    function apply() {
        Dio::setFilterClass( __CLASS__ );
        Dio::setFilterMethods( 'mbConvert', TRUE );
        Dio::setFilterMethods( 'mbCheckKana',   FALSE );
        Dio::setFilterMethods( 'hankaku', FALSE );
        Dio::setFilterMethods( 'hankaku', FALSE );
        Dio::setFilterMethods( 'hankaku', FALSE );
    }
    // +--------------------------------------------------------------- +
    /** only zenkaku-katakana allowed. 
     */
    function zenKanaOnly( $val, $option=array() ) {
        return self::mbCheckKana( $val, 'zen_kana_only' );
    }
    // +--------------------------------------------------------------- +
    /** only hankaku-katakana allowed. 
     */
    function hanKanaOnly( $val, $option=array() ) {
        return self::mbCheckKana( $val, 'han_kana_only' );
    }
    // +--------------------------------------------------------------- +
    /** only zenkaku-hiragana allowed. 
     */
    function zenHiraOnly( $val, $option=array() ) {
        return self::mbCheckKana( $val, 'zen_hira_only' );
    }
    // +--------------------------------------------------------------- +
    /** only hankaku allowed. 
     */
    function hanKakuOnly( $val, $option=array() ) {
        return self::mbCheckKana( $val, 'hankaku_only' );
    }
    // +--------------------------------------------------------------- +
    /** checks for kana type. 
     */
    function mbCheckKana( $val, $option=array() ) {
        $ereg_str = FALSE;
        switch( $option ) 
        {
            case 'zen_kana_only': // only zenkaku-katakana
                $ereg_str = "^[　ー−‐ァ-ヶ]*$";
                break;
            case 'han_kana_only': // only hankaku-katakana
                $ereg_str = "^[ -ヲ-゜]*$";
                break;
            case 'zen_hira_only': // only zenkaku-hiragana
                $ereg_str = "^[　ー−‐ぁ-ん]*$";
                break;
            case 'hankaku_only': // only hankaku
                $ereg_str = "^[ !-~]*$";
                break;
            default:
                $ereg_str = "";
                break;
        }
        $ok = TRUE;
        if( $ereg_str ) {
            $ok = mb_ereg( "^{$ereg_str}$", $val );
        }
        return !!$ok;
    }
    // +--------------------------------------------------------------- +
    /**
     */
    function mbConvert( &$val, $option=array() ) {
        switch( $option ) {
            case 'hankaku':		$str = 'aks';		break;
            case 'han_kana':	$str = 'kh';		break;
            case 'zen_hira':	$str = 'HVc';		break;
            case 'zen_kana':	$str = 'KVC';		break;
            default:			$str = 'KV';		break;
        }
        //echo "$str $val ";
        $val = mb_convert_kana( $val, $str, 'UTF-8' );
        //echo "$str $val";
        return TRUE;
    }
    // +--------------------------------------------------------------- +
}


?>