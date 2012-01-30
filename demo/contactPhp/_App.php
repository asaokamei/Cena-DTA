<?php
/**
 * Contract Only PHP demo. uses AmidaMVC.  
 */
define( 'WORDY', FALSE );
require_once( dirname( __FILE__ ) . '/../../src/CenaDta/class/class.pgg_JPN.php' );
require_once( __DIR__ . '/../lib_contact_code.php' );
require_once( __DIR__ . '/../dao.contact100.php' );
require_once( __DIR__ . '/../dao.contact110.php' );
require_once( __DIR__ . '/../setup_contact.php' );
require_once( __DIR__ . '/_View.php' );

/** @var $_ctrl  \AmidaMVC\Framework\Controller */
$_ctrl->prependComponent( array(
    array( 'appContact',  'app' ),
    array( 'viewContact', 'view' ),
));

class appContact extends AmidaMVC\Component\Model
{
    static $dao100;
    static $dao110;
    // +-------------------------------------------------------------+
    /**
     * @static
     * @param AmidaMVC\Framework\Controller $ctrl
     * @param AmidaMVC\Component\SiteObj $siteObj
     * @return mixed
     */
    static function _init(
        \AmidaMVC\Framework\Controller $ctrl,
        \AmidaMVC\Component\SiteObj &$siteObj  )
    {
        static::$dao100  = 'dao_contact100';
        static::$dao110  = 'dao_contact110';
        if( $method = self::getRestMethod( $ctrl, $siteObj ) ) {
            $action = $ctrl->getAction();
            $action .= $method;
            $ctrl->setAction( $action );
        }
        return;
    }
    // +-------------------------------------------------------------+
    /**
     * @static
     * @param AmidaMVC\Framework\Controller $ctrl
     * @param AmidaMVC\Component\SiteObj $siteObj
     * @return bool
     */
    static function getRestMethod(
        \AmidaMVC\Framework\Controller $ctrl,
        \AmidaMVC\Component\SiteObj &$siteObj )
    {
        $methods = array( '_post', '_put', '_delete', '_edit', '_new', '_get' );
        $siteInfo = $siteObj->get( 'siteObj' );
        foreach( $methods as $method ) {
            if( in_array( $method, $siteInfo[ 'command' ] ) ) {
                return $method;
            } 
        }
        return FALSE;
    }
    // +-------------------------------------------------------------+
    /**
     * @static
     * @param AmidaMVC\Framework\Controller $ctrl
     * @param AmidaMVC\Component\SiteObj $siteObj
     * @return array
     */
    static function actionDefault(
        \AmidaMVC\Framework\Controller $ctrl,
        \AmidaMVC\Component\SiteObj &$siteObj )
    {
        $dao = static::$dao100;
        $obj = $dao::getInstance();
        $loadInfo = $siteObj->get( 'loadInfo' );
        $records = NULL;
        $pn  = array();
        $opt = array( 'limit' => 4, 'start' => $loadInfo['offset'] );
        $obj->order( $obj->getIdName() )
            ->makeSQL( \CenaDta\Dba\Sql::SELECT )
            ->setPage( $pn, $opt )
            ->fetchRecords( $records )
        ;
        $rec100 = array();
        if( !empty( $records ) ) {
            foreach( $records as $rec ) {
                $rec100[] = new \CenaDta\Cena\Record( $rec );
            }
        }
        $viewData = array(
            'records' => $rec100,
            'html_type' => 'NAME',
            'pn' => $pn,
            'nextAction' => 'edit'
        );
        if( WORDY ) wt( $records, 'records' );
        // default is page not found error. 
        return $viewData;
    }
    // +-------------------------------------------------------------+
    /**
     * @static
     * @param AmidaMVC\Framework\Controller $ctrl
     * @param AmidaMVC\Component\SiteObj $siteObj
     * @return array
     */
    static function actionDetail(
        \AmidaMVC\Framework\Controller $ctrl,
        \AmidaMVC\Component\SiteObj &$siteObj )
    {
        $loadInfo = $siteObj->get( 'loadInfo' );
        $cena_id  = $loadInfo[ 'id' ];
        \CenaDta\Cena\Cena::setRelation();
        $rec100 = array( \CenaDta\Cena\Cena::getCenaByCenaId( $cena_id ) );

        $rec100[0]->loadChildren();
        $records = $rec100[0]->getChildren( 'dao_contact110', FALSE );
        $rec110  = array();
        for( $i = 0; $i < count( $records ); $i++ ) {
            $rec = $records[$i];
            $rec110[] = \CenaDta\Cena\Cena::getCenaByRec( $rec );
        }

        $viewData = array(
            'cena_id' => $cena_id,
            'rec100' => $rec100,
            'rec110' => $rec110,
        );
        if( WORDY ) wt( $records, 'records' );
        return $viewData;
    }
    // +-------------------------------------------------------------+
    /**
     * @static
     * @param AmidaMVC\Framework\Controller $ctrl
     * @param AmidaMVC\Component\SiteObj $siteObj
     * @return array
     */
    static function actionDetail_Edit(
        \AmidaMVC\Framework\Controller $ctrl,
        \AmidaMVC\Component\SiteObj &$siteObj )
    {
        $viewData = self::actionDetail( $ctrl, $siteObj );
        if( have_value( $viewData, 'rec110' ) ) {
            $rec110   = $viewData[ 'rec110' ];
            $max = 10 - count( $rec110 );
        }
        else {
            $max = 10;
        }
        for( $i = 0; $i < $max; $i ++ ) {
            $title = sprintf( "%02d", $i + 1 );
            $child = dao_contact110::getRecord( \CenaDta\Dba\Record::TYPE_NEW, $title );
            $cena  = \CenaDta\Cena\Cena::getCenaByRec( $child );
            $cena->setRelation( 'contact_id', $viewData[ 'rec100' ][0] );
            $rec110[] = $cena;
        }
        $viewData[ 'rec110' ] = $rec110;
        if( WORDY ) wt( $records, 'records' );
        return $viewData;
    }
    // +-------------------------------------------------------------+
    /**
     * @param AmidaMVC\Framework\Controller $ctrl
     * @param AmidaMVC\Component\SiteObj $siteObj
     */
    function actionDetail_Put(
        \AmidaMVC\Framework\Controller $ctrl,
        \AmidaMVC\Component\SiteObj &$siteObj )
    {
        \CenaDta\Cena\Cena::setRelation();
        \CenaDta\Cena\Cena::set_models( array( 'dao_contact100', 'dao_contact110' ) );
        $err_num = \CenaDta\Cena\Cena::do_cena( $cenas, 'doAction' );
        
        if( !$err_num ) {
            $cena_id  = $cenas[ 'dao_contact100' ][0]->getCenaId();
            $ctrl->redirect( "detail/{$cena_id}" );
        }
        // Error! show error message and re-edit.  
        $viewData = array(
            'cena_id' => $cenas['dao_contact100'][0]->getCenaId(),
            'rec100' => $cenas['dao_contact100'],
            'rec110' => $cenas['dao_contact110'],
        );
        $ctrl->setAction( 'detail_edit' );
        return $viewData;
    }
    // +-----------------------------------------------------------+
    function actionDetail_New(
        \AmidaMVC\Framework\Controller $ctrl,
        \AmidaMVC\Component\SiteObj &$siteObj )
    {
        $rec    = dao_contact100::getRecord( \CenaDta\Dba\Record::TYPE_NEW, '1' );
        $parent = \CenaDta\Cena\Cena::getCenaByRec( $rec );
        $rec100 = array( $parent );

        $rec110 = array();
        for( $i = 0; $i < 10; $i ++ ) {
            $title = sprintf( "%02d", $i + 1 );
            $rec   = dao_contact110::getRecord( \CenaDta\Dba\Record::TYPE_NEW, $title );
            $child = \CenaDta\Cena\Cena::getCenaByRec( $rec );
            $child->setRelation( 'contact_id', $parent );
            $rec110[] = $child;
        }
        $viewData = array(
            'cena_id' => $parent->getCenaId(),
            'rec100' => $rec100,
            'rec110' => $rec110,
        );
        return $viewData;
    }
    // +-------------------------------------------------------------+
}

appContact::_init( $_ctrl, $_siteObj );
viewContact::_init( $_ctrl, $_siteObj );

