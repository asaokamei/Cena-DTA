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
// class.pgg_value.php

if( !defined( "WORDY" ) ) define( "WORDY",  0 ); // very wordy...

// include necessary libraries...
require_once( dirname( __FILE__ ) . "/class.ext_func.php" );
require_once( dirname( __FILE__ ) . "/class.web_io.php" );

define( "PGG_POST",        "post" );
define( "PGG_GET",         "get" );
define( "PGG_REG_GLOBAL",  "variable" );
define( "PGG_SESSION",     "session" );
define( "PGG_COOKIE",      "cookie" ); 
define( "PGG_GLOBAL",      "global" ); 
define( "PGG_DATA",        "data" ); 

define( "PGG_VAR_ORDER", "array( PGG_POST, PGG_GET, PGG_SESSION, PGG_REG_GLOBAL );" );
define( "PGG_ERROR_ACTION_ADD",     "ADD" );
define( "PGG_ENCODE_TYPE", WEBIO_ENCODE_TYPE );
define( "PGG_ENCODE_NAME", "pggValEnCode" );

define( "PGG_REG_NUMBER",  '[0-9]*' );
define( "PGG_REG_FLOAT",   '[.0-9]*' );
define( "PGG_REG_AL_NUM",  '[-_a-zA-Z0-9]*' );

/***
TO-DO:
  - automatic encoding save may not work for cookie.
  - more testing. 

***/

/* ================================================================= */
 class pgg_value
{
    // stores all the variables found via pgg_value
    var $all_variables; 
    var $pggCheck_id;
    
    // error messages etc. 
    var $error_num, $error_msgs, $error_usemsg; 
    var $error_style;
    var $all_errmsgs;           // stores error messages 
    
    // flags
    var $flag_RETURN_FALSE_ON_ERROR; // returns FALSE when an error was found
    var $flag_PUSH_EMPTY_VALUE; // ignore the empty variable or not
    var $flag_VARIABLE_FOUND;   // set where the variable was found 
    
