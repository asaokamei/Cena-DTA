<?php
/* ========================================================================== */
// class.web_io.php
// 
// a class for Input/Output of PHP variables using standard WEB/HTTP 
// environment; i.e. POST, SESSION, COOKIE, etc. 
/* ========================================================================== */

if( !defined( "WORDY" ) ) define( "WORDY",  0 ); // very wordy...

define( "WEBIO_ENCODE_FLAG",   "WEBIO.saveID" );
define( "WEBIO_ENCODE_NONE",   "none" );
define( "WEBIO_ENCODE_BASE64", "base64" );
define( "WEBIO_ENCODE_CRYPT",  "base64" ); // not supported yet!
if( !defined( "WEBIO_ENCODE_TYPE" ) ) define( "WEBIO_ENCODE_TYPE", WEBIO_ENCODE_BASE64 );

/* ========================================================================== */
class web_io
{
    var $webIO_save_id;  // 
    var $crypt_passwd;   // 
    
    // +-------------------------------------------------------------+
    function web_io( $save_id='', $passwd='' ) 
    {
        if( !have_value( $save_id ) ) { $save_id = WEBIO_ENCODE_FLAG; }
        if( "$passwd" != "" ) $this->crypt_passwd  = $passwd;
        
        if( WEBIO_SESSION_ID_FOLDER ) 
        {
            $this->webIO_save_id .= '.' . dirname( $_SERVER['PHP_SELF'] );
            if( WEBIO_SESSION_ID_PARENT ) {
                $this->use_parent_session_data = TRUE;
            } else {
                $this->use_parent_session_data = FALSE;
            }
        }
    }
    // +-------------------------------------------------------------+
    function savePost( $data, $save_id='',$encode=WEBIO_ENCODE_TYPE )
    {
        if( WORDY > 4 ) echo "<i>web_io::savePost( $data, $save_id, $encode )</i>...<br>\n";
        if( !have_value( $save_id ) ) { $save_id = WEBIO_ENCODE_FLAG; }
        
        $val  = web_io::encodeData( $data, $encode );
        $htag = "<input type='hidden' name='{$save_id}' value='{$val}'>";
        
        return $htag;
    }
    // +-------------------------------------------------------------+
    function loadPost( $save_id='',$encode=WEBIO_ENCODE_TYPE )
    {
        if( WORDY > 4 ) echo "<i>web_io::loadPost( $save_id, $encode )</i>...<br>\n";
        if( !have_value( $save_id ) ) { $save_id = WEBIO_ENCODE_FLAG; }
        
        $data = NULL;
        if( have_value( $_POST[ $save_id ] ) ) {
            $data = web_io::decodeData( $_POST[ $save_id ], $encode );
			if( WORDY > 5 ) print_r( $data );
        }
        return $data;
    }
    // +-------------------------------------------------------------+
    function saveSession( $data, $save_id='',$encode=WEBIO_ENCODE_TYPE )
    {
        if( WORDY > 4 ) echo "<i>web_io::saveSession( $data, $save_id, $encode )</i>...<br>\n";
        if( !have_value( $save_id ) ) { $save_id = WEBIO_ENCODE_FLAG; }
        
        if( empty( $_SESSION ) ) {
            session_start();
        }
        $_SESSION[ $save_id ] = web_io::encodeData( $data, $encode );
        
        return TRUE;
    }
    // +-------------------------------------------------------------+
    function loadSession( $save_id='',$encode=WEBIO_ENCODE_TYPE )
    {
        if( WORDY > 4 ) echo "<i>web_io::loadSession( $save_id, $encode )</i>...<br>\n";
        if( !have_value( $save_id ) ) { $save_id = WEBIO_ENCODE_FLAG; }
        
        $data = NULL;
        if( empty( $_SESSION ) ) {
            session_start();
        }
        if( !empty( $_SESSION[ $save_id ] ) ) {
            $data = web_io::decodeData( $_SESSION[ $save_id ], $encode );
        }
        return $data;
    }
    // +-------------------------------------------------------------+
    function clearSession( $save_id='' )
    {
        if( isset( $_SESSION[ $save_id ] ) ) {
            unset( $_SESSION[ $save_id ]  );
        }
    }
    // +-------------------------------------------------------------+
    function saveCookie( $data, $save_id='',$save_time='', $encode=WEBIO_ENCODE_TYPE )
    {
        if( WORDY > 4 ) echo "<i>web_io::saveCookie( $data, $save_id, $save_time, $encode )</i>...<br>\n";
        if( !have_value( $save_id ) ) { $save_id = WEBIO_ENCODE_FLAG; }
        
        $cook_value = web_io::encodeData( $data, $encode );
        if( !$save_time ) {
            $success = setcookie( $save_id, $cook_value );
        }
        elseif( !is_numeric( $save_time ) ) {
            // $save_time = 60 * 60 * 24 * 365; // save for a year
            // $save_time = 60 * 60 * 24 * 30; // save for 30 days
            // $save_time = 60 * 60 * 24 * 1; // save for 1 days
               $save_time = 60 * 60 * 24 * 1; // save for 1 days
            $success = setcookie( $save_id, $cook_value, time()+$save_time );
        }
        else {
            $success = setcookie( $save_id, $cook_value, time()+$save_time );
        }
        // the password is junked with md5.
        
        if( WORDY > 4 ) echo "setcookie( $save_id, $cook_value );<br>\n";
        if( $success ) {
            if( WORDY > 4 ) echo " -> saved data to COOKIE[{$save_id}]...<br>\n";
        } else {
            if( WORDY ) echo "<font color=red> -> save to cookie failed! maybe char already sent before header...</font><br>\n";
        }
        return $success;
    }
    // +-------------------------------------------------------------+
    function loadCookie( $save_id='',$encode=WEBIO_ENCODE_TYPE )
    {
        if( WORDY > 4 ) echo "<i>web_io::loadCookie( $save_id, $encode )</i>...<br>\n";
        if( !have_value( $save_id ) ) { $save_id = WEBIO_ENCODE_FLAG; }
        
        $data = NULL;
        if( @have_value( $_COOKIE[ $save_id ] ) ) {
            $data = web_io::decodeData( $_COOKIE[ $save_id ], $encode );
        }
        return $data;
    }
    // +-------------------------------------------------------------+
    function encodeData( $data, $encode=WEBIO_ENCODE_TYPE )
    {
        if( WORDY > 4 ) echo "<i>web_io::encodeData( $data, $encode )</i>...<br>\n";
        // encoding $data; $data can be an array
        // returns a seriarized string data.
        $se_data = serialize( $data );
        
        switch( $encode )
        {
            case WEBIO_ENCODE_BASE64:
                $en_data = base64_encode( $se_data );
                break;
            case WEBIO_ENCODE_CRYPT: // not supported yet
            case WEBIO_ENCODE_NONE:
            default:
                $en_data = $se_data;
                break;
        }
        if( WORDY > 4 ) {
            echo "encoded: "; print_r( $data ); echo "==> {$se_data} ==> {$en_data}<br>\n";
        }
        return $en_data;
    }
    // +-------------------------------------------------------------+
    function decodeData( $data, $encode=WEBIO_ENCODE_TYPE )
    {
        if( WORDY > 4 ) echo "<i>web_io::decodeData( $data, $encode )</i>...<br>\n";
        // decoding $data; $data is a seriarized string data of an PHP variable.
        
        switch( $encode )
        {
            case WEBIO_ENCODE_BASE64:
                $de_data = base64_decode( $data );
                break;
            case WEBIO_ENCODE_CRYPT: // not supported yet
            case WEBIO_ENCODE_NONE:
            default:
                $de_data = $data;
                break;
        }
        $un_data = unserialize( $de_data );
        
        return $un_data;
    }
    // +-------------------------------------------------------------+
}
/* ========================================================================== */


?>