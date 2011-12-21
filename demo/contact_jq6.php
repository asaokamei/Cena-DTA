<?php
//define( 'WORDY', 4 );
require_once( dirname( __FILE__ ) . '/../cenaPhp/class/class.pgg_JPN.php' );
require_once( dirname( __FILE__ ) . '/../cenaPhp/Html/Control.php' );
require_once( dirname( __FILE__ ) . '/../cenaPhp/Html/Form.php' );
require_once( dirname( __FILE__ ) . '/../cenaPhp/Dba/Record.php' );
require_once( dirname( __FILE__ ) . '/../cenaPhp/Cena/Record.php' );
require_once( dirname( __FILE__ ) . '/../cenaPhp/Cena/Envelope.php' );
require_once( 'lib_contact_code.php' );
require_once( 'dao.contact100.php' );
require_once( 'dao.contact110.php' );
require_once( 'setup_contact.php' );

use CenaDta\Dba as orm;
use CenaDta\Cena as cena;

//Cena::useEnvelope();
$page = new CenaDta\Html\Control();

$page	->setDefault( 'none',               'none' )
		->setAction(  'check', 'check_records', 'Confirm Upload' )
		->setAction(  'done',  'done_records',  'Save Upload' )
		->setAction(  'down',  'goto_download', 'Download Data' )
        ->setAction(  'cena',  'cena_env',      'Cena Envelope' )
;

$page->action();
extract( $page->get() );

// +-----------------------------------------------------------+
function goto_download( $page, $method )
{
	header( 'Location: contact_jq2.php' );
}

// +-----------------------------------------------------------+
function cena_env( $page, $method )
{
    $cena = $_REQUEST[ 'cena' ];
    echo $cena;
    cena\Cena::proc_env( $cena, 'doAction' );
    exit;
}

// +-----------------------------------------------------------+
function done_records( $page, $method )
{
	$num_err     = 0;
	cena\Cena::set_models( array( 'dao_contact100', 'dao_contact110' ) );
	
	cena\Cena::do_cena( $cenas, 'doAction' );
	
	$page->add( 'rec100', $cenas[ 'dao_contact100' ] );
	$page->add( 'rec110', $cenas[ 'dao_contact110' ] );
	if( $num_err ) {
		$page->add( 'html_type', 'EDIT' );
		$page->nextAct( 'check' );
	}
	else {
		$page->addData( 'html_type', 'NAME' );
		$page->nextAct( 'down' ); // to default
	}
}

// +-----------------------------------------------------------+
function check_records( $page, $method )
{
	$num_err = cena\Cena::do_cena( $cenas, 'doValidate' );
	
	$page->add( 'rec100', $cenas[ 'dao_contact100' ] );
	$page->add( 'rec110', $cenas[ 'dao_contact110' ] );

	if( $num_err ) {
		$page->add( 'html_type', 'EDIT' );
		$page->nextAct( 'check' );
	}
	else {
		$page->add( 'html_type', 'PASS' );
		$page->nextAct( 'done' );
	}
}


// +-----------------------------------------------------------+

