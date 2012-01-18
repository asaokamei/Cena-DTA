<?php
namespace CenaDta\Html;

// +----------------------------------------------------------------------+
class formHidden extends Form
{
    var $style = 'HIDDEN';
    var $size, $max;
    /* -------------------------------------------------------- */
    function __construct( $name )
    {
        if( WORDY > 3 ) echo "formHidden( $name, $option )";
        $setup = array(
            0 => array( 'name' ),
        );
        $args = Util::arg( func_get_args(), $setup );
        $this->name   = $args[ 'name' ];
    }
    // +--------------------------------------------------------+
    function makeHtml( $value ) {
        return Tags::inputType( 'hidden', $this->name, $value, $this->option->getOptions() );
    }
}

