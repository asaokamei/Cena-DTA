<?php

// +----------------------------------------------------------------------+
class formCheck extends Form
{
    var $style      = 'CHECK';
    var $item_sep   = '&nbsp;';
    var $item_chop  = 0;
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
        return Tags::listCheck(
            $this->name,
            $this->item_data,
            $this->item_sep,
            $this->item_chop,
            $option,
            $this->add_header,
            $value,
            $this->disable_list
        );
    }
}