    var $var_order;             // define the order to look for
    // +-------------------------------------------------------------+
    function pgg_value() 
    {
        $this->all_variables      = array();
        $this->error_msgs         = array();
        $this->error_num          = 0;
        $this->error_style        = PGG_ERROR_ACTION_ADD; // add error msg to $all_variables. 
        $this->pggCheck_id        = 'pggCheckID';
        
        // flag_RETURN_FALSE_ON_ERROR:
        //   TRUE : returns FALSE value when an error was found
        //   FALSE: returns the value even if an error was found
        $this->flag_RETURN_FALSE_ON_ERROR = TRUE;
        
        // flag_PUSH_EMPTY_VALUE:
        //   TRUE : a variable with empty value is created
        //   FALSE: the variable is not created if the value is empty
        $this->flag_PUSH_EMPTY_VALUE = FALSE; // do not push empty value. 
        
        // var_order:
        //   order of places to look for...
        $this->var_order   = array( PGG_DATA, PGG_POST, PGG_GET, PGG_SESSION, PGG_COOKIE, PGG_REG_GLOBAL );
		
		$this->post_vars   = array();
		
		$this->filter = array( // filter name => array( function name, active, options )
			'trim'     => array( 'recurse_trim',     TRUE, FALSE ), 
			'del_null' => array( 'recurse_del_null', TRUE, FALSE )
		);
		$this->verify = array(); // verify name => array( active, options )
    }
    // +-------------------------------------------------------------+  
    //         getting values from post/get/session/cookie          //
    // +-------------------------------------------------------------+  
	function & getBinary( $var_name )
	{
		return $this->get( $var_name );
	}
    // +-------------------------------------------------------------+  
	function & getValue( $var_name )
	{
		$val = $this->get( $var_name );
		if( have_value( $val ) )
		if( have_value( $this->filter ) ) 
		foreach( $this->filter as $filter => $options )
		{
			$filter_func = $options[0];
			if( $options[1] ) {
				if( $options[2] === FALSE ) {
					$filter_func( $val );
				}
				else {
					$filter_func( $val, $options[1] );
				}
			}
		}
		return $val;
	}
    // +-------------------------------------------------------------+  
    function & get( $var_name )
    {
        if( WORDY > 3 ) echo "pgg_value::getValue( $var_name )...";
        $val = FALSE;
        if( empty( $this->var_order ) || !is_array( $this->var_order ) ) {
			return $val;
        }
		for( $i = 0; $i < count( $this->var_order ); $i++ ) 
		{
			$found = FALSE;
			switch( $this->var_order[$i] ) 
			{
				case PGG_DATA:
					$found = $this->varInArray( $var_name, $this->post_vars, $val );
					break;
				case PGG_POST:
					$found = $this->varInArray( $var_name, $_POST, $val );
		            if( get_magic_quotes_gpc() ) { $val = recurse_func( $val, 'stripslashes' ); }
					break;
				case PGG_GET:
					$found = $this->varInArray( $var_name, $_GET, $val );
		            if( get_magic_quotes_gpc() ) { $val = recurse_func( $val, 'stripslashes' ); }
					break;
				case PGG_SESSION:
					$found = $this->varInArray( $var_name, $this->session_vars, $val );
					break;
				case PGG_COOKIE:
					$found = $this->varInArray( $var_name, $this->cookie_vars, $val );
					break;
				default:
					break;
			}
		if( $found ) {
			$this->flag_VARIABLE_FOUND = $this->var_order[$i];
			break;
		}
		}
		if( WORDY > 3 ) {
			echo "<font color=green>found = {$found} in {$this->flag_VARIABLE_FOUND }";
			echo ", val = " . fmt::val_type( $val );
			echo ", {$var_name} in {$this->flag_VARIABLE_FOUND}! </font><br>\n";
		}
        return $val;
    }
    // +-------------------------------------------------------------+  
    function varInArray( $var_name, &$data, &$val ) 
	{
		if( WORDY > 4 ) echo "pgg_value::varInArray( $var_name, &$data, &$val )<br>\n";
        $found = FALSE;
		if( isset( $data[ $var_name ] ) ) 
		{
			$found = TRUE;
			if( have_value( $data[ $var_name ] ) ) {
				$val = $data[ $var_name ];
			}
			else {
				$val = NULL; // NULL means variable is set but has no value!
			}
		}
        return $found;
    }
    // +-------------------------------------------------------------+  
    function setVarOrder( $var_order=NULL )
    {
        if( empty( $var_order ) ) {
			return $this->var_order;
		}
		if( !is_array( $var_order ) ) {
			$var_order = array( $var_order );
		}
		$this->var_order = $var_order;
		return $this->var_order;
    }
    /* ---------------------------------------------------------------------- */
    function saveSession( $save_vars=NULL, $save_id=NULL, $encode=PGG_ENCODE_TYPE )
    {
        if( WORDY ) echo "<br><i><b>pgg_value::saveSession( $save_vars, $save_id, $encode )...</b></i><br>\n";
        if( !have_value( $save_id ) ) $save_id = $this->pggCheck_id;
        
        $save_var = $this->prepareArray( $var_name );
        web_io::saveSession( $save_var, $save_id, $encode );
        if( WORDY > 3 ) {
            wordy_table( $save_var, "...saved to session['{$save_id}']" );
            print_r( $_SESSION[ $save_id ] );
			echo "<br>\n";
        }
    }
    /* ---------------------------------------------------------------------- */
    function & loadSession( $save_id=NULL, $encode=PGG_ENCODE_TYPE )
    {
        if( WORDY ) echo "<br><i><b>pgg_value::loadSession( $save_id, $encode )...</b></i><br>\n";
        
        if( !have_value( $save_id ) ) $save_id = $this->pggCheck_id;
        
        if( have_value( $_SESSION[ $save_id ] ) ) {
            $this->session_vars = web_io::loadSession( $save_id, $encode );
        }
        if( WORDY > 3 ) {
            wordy_table( $this->session_vars, "loading var from session['{$save_id}']" );
			echo "<br>\n";
        }
		return $this->session_vars;
    }
    /* ---------------------------------------------------------------------- */
    function & restoreSession( $save_id=NULL, $encode=PGG_ENCODE_TYPE )
    {
        if( WORDY ) echo "<br><i><b>pgg_value::restoreSession( $save_id, $encode )...</b></i><br>\n";
        
        if( !have_value( $save_id ) ) $save_id = $this->pggCheck_id;
        
		$success = FALSE;
        if( have_value( $_SESSION[ $save_id ] ) ) {
			$success = TRUE;
            $this->all_variables = 
				array_merge( $this->all_variables, web_io::loadSession( $save_id, $encode ) );
        }
        if( WORDY > 3 ) {
            wordy_table( $this->all_variables, "restored from session['{$save_id}']" );
			echo "<br>\n";
        }
		return $success;
    }
    /* ---------------------------------------------------------------------- */
    function clearSession( $save_id=NULL, $encode=PGG_ENCODE_TYPE )
    {
        if( WORDY ) echo "<br><i><b>pgg_value::clearSession( $save_id, $encode )...</b></i><br>\n";
        
        if( !have_value( $save_id ) ) $save_id = $this->pggCheck_id;
        if( isset( $_SESSION[ $save_id ] ) ) {
            unset( $_SESSION[ $save_id ] );
        }
        if( WORDY > 3 ) {
            echo "...cleared session:" . $this->pggCheck_id;
        }
    }
    /* ---------------------------------------------------------------------- */
    function delSession( $var_name, $save_id=NULL, $encode=PGG_ENCODE_TYPE )
    {
        if( WORDY ) echo "<br><i><b>pgg_value::delSession( $var_name, $save_id, $encode )...</b></i><br>\n";
        
        if( !have_value( $save_id ) ) { $save_id = $this->pggCheck_id; }
        if( isset( $_SESSION[ $save_id ][ $var_name ] ) ) {
            //unset( $_SESSION[ $save_id ][ $var_name ] );
            $_SESSION[ $save_id ][ $var_name ] = NULL;
        }
        if( WORDY > 3 ) {
            echo "...deleted session var [{$var_name}] from {$save_id}...<br>";
        }
    }
    // +-------------------------------------------------------------+  
    function savePost( $save_vars=NULL, $save_id=NULL, $encode=PGG_ENCODE_TYPE )
    {
        if( WORDY ) echo "<br><i><b>pgg_value::savePost( $save_vars, $save_id, $encode )...</b></i><br>\n";
        if( !have_value( $save_id ) ) $save_id = $this->pggCheck_id;
        
        $save_var    = $this->prepareArray( $save_vars );
		if( WORDY > 3 ) wordy_table( $save_var, 'savePost data' );
        $hidden_tags = web_io::savePost( $save_var, $save_id, $encode );
        
        if( PGG_ENCODE_TYPE ) {
            $hidden_tags .= "<input type='hidden' name='" . PGG_ENCODE_NAME . "' value='{$encode}'>\n";
        }
        return $hidden_tags;
    }
    /* ---------------------------------------------------------------------- */
    function & loadPost( $save_id=NULL, $encode=PGG_ENCODE_TYPE )
    {
        if( !have_value( $save_id ) ) $save_id = $this->pggCheck_id;
        if( WORDY ) echo "<br><i><b>pgg_value::loadPost( $save_id ) loading from post...</b></i><br>\n";
        
        if( have_value( $_POST[ PGG_ENCODE_NAME ] ) ) {
            $encode = $_POST[ PGG_ENCODE_NAME ];
        }
        if( have_value( $_POST[ $save_id ] ) ) {
            $this->post_vars = array_merge( 
                $this->post_vars, web_io::loadPost( $save_id, $encode ) );
        }
        if( WORDY > 3 ) {
            wordy_table( $this->post_vars, "loading var from post: " );
        }
		return $this->post_vars;
    }
    /* ---------------------------------------------------------------------- */
    function & restorePost( $save_id=NULL, $encode=PGG_ENCODE_TYPE )
    {
        if( !have_value( $save_id ) ) $save_id = $this->pggCheck_id;
        if( WORDY ) echo "<br><i><b>pgg_value::restorePost( $save_id ) loading from post...</b></i><br>\n";
        
        if( have_value( $_POST[ PGG_ENCODE_NAME ] ) ) {
            $encode = $_POST[ PGG_ENCODE_NAME ];
        }
		$success = FALSE;
        if( have_value( $_POST[ $save_id ] ) ) {
			$success = TRUE;
            $this->all_variables = 
				array_merge( $this->all_variables, web_io::loadPost( $save_id, $encode ) );
        }
        if( WORDY > 3 ) {
            wordy_table( $this->all_variables, "restored var from post: " );
        }
		return $success;
    }
    // +-------------------------------------------------------------+  
    function saveCookie( $save_vars=NULL, $save_id=NULL, $encode=PGG_ENCODE_TYPE )
    {
        if( WORDY ) echo "<br><i><b>pgg_value::saveCookie() saving to cookie...</b></i><br>\n";
        if( !have_value( $save_id ) ) $save_id = $this->pggCheck_id;
        
        $save_var    = $this->prepareArray( $var_name );
        $success = web_io::saveCookie( $save_var, $save_id, NULL, $encode );
        
        if( PGG_ENCODE_TYPE ) {
            setcookie( PGG_ENCODE_NAME, $encode );
        }
        return $success;
    }
    /* ---------------------------------------------------------------------- */
    function loadCookie( $save_id=NULL, $encode=PGG_ENCODE_TYPE )
    {
        if( WORDY ) echo "<br><i><b>pgg_value::loadCookie( $save_id, $encode)...</b></i><br>\n";
        
        if( !have_value( $save_id ) ) $save_id = $this->pggCheck_id;
        if( have_value( $_COOKIE[ PGG_ENCODE_NAME ] ) ) {
            $encode = $_COOKIE[ PGG_ENCODE_NAME ];
        }
        if( have_value( $_COOKIE[ $save_id ] ) ) {
            $this->cookie_vars = array_merge( 
                $this->cookie_vars, web_io::loadCookie( $save_id, $encode ) );
        }
        if( WORDY > 3 ) {
            echo "loading var from cookie: ";
            print_r( $this->cookie_vars );
            echo "<br>\n";
        }
    }
    // +-------------------------------------------------------------+
    //                  pop related functions                       //
    // +-------------------------------------------------------------+
    function prepareArray( $var_name )
    {
        if( WORDY > 3 ) echo "pgg_value::prepareArray( $var_name ) <br>\n";
        $ret_vars = array();
        
        if( is_array( $var_name ) AND !empty( $var_name ) ) {
            $ret_array = array();
            foreach( $var_name as $var ) {
                if( WORDY > 4 ) echo "--> got $var: " . $this->all_variables[ $var] . "<br>\n";
                $ret_vars[ $var ] = $this->all_variables[ $var ];
            }
        }
        elseif( have_value( $var_name ) ) {
            if( WORDY > 4 ) echo "--> got " . $this->all_variables[ $var_name ] . "<br>\n";
            $ret_vars = $this->all_variables[ $var_name ];
        }
        else {
            if( WORDY > 4 ) { 
                echo "--> got all of varialbes<br>\n"; 
                print_r( $this->all_variables ); 
                echo '<br>';
            }
            $ret_vars = $this->all_variables;
        }
        return $ret_vars;
    }
    // +-------------------------------------------------------------+
    function popVariables( $var_name=NULL ) 
    {
        if( WORDY ) echo "<br><i><strong>pgg_value::popVariables( $var_name ) </strong></i><br>\n";
        $ret_vars = $this->prepareArray( $var_name );
        
        return $ret_vars; 
    }
    // +-------------------------------------------------------------+
    function popHtml( $var_name=NULL ) 
    {
        if( WORDY ) echo "<br><i><strong>pgg_value::popHtml( $var_name )</strong></i><br>\n";
        $ret_vars = $this->prepareArray( $var_name );
        $ret_vars = recurse_htmlspecialchars( $ret_vars );
        
        // add error messages if PGG_ERROR_ACTION_ADD.
        if( is_array( $ret_vars ) ) 
        {
            if( WORDY > 4 ) echo "processing ret_vars... <br>\n";
            reset( $ret_vars );
            while( list( $key, $val ) = each( $ret_vars ) ) 
            {
                if( have_value( $this->all_errmsgs[ $key ] ) ) {
                    $msg = $this->all_errmsgs[ $key ];
                    if( have_value( $msg ) ) {
                        if( $this->error_style == PGG_ERROR_ACTION_ADD )
						if( !is_array( $val ) ) {
                            $ret_vars[ $key ] .= $msg;
                        }
                        else {
							foreach( $val as $k => $v ) {
                            	$ret_vars[ $key ][ $k ] .= $msg;
							}
                        }
                    }
                }
                if( WORDY > 4 ) { echo "-> {$key}=>{$val} + [{$msg}]<br>\n"; }
            }
        }
        else
        {
            if( have_value( $this->all_errmsgs[ $var_name ] ) ) {
                $msg = $this->all_errmsgs[ $var_name ];
                if( have_value( $msg ) ) {
                    if( $this->error_style == PGG_ERROR_ACTION_ADD && !is_array( $ret_vars ) ) {
                        $ret_vars .= $msg;
                    }
                    else {
                        $ret_vars = $msg;
                    }
                }
            }
        }
        if( WORDY > 3 ) { wordy_table( $ret_vars ); echo "<br>\n"; }
        
        return $ret_vars;
    }
    // +-------------------------------------------------------------+
    function popHtmlSafe( $var_name=NULL ) 
    {
        if( WORDY ) echo "<br><i><strong>pgg_value::popHtmlSafe( $var_name )</strong></i><br>\n";
        $ret_vars = $this->prepareArray( $var_name );
        $ret_vars = recurse_htmlspecialchars( $ret_vars );
        
        if( WORDY > 3 ) { wordy_table( $ret_vars ); echo "<br>\n"; }
        
        return $ret_vars;
    }
    // +-------------------------------------------------------------+
    function popSqlSafe( $var_name=NULL ) 
    {
        if( WORDY ) echo "<br><i><strong>pgg_value::popSqlSafe( $var_name )</strong></i><br>\n";
        $ret_vars = $this->prepareArray( $var_name );
        $ret_vars = recurse_func( $ret_vars, 'addslashes_safe' );
        
        if( WORDY > 3 ) { wordy_table( $ret_vars ); echo "<br>\n"; }
        return $ret_vars;
    }
    // +-------------------------------------------------------------+
    function popTextArea( $var_name=NULL ) 
    {
        if( WORDY ) echo "<br><i><strong>pgg_value::popTextArea( $var_name )</strong></i><br>\n";
        $ret_vars = $this->prepareArray( $var_name );
        $ret_vars = recurse_func( $ret_vars, 'nl2br' );
        
        if( WORDY > 3 ) { wordy_table( $ret_vars ); echo "<br>\n"; }
        return $ret_vars;
    }
    // +-------------------------------------------------------------+
    function getHtmlHidden( $var_name ) 
    {
		if( have_value( $this->all_errmsgs[ $var_name ] ) ) {
			$ret_val = $this->all_errmsgs[ $var_name ];
		}
		else {
			$value   = $this->all_variables[ $var_name ];
			$ret_val = str_repeat( '*', strlen( $value ) );
		}
		return $ret_val;
    }
    // +-------------------------------------------------------------+  
    // push and error functions
    // +-------------------------------------------------------------+
    function & pushValue( $var_name, $val, $default_val=FALSE )
    {
        if( WORDY ) echo "pushValue( '$var_name', '$val', '$default_val' )...<br>\n";
		if( have_value( $val ) ) {
			if( WORDY > 3 ) echo "--> val has some value: '{$val}'<br>\n";
            $this->all_variables[ $var_name ] = $val;
		}
		else
		if( $default_val !== FALSE ) {
			if( WORDY > 3 ) echo "--> val has no value, set to a default value (" . fmt::val_type( $default_val ) .").<br>\n";
            $this->all_variables[ $var_name ] = $default_val;
		}
		else {
			if( WORDY > 3 ) echo "--> val is an empty value<br>\n";
			if( $this->flag_PUSH_EMPTY_VALUE ) {
				if( WORDY > 3 ) echo "--> pushed NULL value<br>\n";
    	        $this->all_variables[ $var_name ] = NULL;
			}
		}
        return $val;
    }
    // +-------------------------------------------------------------+
    function deleteVar( $var_name )
    {
		if( isset( $this->all_variables[ $var_name ] ) ) {
			unset( $this->all_variables[ $var_name ] );
		}
    }
    // +-------------------------------------------------------------+
    function errUseMsg( $err_msg, $err_style=NULL )
    {
        if( WORDY > 3 ) echo " -> starting errUseMsg( $err_msg )<br>\n";
        $this->error_usemsg = $err_msg;
        if( isset( $err_style ) && $err_style == PGG_ERROR_ACTION_ADD ) 
            $this->error_style = PGG_ERROR_ACTION_ADD;
        return TRUE;
    }
    // +-------------------------------------------------------------+
    function errAddErr( $err_msg, $var_name=NULL )
    {
        if( WORDY ) echo " -> starting errAddErr( $err_msg, $var_name )<br>\n";
        if( isset( $var_name ) )
        $this->error_msgs[] = $err_msg;
        $this->error_num++;
        if( $var_name ) {
            $this->all_errmsgs[ "$var_name" ] = $err_msg;
            if( !isset( $this->all_variables[ "$var_name" ] ) ) 
                $this->all_variables[ "$var_name" ] = NULL;
        }
        return FALSE;
    }
    // +-------------------------------------------------------------+
    function errGetErrs() { 
        return $this->error_msgs; 
    }
    // +-------------------------------------------------------------+
    function errGetNum() { 
		if( WORDY ) echo "pgg_value::errGetNum()=>{$this->error_num}<br>";
        return $this->error_num; 
    }
    // +-------------------------------------------------------------+
    function errGetMsgs() { 
        return $this->all_errmsgs; 
    }
    // +-------------------------------------------------------------+
    function _handle_err( $var_name, $val, $err_type, $err_msg=NULL )
    {
        return $this->pushError( $var_name, $val, $err_type, $err_msg );
    }
    // +-------------------------------------------------------------+
    function popErrors() 
	{
        return $this->all_errmsgs; 
    }
    // +-------------------------------------------------------------+
    function errPopMsgs() 
	{
        return $this->all_errmsgs; 
    }
    // +-------------------------------------------------------------+
    function & pushError( $var_name, $val, $err_type, $err_msg=NULL )
    {
        if( WORDY ) echo "<b><font color=red>pgg_value::pushError( '$var_name', '$val', $err_type, $err_msg )</font></b><br>\n";
        if( have_value( $err_msg ) ) {
            $msg = $err_msg;
        }
        elseif( $this->error_usemsg ) {
            $msg = $this->error_usemsg;
        }
        elseif( $this->err_msg[ $err_type ] ) {
            $msg = $this->err_msg[ $err_type ];
        }
        else {
            $msg = $err_type;
        }
        if( WORDY ) echo "err_msg: {$msg}, flag_RETURN_FALSE_ON_ERROR: {$this->flag_RETURN_FALSE_ON_ERROR}<br>\n";
        
        $this->error_msgs[] = $msg;
        $this->error_num++;
        $this->all_variables[ $var_name ] = $val;
        $this->all_errmsgs[   $var_name ] = $msg;
        
        if( $this->flag_RETURN_FALSE_ON_ERROR ) {
			$val = FALSE;
        }
        return $val;
    }
    // +-------------------------------------------------------------+
	function setPostVars( $vars )
	{
		if( WORDY ) echo "<b>setPostVars</b>... use with care!!!<br>\n";
		if( WORDY > 3 ) wordy_table( $vars, 'vars' );
		$this->post_vars = array_merge( $this->post_vars, $vars );
		
		return TRUE;
    }
    // +-------------------------------------------------------------+
	function useSessionVars()
	{
		if( WORDY ) echo "<b>useSessionVars</b>... use with care!!!<br>\n";
		if( WORDY > 3 ) wordy_table( $this->session_vars, 'this->session_vars' );
		$this->all_variables = array_merge( $this->all_variables, $this->session_vars );
    }
    // +-------------------------------------------------------------+
	function usePostVars()
	{
		if( WORDY ) echo "<b>usePostVars</b>... use with care!!!<br>\n";
		if( WORDY > 3 ) wordy_table( $this->post_vars, 'this->post_vars' );
		$this->all_variables = array_merge( $this->all_variables, $this->post_vars );
    }
    // +-------------------------------------------------------------+
	function setAllVars( $vars )
	{
		if( WORDY ) echo "<b>setAllVars</b>... use with care!!!<br>\n";
		if( WORDY > 3 ) wordy_table( $vars, 'vars' );
		$this->all_variables = array_merge( $this->all_variables, $vars );
		
		return TRUE;
    }
    // +-------------------------------------------------------------+
	function clearToken( $token_id=NULL )
	{
		if( WORDY ) echo "<b>setAllVars</b>... use with care!!!<br>\n";
		if( !$token_id  ) { $token_id  = PGG_TOKEN_NAME; }
		
		if( isset( $_SESSION[ $token_id ] ) ) {
			unset( $_SESSION[ $token_id ] );
		}
		if( WORDY ) echo "<br><b>clearToken( $token_id ) </b>=> sess={$token_sess_val}, post={$token_post_val}, check={$check_token}<br>\n";
		return $check_token;
    }
    // +-------------------------------------------------------------+
	function checkToken( $token_id=NULL, $clear_token=TRUE )
	{
		if( !$token_id  ) { $token_id  = PGG_TOKEN_NAME; }
		$token_sess_val = $_SESSION[ PGG_TOKEN_NAME ];
		$token_post_val = $this->getValue( $token_id );
		if( !$token_sess_val ) {
			$check_token = FALSE;
			$this->pushError( $token_id, NULL, PGG_ERRNUM_TOKEN_NOTMATCH );
		}
		else if( $token_sess_val == $token_post_val ) {
			$check_token = TRUE;
		}
		else {
			$check_token = FALSE;
			$this->pushError( $token_id, NULL, PGG_ERRNUM_TOKEN_NOTMATCH );
		}
		if( $clear_token ) {
			unset( $_SESSION[ $token_id ] );
		}
		if( WORDY ) echo "<br><b>checkToken( $token_id ) </b>=> sess={$token_sess_val}, post={$token_post_val}, check={$check_token}<br>\n";
		return $check_token;
    }
    // +-------------------------------------------------------------+  
	function pushToken( $token_id=NULL, $token_val=NULL )
	{
		if( WORDY ) echo "<br><b>pushToken( $token_id, $token_val ) </b>";
		if( !$token_id  ) { $token_id  = PGG_TOKEN_NAME; }
		if( !$token_val ) { $token_val = md5( rand() ); }
		$_SESSION[ $token_id ] = $token_val;
		$this->pushValue( $token_id, $token_val );
		
		if( WORDY ) echo "pushToken: => {$token_id}={$token_val}<br>\n";
	}
    // +-------------------------------------------------------------+
	function popPasswd( $var_name )
	{
		$val = $this->all_variables[ $var_name ];
		$err = $this->all_errmsgs  [ $var_name ];
		
		if( $err ) {
			$passwd = $err;
		}
		else {
			$passwd = str_repeat( '*', strlen( $val ) );
		}
		return $passwd;
	}
    // +-------------------------------------------------------------+
}
/* ================================================================= */


