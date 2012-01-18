<?php
namespace CenaDta\Html;

// +----------------------------------------------------------------------+
class formText extends Form
{
    var $style = 'TEXT';
    var $size, $max;
    /* -------------------------------------------------------- */
    function __construct( $name )
    {
        if( WORDY > 3 ) echo "htmlText( $name )";
        $setup = array(
            0 => array( 'name' ),
            1 => array( 'size',       40   ),
            2 => array( 'maxlength',  NULL ),
            3 => array( 'ime',       'ON'  ),
        );
        $args = _util_arg( func_get_args(), $setup );
        $this->name   = $args[ 'name' ];
        unset( $args[ 'name' ] );
        $this->option = new Prop( $args );
    }
    // +--------------------------------------------------------+
    function makeHtml( $value ) {
        return Tags::inputText( $this->name, $value, $this->option->getOptions() );
    }
}

