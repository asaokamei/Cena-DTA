<?php
namespace CenaDta\Html;

// +----------------------------------------------------------------------+
class formSelect extends Form
{
    var $style = 'SELECT';
    var $size;
    // +--------------------------------------------------------+
    function makeName( $value ) {
        return $this->makeNameItems( $value );
    }
    // +--------------------------------------------------------+
    function makeHtml( $value )
    {
        if( isset( $this->option ) ) {
            $option = $this->option->getOptions();
        }
        else {
            $option = array();
        }
        return Tags::select(
            $this->name,
            $this->item_data,
            $this->add_head_option,
            $option,
            $value,
            $this->disable_list
        );
    }
}