/* obsolete methods

    // +-------------------------------------------------------------+
    function & xxxvarInData( $var_name, &$val ) {
		$this_wordy = 3;
		if( WORDY > $this_wordy ) echo "pgg_value::varInData( '$var_name', '$val' )<br>\n";
        $found = FALSE;
		if( !isset( $this->post_vars[ $var_name ] ) ) {
            if( WORDY > $this_wordy ) echo "var_name: $var_name not defined in data<br>\n";
		}
        else
		if( have_value( $this->post_vars[ $var_name ] ) ) {
            $val = $this->post_vars[ $var_name ];
            $this->flag_VARIABLE_FOUND = PGG_DATA;
            $found = TRUE;
            if( WORDY > $this_wordy ) echo "var_name: $var_name found in data: '$val'...<br>\n";
        }
		else {
            if( WORDY > $this_wordy ) echo "var_name: $var_name empty value found in data<br>\n";
            $found = TRUE;
			$val = ''; // not NULL but empty string!
		}
        return $found;
    }
    // +-------------------------------------------------------------+
    function & xxxvarInPost( $var_name, &$val ) {
		$this_wordy = 3;
		if( WORDY > $this_wordy ) echo "pgg_value::varInPost( '$var_name', '$val' )<br>\n";
        $found = FALSE;
		if( !isset( $_POST[ $var_name ] ) ) {
			//$val = NULL;
            if( WORDY > $this_wordy ) echo "var_name: $var_name not defined in post<br>\n";
		}
        elseif( have_value( $_POST[ $var_name ] ) ) {
            $val = $_POST[ $var_name ];
            $this->flag_VARIABLE_FOUND = PGG_POST;
            $found = TRUE;
            if( get_magic_quotes_gpc() ) { $val = recurse_func( $val, 'stripslashes' ); }
            if( WORDY > $this_wordy ) echo "found $var_name in _POST '$val'...<br>\n";
        }
		else {
            if( WORDY > $this_wordy ) echo "var_name: $var_name empty value found in post<br>\n";
            $found = TRUE;
			$val = ''; // not NULL but empty string!
		}
        return $found;
    }
    // +-------------------------------------------------------------+  
    function & xxxvarInGet( $var_name, &$val ) {
		$this_WORDY = 4;
        $found = FALSE;
		if( !isset( $_GET[ $var_name ] ) ) {
			//$val = NULL;
            if( WORDY > 3 ) echo "var_name: $var_name not defined in get<br>\n";
		}
        elseif( have_value( $_GET[ $var_name ] ) ) {
            $val = $_GET[ $var_name ];
            if( WORDY > 3 ) echo "found $var_name in GET...<br>\n";
            $this->flag_VARIABLE_FOUND = PGG_GET;
            $found = TRUE;
            if( get_magic_quotes_gpc() ) { $val = recurse_func( $val, 'stripslashes' ); }
        }
		else {
			$val = ''; // not NULL but empty string!
		}
        return $found;
    }
    // +-------------------------------------------------------------+  
    function & xxxvarInRegGlobal( $var_name, &$val ) {
        $found = FALSE;
        global $$var_name;
        if( have_value( $$var_name ) ) 
        {
            $val = $$var_name;
            if( WORDY > 3 ) echo "found $var_name in Global Variable...<br>\n";
            $this->flag_VARIABLE_FOUND = PGG_GLOBAL;
            $found = TRUE;
            if( get_magic_quotes_gpc() ) { $val = recurse_func( $val, 'stripslashes' ); }
        }
        return $found;
    }
    // +-------------------------------------------------------------+  
    function & xxxvarInSession( $var_name, &$val ) {
        $found = FALSE;
		if( have_value( $this->session_vars[ $var_name ] ) ) {
            $val = $this->session_vars[ "$var_name" ];
            if( WORDY > 3 ) echo "found $var_name in SESSION...$val<br>\n";
            $this->flag_VARIABLE_FOUND = PGG_SESSION;
            $found = TRUE;
        }
        return $found;
    }
    // +-------------------------------------------------------------+  
    function & xxxvarInCookie( $var_name, &$val ) {
        $found = FALSE;
        if( have_value( $this->cookie_vars[ $var_name ] ) ) {
            $val = $this->cookie_vars[ $var_name ];
            if( WORDY > 3 ) echo "found $var_name in COOKIE...$val<br>\n";
            $this->flag_VARIABLE_FOUND = PGG_COOKIE;
            $found = TRUE;
        }
        return $found;
    }


*/

?>