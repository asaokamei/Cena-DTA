<?php
namespace CenaDta\Html;

// +----------------------------------------------------------------------+
class formTextArea extends Form
{
    var $style = 'TEXTAREA';
    var $cols, $rows;
    /* -------------------------------------------------------- */
    function __construct( $name=NULL, $width=40, $height=5, $ime='ON', $option=NULL )
    {
        if( WORDY > 3 ) echo "htmlTextArea( $name, $width, $height, $ime, $option )";
        $this->name   = $name;
        $this->option = new Prop( array(
            'cols'  => $width,
            'rows'  => $height,
        ) );
        $this->option->setIme( $ime );
        $this->make_name_funcs[] = 'nl2br';
    }
    // +--------------------------------------------------------+
    function makeHtml( $value ) {
        $option = $this->option->getOptions();
        return Tags::textArea( $this->name, $value, $option );
    }
}

