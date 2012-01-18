<?php
/*** svCart.php, version 2.0
 **  shopping Cart using session variable
 **  version 2.0:
 **    - stores extra information with product ID.
 **    - 文字化け対策：雀の往来
***/
require_once( dirname( __FILE__ ) . '/class.web_io.php' );

if( !defined( "WORDY" ) ) { define( "WORDY", 0 ); }
define( "SVCART_SAVE_SESSION", "session" );
define( "SVCART_SAVE_COOKIE",  "cookie" );
define( "SVCART_SAVE_METHOD",  SVCART_SAVE_SESSION );

/* ------------------------------------------------------------------------------- */
class svCart 
{
    // $cart[$i]["pid"]   : product id
    // $cart[$i]["num"]   : number of items to purchase
    // $cart[$i]["info"]  : extra information about product
    // $cart[$i]["price"] : extra information about price
    // $num_prod : number of product 
    var $cart;
    var $num_prod; 
    var $max_prod; 
    var $cart_id;
    /* ---------------------------------------------------------------------- */
    function svCart( $cart_id="" ) 
    {
        if( WORDY ) echo "<b>created svCart( $cart_id ) instance!</b><br>\n";
        
        if( "$cart_id" == "" ) {
            $this->cart_id = SV_CART_SAVE_ID;
        }
        else {
            $this->cart_id = $cart_id;
        }
        $this->_load_data();
		
		if( !defined( "SVCART_USE_PRICE" ) ) { // default: do not use price 
			define( "SVCART_USE_PRICE",    FALSE );
		}
    }
    /* ---------------------------------------------------------------------- */
    function act( $args ) 
    {
        if( WORDY ) echo "<br><br><b>svCart::action( $arg )</b><br>\n";
        if( WORDY > 5 ) { echo 'args are:<br><pre>'; print_r( $args ); echo '</pre>'; }
        $flag_load = FALSE; // reload needed for saving cookie...
        
        if( !isset( $args[ "act" ] ) ) {
            return FALSE;
        } 
        else {
            $action    = strtolower( $args[ "act" ] );
            $num       = $args[ "num" ];
			$num       = recurse_mb_convert_number( $num );
            $id        = $args[ "id"  ];
            $info      = $args[ "info"  ];
			if( SVCART_USE_PRICE ) {
				$price = $args[ "price"  ];
				$price = recurse_mb_convert_number( $price );
			}
        }
        if( WORDY > 3 ) { echo "action={$action}, id={$id}, num={$num}, info={$info}<br>\n"; }
        
        switch( $action )
        {
            case "set":
                $id = trim( strtoupper( $id ) );
                if( !$num || !is_numeric( $num ) ) $num = 1;
                $this->del_all();
                $this->add( $id, $num, $info, $price );
                $this->_save_data();
                $flag_load = TRUE;
                break;
			
            case "add":
                $id = trim( strtoupper( $id ) );
                if( !$num || !is_numeric( $num ) ) $num = 1;
                $this->add( $id, $num, $info, $price );
                $this->_save_data();
                $flag_load = TRUE;
                break;
            
			case "del":
                if( WORDY ) echo "act={$action} for id={$id}<br>\n";
        
                $id = trim( strtoupper( $id ) );
                $this->del( $id );
                $this->_save_data();
                $flag_load = TRUE;
                break;
            
			case "clear":
                $this->del_all();
                $this->_save_data();
                $flag_load = TRUE;
                break;
            
			case "mod":
                // 在庫以上の注文はキャンセルする。
                $array_del = array();
                for( $i = 0; $i < count( $id ); $i++ )
                {
                    if( isset( $id[$i] ) ) {
                        $id[$i] = trim( $id[$i] );
                        if( empty( $num[$i] ) || $num[$i] == 0 ) { // 注文数０の場合、削除
                            $array_del[$i] = "DEL";
                        }
                        else {
                            $array_del[$i] = "";
                        }
                        $array_del[$i];
                        if( WORDY > 3 ) {
                            echo "#{$i}: id={$id{$i}} num={$num{$i}} del={$array_del{$i}}<br>";
                        }
                    }
                }
                $this->mod_all( $id, $num, $array_del, $info, $price ); // $id & $num are arrays!
                $this->_save_data();
                $flag_load = TRUE;
                break;
            
			default:
                break;
        }
        if( SVCART_SAVE_METHOD == SVCART_SAVE_SESSION ) {
            // need to reload only when saving to cookie.
            $flag_load = FALSE;
        }
        if( WORDY > 3 ) wordy_table( $this->cart , 'items in cart' );
		
        return $flag_load;
    }
    /* ---------------------------------------------------------------------- */
    function add( $pid, $num=1, $info=NULL, $price=NULL ) 
    {
        if( WORDY > 3 ) echo "add( $pid, $num )<br>\n";
        $found = FALSE;
        if( $num == 0 ) return $found;
        if( !$pid    ) return $found;
        for( $i = 0; $i < $this->num_prod; $i++ )
        {
            if( $this->cart[$i]["pid"] == $pid ) {
                $this->cart[$i]["num"]  = $num;
				if( strlen( $info ) ) $this->cart[$i]["info"] = $info;
				if( SVCART_USE_PRICE ) $this->cart[$i]["price"] = $price;
                $found = TRUE;
            }
        }
        if( !$found ) {
            $this->cart[$i]["pid"]  = $pid;
            $this->cart[$i]["num"]  = $num;
            $this->cart[$i]["info"] = $info;
			if( SVCART_USE_PRICE ) $this->cart[$i]["price"] = $price;
            $found = TRUE;
        }
        $this->num_prod = count( $this->cart );
		return $found;
    }
    /* ---------------------------------------------------------------------- */
    function del( $pid )
    {
        if( WORDY ) echo "del( $pid )<br>\n";
        $found = FALSE;
        if( !$pid    ) return $found;
        for( $i = 0; $i < $this->num_prod; $i++ )
        {
            if( $this->cart[$i]["pid"] == $pid ) {
                unset( $this->cart[$i] );
                $found = TRUE;
            }
        }
        $this->_pack_cart();
        return $found;
    }
    /* ---------------------------------------------------------------------- */
    function del_info( $info )
    {
        if( WORDY ) echo "del_info( $info )<br>\n";
        $found = FALSE;
        for( $i = 0; $i < $this->num_prod; $i++ )
        {
            if( $this->cart[$i]["info"] == $info ) {
                unset( $this->cart[$i] );
                $found = TRUE;
            }
        }
        $this->_pack_cart();
        return $found;
    }
    /* ---------------------------------------------------------------------- */
    function mod( $pid, $num=1, $info=NULL, $price=NULL )
    {
        if( WORDY ) echo "mod( $pid, $num )<br>\n";
        $found = FALSE;
        if( $num < 0 ) return $found;
        if( !$pid    ) return $found;
        for( $i = 0; $i < $this->num_prod; $i++ )
        {
            if( $this->cart[$i]["pid"] == $pid ) {
                $this->cart[$i]["num"] = $num;
				if( strlen( $info ) ) $this->cart[$i]["info"] = $info;
				if( SVCART_USE_PRICE ) $this->cart[$i]["price"] = $price;
                $found = TRUE;
            }
        }
        $this->num_prod = count( $this->cart );
        return $found;
    }
    /* ---------------------------------------------------------------------- */
    function mod_all( $pid, $num, $del, $info=array(), $price=array() )
    {
        if( WORDY ) echo "mod_all( $pid, $num, $del )<br>\n";
        while( list( $i, $prod_id ) = each( $pid ) )
        {
            if( !empty( $pid[$i] ) ) 
            {
                if( WORDY > 2 ) echo " > $i : $prod_id = {$num{$i}} / {$del{$i}}<br>\n";
                if( !empty( $del[$i] ) && $del[$i] == "DEL" ) { 
                    $this->del( $prod_id ); 
                }
                elseif( !empty( $num[$i] ) && is_numeric( $num[$i] ) ) {
                    $this->add( $prod_id, $num[$i], $info[$i], $price[$i] ); 
                }
            }
        }
        $this->_pack_cart();
        $this->num_prod = count( $this->cart );
        
        return TRUE;
    }
    /* ---------------------------------------------------------------------- */
    function del_all()
    {
        if( WORDY ) echo "del_all()<br>\n";
        $this->cart = array();
        $this->num_prod = 0;
        //$this->_save_data();
        return TRUE;
    }
    /* ---------------------------------------------------------------------- */
    function get_list()
    {
        if( WORDY ) echo "get_list()<br>\n";
        if( WORDY > 6 ) {
            wordy_table( $this->cart );
        }
        return $this->cart;
    }
    /* ---------------------------------------------------------------------- */
    function _save_data()
    {
        if( WORDY ) echo "_save_data() saving to " . SVCART_SAVE_METHOD . "...<br>\n";
        if( SVCART_SAVE_METHOD == SVCART_SAVE_SESSION ) {
            web_io::saveSession( $this->cart, $this->cart_id );
        }
        elseif( SVCART_SAVE_METHOD == SVCART_SAVE_COOKIE ) {
			web_io::saveCookie( $this->cart, $this->cart_id, time()+36000 );
        }
    }
    /* ---------------------------------------------------------------------- */
    function _load_data()
    {
        if( WORDY ) echo "_load_data() loading from " . SVCART_SAVE_METHOD . "...<br>\n";
        $this->cart = array();
        $this->num_prod = 0;
        if( SVCART_SAVE_METHOD == SVCART_SAVE_SESSION ) {
			$this->cart = web_io::loadSession( $this->cart_id );
        }
        elseif( SVCART_SAVE_METHOD == SVCART_SAVE_COOKIE ) {
			$this->cart = web_io::loadCookie( $this->cart_id );
        }
		$this->num_prod = count( $this->cart );
		if( WORDY > 3 ) {
			echo " -> found svCart and {$this->num_prod} items in " . SVCART_SAVE_METHOD . "[{$this->cart_id}]...<br>\n";
			echo " -> " . $_SESSION[ $this->cart_id ] . "<br>\n";
			wordy_table( $this->cart );
		}
    }
    /* ---------------------------------------------------------------------- */
    function _pack_cart()
    {
        $new_pid  = 0;
        $new_cart = array();
        for( $i = 0; $i < $this->num_prod; $i++ )
        {
            if( $this->cart[$i]["pid"] && $this->cart[$i]["num"] != 0 ) {
                $new_cart[$new_pid] = $this->cart[$i] ;
                $new_pid++;
            }
        }
        $this->cart     = $new_cart;
        $this->num_prod = $new_pid;
    }
    /* ---------------------------------------------------------------------- */
    function get_html_list()
    {
    }
}


?>