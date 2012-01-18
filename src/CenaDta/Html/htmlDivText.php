<?php
namespace CenaDta\Html;

// +----------------------------------------------------------------------+
class htmlDivText
{
    var $divider;
    var $num_div;
    var $d_forms = array();
    var $default_items   = FALSE;
    var $implode_with_div = TRUE;
    // +--------------------------------------------------------+
    function __construct( $name, $opt1=NULL, $opt2=NULL, $ime='ON', $option=NULL )
    {
        // example constructor.
    }
    // +--------------------------------------------------------+
    function popHtml( $type="NAME", $values=NULL, $err_msgs=NULL )
    {
        if( is_array( $values ) ) {
            if( isset( $values[ $this->name ] ) ) {
                $value = $values[ $this->name ];
            }
            else {
                $value = NULL;
            }
        }
        else {
            $value = $values;
        }
        if( is_array( $err_msgs ) ) {
            if( isset( $err_msgs[ $this->name ] ) ) {
                $err_msg = $err_msgs[ $this->name ];
            }
            else {
                $err_msg = NULL;
            }
        }
        else {
            $err_msg = $err_msgs;
        }
        return $this->show( $type, $value ) . $err_msg;
    }
    // +--------------------------------------------------------+
    function show( $style="NAME", $value=NULL )
    {
        if( WORDY > 3 ) echo "htmlDivText::show( $style, $value ) w/ {$this->divider} x {$this->num_div}<br>\n";

        if( in_array( $style, array( 'NEW', 'EDIT' ) ) )
        {
            $vals = array();
            if( $style == 'NEW' && !\CenaDta\Util\Util::isValue( $value ) ) {
                $value = $this->default_items;
            }
            if( $value ) {
                $vals = $this->splitValue( $value );
            }
            $forms = array();
            for( $i = 0; $i < $this->num_div; $i ++ ) {
                if( isset( $vals[$i] ) ) {
                    $html = $this->d_forms[$i]->show( $style, $vals[$i] );
                }
                else {
                    $html = $this->d_forms[$i]->show( $style, NULL );
                }
                if( \CenaDta\Util\Util::isValue( $html ) ) $forms[] = $html;
            }
            if( $this->implode_with_div ) {
                $ret_html = implode( $this->divider, $forms );
            }
            else {
                $ret_html = implode( '', $forms );
            }
        }
        else
        {
            $ret_html = $this->makeName( $value );
        }
        return $ret_html;
    }
    // +--------------------------------------------------------+
    function splitValue( $value )
    {
        // split value into each forms.
        // overload this method if necessary.
        return explode( $this->divider, $value );
    }
    // +--------------------------------------------------------+
    function makeName( $value )
    {
        // display input value (for style=NAME/DISP).
        // overload this method if necessary.
        return $value;
    }
    // +--------------------------------------------------------+
}

