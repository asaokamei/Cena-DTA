<?php
/**
 * Contract Only PHP demo. uses AmidaMVC.  
 */
define( 'WORDY', FALSE );
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
        $loadInfo = $siteObj->get( 'loadInfo' );
        $opt = array( 'limit' => 4, 'start' => $loadInfo['offset'] );
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
    static $title = "";
    // +-------------------------------------------------------------+
    static function _init(
        \AmidaMVC\Framework\Controller $ctrl,
        \AmidaMVC\Component\SiteObj &$siteObj  )
    {
        static::$app_url = $ctrl->getBaseUrl();
        static::$title = '<title>Contact PHP Demo</title>' . 
            '<p>pure and plain PHP demo. no JavaScript!</p>';
    }
    // +-------------------------------------------------------------+
    static function makeContents( $content ) {
        $content = static::$title . "<div class=\"contractView\">
        {$content}
        </div>";
        return $content;
    }
    // +-------------------------------------------------------------+
    static function actionDefault(
        \AmidaMVC\Framework\Controller $ctrl,
        \AmidaMVC\Component\SiteObj &$siteObj,
        $viewData )
    {
        $base_url = $ctrl->getBaseUrl();
        extract( $viewData );
        ob_start();
        ob_implicit_flush(0);
?>        
        <table width="100%">
    <thead>
    <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Gender</th>
    <th>Type</th>
    <th>Date</th>
    <th>Details</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for( $i=0; $i<count( $records ); $i++ ) {
    $rec = $records[ $i ];
    ?>
        <tr>
          <td height="30"><?php echo $rec->getCenaId(); ?></td>
          <td><?php echo $rec->popHtml( 'contact_name', $html_type ); ?></td>
          <td align="center"><?php echo $rec->popHtml( 'contact_gender',  $html_type  ); ?></td>
          <td align="center"><?php echo $rec->popHtml( 'contact_type',    $html_type  ); ?></td>
          <td align="center"><?php echo $rec->popHtml( 'contact_date',    $html_type  ); ?></td>
          <td align="center"><form name="form1" method="post" action="<?php echo static::$app_url; ?>contact_env2.php" style="margin:0px; padding:0px; ">
            <?php echo $rec->popIdHidden(); ?>
                    <input type="submit" name="Submit2" value="show">
          </form>          </td>
        </tr>
        <?php } ?>
      </tbody>
  </table>
  <p><?php if( $pn ) echo self::Pager( $pn, 'list' ); ?>&nbsp;</p>
  <p align="center">&nbsp;</p>
  </div>
  <p>
      <input type="button" name="top" value="List of Contacts" onClick="location.href='contact_env1.php'">
  </p>
    <?php
        $content = ob_get_clean();
        ob_clean();
        $siteObj->setContents( self::makeContents( $content ) );
    }
}

appContact::_init( $_ctrl, $_siteObj );
viewContact::_init( $_ctrl, $_siteObj );

