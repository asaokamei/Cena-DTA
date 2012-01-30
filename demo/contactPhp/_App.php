<?php
/**
 * Contract Only PHP demo. uses AmidaMVC.  
 */
require_once( __DIR__ . '/../lib_contact_code.php' );
require_once( __DIR__ . '/../dao.contact100.php' );
require_once( __DIR__ . '/../setup_contact.php' );

$_ctrl->prependComponent( array(
    array( 'appContact',  'app' ),
    array( 'viewContact', 'view' ),
));

class appContact extends AmidaMVC\Component\Model
{
    static $dao100;
    static $dao110;
    // +-------------------------------------------------------------+
    static function _init(
        \AmidaMVC\Framework\Controller $ctrl,
        \AmidaMVC\Component\SiteObj &$siteObj  )
    {
        static::$dao100  = 'dao_contact100';
        static::$dao110  = 'dao_contact110';
        return;
    }
    // +-------------------------------------------------------------+
    static function actionDefault(
        \AmidaMVC\Framework\Controller $ctrl,
        \AmidaMVC\Component\SiteObj &$siteObj )
    {
        $dao = static::$dao100;
        $obj = $dao::getInstance();
        $records = NULL;
        $pn     = array();

        $opt = array( 'limit' => 4 );
        $obj->order( $obj->getIdName() )
            ->makeSQL( \CenaDta\Dba\Sql::SELECT )
            ->setPage( $pn, $opt )
            ->fetchRecords( $records )
        ;
        $cenas = array();
        if( !empty( $records ) ) {
            foreach( $records as $rec ) {
                $cenas[] = new \CenaDta\Cena\Record( $rec );
            }
        }
        $viewData = array(
            'records' => $cenas,
            'html_type' => 'NAME',
            'pn' => $pn,
            'nextAction' => 'edit'
        );
        if( WORDY ) wt( $records, 'records' );
        // default is page not found error. 
        return $viewData;
    }
}

class viewContact extends  \AmidaMVC\Component\View
{
    // +-------------------------------------------------------------+
    static function _init(
        \AmidaMVC\Framework\Controller $ctrl,
        \AmidaMVC\Component\SiteObj &$siteObj  )
    {
        
    }
    // +-------------------------------------------------------------+
    static function actionDefault(
        \AmidaMVC\Framework\Controller $ctrl,
        \AmidaMVC\Component\SiteObj &$siteObj,
        $viewData )
    {
        Debug::bug( 'table', $viewData, 'view data @ default' );
    }
}

appContact::_init( $_ctrl, $_siteObj );
viewContact::_init( $_ctrl, $_siteObj );

