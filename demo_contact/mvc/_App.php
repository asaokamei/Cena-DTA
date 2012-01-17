<?php
error_reporting( E_ALL ^ E_NOTICE );
//define( 'WORDY', 2 );
require_once( dirname( __FILE__ ) . '/../../cenaPhp/class/class.pgg_JPN.php' );
require_once( dirname( __FILE__ ) . '/../../cenaPhp/Html/Control.php' );
require_once( dirname( __FILE__ ) . '/../../cenaPhp/Html/Form.php' );
require_once( dirname( __FILE__ ) . '/../../cenaPhp/Dba/Record.php' );
require_once( dirname( __FILE__ ) . '/../../cenaPhp/Dba/Model.php' );
require_once( dirname( __FILE__ ) . '/../../cenaPhp/Cena/Record.php' );
require_once( dirname( __FILE__ ) . '/../../cenaPhp/Cena/Envelope.php' );
require_once( __DIR__ . '/../lib/lib_contact_code.php' );
require_once( __DIR__ . '/../lib/dao.contact100.php' );
require_once( __DIR__ . '/../lib/setup_contact.php' );

use CenaDta\Dba as orm;
use CenaDta\Cena as cena;

cena\Cena::useEnvelope();
$dao  = 'dao_contact100';

class appContacts extends AmidaMVC\Component\Model
{
    static $dao = 'dao_contact100';
    // +-------------------------------------------------------------+
    function actionDefault( $ctrl, &$view )
    {
        $view = 'list of contacts.';
        $dao = static::$dao;
        $obj = $dao::getInstance( __DIR__ . '/../lib/dba.ini.php' );

        $opt = array( 'limit' => 4 );
        $obj->order( $obj->getIdName() )
            ->makeSQL( orm\Sql::SELECT )
            ->setPage( $pn, $opt )
            ->fetchRecords( $records )
        ;
        $cenas = array();
        if( !empty( $records ) )
            foreach( $records as $rec ) {
                $cenas[] = new cena\Record( $rec );
            }
        $page->add( 'records', $cenas );
        $page->add( 'html_type', 'NAME' );
        $page->add( 'pn', $pn );
        $page->nextAct( 'edit' );
        if( WORDY ) wt( $records, 'records' );
    }
    // +-------------------------------------------------------------+
}

$ctrl->prependModel( 'appContacts' );


