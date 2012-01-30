<?php

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
        $content = viewContactHtml::viewHtmlDefault( $ctrl, $siteObj, $viewData );
        $siteObj->setContents( self::makeContents( $content ) );
    }
    // +-------------------------------------------------------------+
    static function actionDetail(
        \AmidaMVC\Framework\Controller $ctrl,
        \AmidaMVC\Component\SiteObj &$siteObj,
        $viewData )
    {
        $cena_id = $viewData[ 'cena_id' ];
        $viewData[ 'html_type' ] = 'NAME';
        $viewData[ 'nextAction' ] = "detail/{$cena_id}/_edit";
        $content = viewContactHtml::viewHtmlDetail( $ctrl, $siteObj, $viewData );
        $siteObj->setContents( self::makeContents( $content ) );
    }
    // +-------------------------------------------------------------+
    static function actionDetail_Edit(
        \AmidaMVC\Framework\Controller $ctrl,
        \AmidaMVC\Component\SiteObj &$siteObj,
        $viewData )
    {
        $cena_id = $viewData[ 'cena_id' ];
        $viewData[ 'html_type' ] = 'EDIT';
        $viewData[ 'nextAction' ] = "detail/{$cena_id}/_put";
        $content = viewContactHtml::viewHtmlDetail( $ctrl, $siteObj, $viewData );
        $siteObj->setContents( self::makeContents( $content ) );
    }
    // +-------------------------------------------------------------+
    static function actionDetail_New(
        \AmidaMVC\Framework\Controller $ctrl,
        \AmidaMVC\Component\SiteObj &$siteObj,
        $viewData )
    {
        $cena_id = $viewData[ 'cena_id' ];
        $viewData[ 'html_type' ] = 'EDIT';
        $viewData[ 'nextAction' ] = "detail/{$cena_id}/_put";
        $content = viewContactHtml::viewHtmlDetail( $ctrl, $siteObj, $viewData );
        $siteObj->setContents( self::makeContents( $content ) );
    }
    // +-------------------------------------------------------------+
}


class viewContactHtml extends \AmidaMVC\Component\View
{
    // +-------------------------------------------------------------+
    /**
     * HTML for list of contacts view. 
     * @static
     * @param AmidaMVC\Framework\Controller $ctrl
     * @param AmidaMVC\Component\SiteObj $siteObj
     * @param $viewData
     */
    static function viewHtmlDefault(
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
            /** @var $html_type   type of html to output (NAME/EDIT/etc.) */
            /** @var $records array of cena records */
            /** @var $rec  \CenaDta\Cena\Record */
            /** @var $pn  prev/next array data */
            for( $i=0; $i<count( $records ); $i++ ) {
                $rec = $records[ $i ];
                ?>
            <tr>
                <td height="30"><?php echo $rec->getCenaId(); ?></td>
                <td><strong><?php echo $rec->popHtml( 'contact_name', $html_type ); ?></strong></td>
                <td align="center"><?php echo $rec->popHtml( 'contact_gender',  $html_type  ); ?></td>
                <td align="center"><?php echo $rec->popHtml( 'contact_type',    $html_type  ); ?></td>
                <td align="center"><?php echo $rec->popHtml( 'contact_date',    $html_type  ); ?></td>
                <td align="center"><form name="form1" method="get" action="<?php echo static::$app_url; ?>detail/<?php echo $rec->getCenaId(); ?>" style="margin:0px; padding:0px; ">
                    <input type="submit" name="" value="show">
                </form>
                </td>
            </tr>
                <?php } ?>
        </tbody>
    </table>
    <p><?php
        if( $pn ) echo self::Pager( $pn, 'list' ); ?>&nbsp;</p>
    <p align="center">&nbsp;</p>
    <?php
        $content = ob_get_clean();
        return $content;
    }
    // +-------------------------------------------------------------+
    /**
     * HTML for detail view
     * @static
     * @param AmidaMVC\Framework\Controller $ctrl
     * @param AmidaMVC\Component\SiteObj $siteObj
     * @param $viewData
     */
    static function viewHtmlDetail(
        \AmidaMVC\Framework\Controller $ctrl,
        \AmidaMVC\Component\SiteObj &$siteObj,
        $viewData )
    {
        $base_url = $ctrl->getBaseUrl();
        extract( $viewData );
        ob_start();
        ob_implicit_flush(0);
        /** @var string $nextAction  next action */
        ?>
    <form name="cena" method="post" action="<?php echo static::$app_url.$nextAction; ?>">
        <table width="100%">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Gender</th>
                <th>Type</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody>
                <?php
                /** @var $html_type   type of html to output (NAME/EDIT/etc.) */
                /** @var $rec100   array */
                /** @var $rec110   array */
                /** @var $cena     \CenaDta\Cena\Record */
                for( $i=0; $i<count( $rec100 ); $i++ ) {
                    $cena = $rec100[ $i ];
                    ?>
                <tr>
                    <td height="30"><?php echo $cena->getCenaId(); ?><?php echo $cena->popIdHidden(); ?></td>
                    <td><strong><?php echo $cena->popHtml( 'contact_name', $html_type ); ?></strong></td>
                    <td align="center"><?php echo $cena->popHtml( 'contact_gender',  $html_type  ); ?></td>
                    <td align="center"><?php echo $cena->popHtml( 'contact_type',    $html_type  ); ?></td>
                    <td align="center"><?php echo $cena->popHtml( 'contact_date',    $html_type  ); ?></td>
                </tr>
                    <?php } ?>
            </tbody>
        </table>
        <?php if( !empty( $rec110 ) ) { ?>
        <p>&nbsp;</p>
        <table class="tblHover" width="100%">
            <thead>
            <tr>
                <th>ID</th>
                <th>Connection/Relation</th>
                <th>Type</th>
            </tr>
            </thead>
            <tbody>
                <?php
                for( $i=0; $i<count( $rec110 ); $i++ ) {
                    $cena = $rec110[ $i ];
                    ?>
                <tr>
                    <td height="30"><?php echo $cena->getCenaId(); ?><?php echo $cena->popIdHidden(); ?></td>
                    <td><strong><?php echo $cena->popHtml( 'connect_info', $html_type ); ?></strong><br>
                        <?php echo $cena->getRelation('contact_id'); ?>
                        <?php echo $cena->popRelations(); ?></td>
                    <td align="center"><?php echo $cena->popHtml( 'connect_type',    $html_type  ); ?></td>
                </tr>
                    <?php } ?>
            </tbody>
        </table>
        <?php } ?>
        <input type="submit" name="submit" value="<?php echo $nextAction;?>" />
    </form>
    <p>
        <input type="button" name="top" value="List of Contacts" onClick="location.href='<?php echo static::$app_url; ?>list'">
    </p>
    <?php
        $content = ob_get_clean();
        return $content;
    }
    // +-------------------------------------------------------------+
}