?>
<!DOCTYPE html><html lang="en"><!-- InstanceBegin template="/Templates/cenaDta.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!-- DW6 -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../common/cena.css" rel="stylesheet" type="text/css">
<!-- InstanceBeginEditable name="doctitle" -->
<title>Contact Demo Jq ::Cena-DTA Development</title>
<!-- InstanceEndEditable -->
<style type="text/css">
<!--
.style1 {
}
-->
</style>
<!-- InstanceBeginEditable name="head" -->
<style type="text/css">
<!--
.style1 {color: #FF6600}
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body>
<div id="header">
  <p><a href="../index.php" class="headerTitle"><strong>Cena</strong> DTA Developments </a></p>
  <div class="menus">|&nbsp;<a href="../index.php">Top</a>&nbsp;|&nbsp;<a href="http://wsjp.blogspot.com/" target="_blank"></a></div>
  <p align="left" class="headDesc">for HTML5 applications using local databases.</p>
</div>
<div id="contents"><!-- InstanceBeginEditable name="contents" -->
<script language="javascript" type="text/javascript" src="../common/jquery-1.6.2.js"></script>
<h1><span class="bread"><a href="../index.php">Cena-DTA</a>:: <a href="index.php">Contact Demo</a>:: contact_jq</span><a href="contact_jq3.html">List of Contacts</a>:: Upload HTML Form</h1>
<div style="width:600px; ">
    <form name="form1" method="post" action="">
    <table class="tblHover" width="100%">
      <thead>
        <tr>
          <th>ID</th>
          <th>名前</th>
          <th>性別</th>
          <th>分類</th>
          <th>日付</th>
          <th>削除</th>
        </tr>
      </thead>
      <tbody>
        <?php
  for( $i=0; $i<count( $rec100 ); $i++ ) { 
    $cena = $rec100[ $i ]; 
?>
        <tr>
          <td height="30"><?php echo $cena->getCenaId(); ?><?php echo $cena->popIdHidden(); ?></td>
          <td><?php echo $cena->popHtml( 'contact_name', $html_type ); ?></td>
          <td align="center"><?php echo $cena->popHtml( 'contact_gender',  $html_type  ); ?></td>
          <td align="center"><?php echo $cena->popHtml( 'contact_type',    $html_type  ); ?></td>
          <td align="center"><?php echo $cena->popHtml( 'contact_date',    $html_type  ); ?></td>
          <td><?php echo $cena->popHtmlState( $html_type ); ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <p>&nbsp;</p>
    <table class="tblHover" width="100%">
      <thead>
        <tr>
          <th>ID</th>
          <th>通信</th>
          <th>分類</th>
          <th>削除</th>
        </tr>
      </thead>
      <tbody>
        <?php
  for( $i=0; $i<count( $rec110 ); $i++ ) { 
    $cena = $rec110[ $i ]; 
	if( $cena->getType() == orm\Record::TYPE_IGNORE ) continue;
?>
        <tr>
          <td height="30"><?php echo $cena->getCenaId(); ?><?php echo $cena->popIdHidden(); ?><?php echo $cena->popRelations(); ?></td>
          <td><?php echo $cena->popHtml( 'connect_info', $html_type ); ?></td>
          <td align="center"><?php echo $cena->popHtml( 'connect_type',    $html_type  ); ?></td>
          <td><?php echo $cena->popHtmlState( $html_type ); ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <p>
      &nbsp;</p>
    <p align="center"> <?php echo $page->savePost(); ?>
      <input type="submit" name="Submit" value="<?php echo $page->getNextTitle(); ?>">
    </p>
    </form>
    <div id="cena_msg"></div>
  </div>
  <p>
    <input type="button" name="top" value="List of Contacts" onClick="location.href='contact_jq3.html'">
  </p>
  <!-- InstanceEndEditable --></div>
<div id="footer">
  <table  border="0" align="center" cellpadding="0" cellspacing="0">
    <tr valign="top">
      <td valign="bottom" nowrap class="footDesc"><p>Cena developed 
        by <a href="../../index.php"><strong>WorkSpot.JP</strong></a>&nbsp;
      </p>
      </td>
      <td width="6">&nbsp;</td>
      <td width="100"><a href="../../serv/index.php"><img src="../common/img/bar_ser.gif" width="100" height="30" border="0" alt="Service（業務内容） （写真：デビルズタワー国定公園、アメリカ）"></a></td>
      <td width="100"><a href="../../expc/index.php"><img src="../common/img/bar_exp.gif" width="100" height="30" border="0" alt="Experience（実績･経験）　（写真：紀伊半島にある筆薮滝）"></a></td>
      <td width="100"><a href="../../prof/index.php"><img src="../common/img/bar_pro.gif" width="100" height="30" border="0" alt="Profile（経歴）　（写真：バッドランド国立公園、アメリカ）"></a></td>
      <td width="100"><a href="../../tech/index.php"><img src="../common/img/bar_tec.gif" width="100" height="30" border="0" alt="Technology（技術）　（写真：東京フォーラム）"></a></td>
    </tr>
    <tr valign="top">
      <td colspan="6" align="center" nowrap><span class="copyright">copyright (c) 2010-<?php echo date('Y'); ?> WorkSpot.JP</span></td>
    </tr>
  </table>
</div>
</body>
<!-- InstanceEnd --></html>
