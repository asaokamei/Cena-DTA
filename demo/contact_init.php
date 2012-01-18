<?php
//define( 'WORDY', 4 );
require_once( dirname( __FILE__ ) . '/../src/CenaDta/class/class.pgg_JPN.php' );
require_once( dirname( __FILE__ ) . '/../src/CenaDta/class/class.page_mc.php' );
require_once( 'lib_contact_code.php' );
require_once( 'setup_contact.php' );
use CenaDta\Dba as orm;

$db_init = dirname( __FILE__ ) . '/dba.ini.php';
$page = new page_MC();
$page
	->addData(    'dba_ini', $db_init )
	->setDefault( 'begin_init',              'Begin Initialize DB' )
	->setAct(     'conn_database',  'conn',  'Check DB Connection' )
	->setAct(     'init_database',  'init',  'Initialize DB' )
	->setAct(     'done_init',      'done',  'Back to Cena-DTA Dev Page' )
;
$page->main();
extract( $page->data() );

function begin_init( $page, $method )
{
	$page->getData( 'dba_ini', $db_init );
	$msg = "
	Please modify the configuration file at: <br />
	{$db_init}<br />
	<br />
	Then, click on 'Check DB Connection' button to check the configuration.<br /><br />";
	$page->addData( 'title',  'Begins DB Initialization' );
	$page->addData( 'message', $msg );
	$page->setNext( 'conn' );
	
	return TRUE;
}

function conn_database( $page, $method )
{
	$page->getData( 'dba_ini', $db_init );
	$page->addData( 'err_num', 0 );
	$page->addData( 'title',  'DB Connection Check' );
	try{
		$sql = new orm\Sql( $db_init );
	}
	catch ( DbaSql_Exception $e ) {
		$page->setNext( 'conn' );
		$msg = "DB Connection Error. Please check configuration file at<br />{$db_init}<br />" . $e->getMessage();
		$page->addData( 'message', $msg );
		$page->addData( 'err_num', 1 );
		return FALSE;
	}
	catch ( Exception $e ) {
		$page->setNext( 'conn' );
		$msg = "Unknown Error: <br />" . $e->getMessage();
		$page->addData( 'message', $msg );
		$page->addData( 'err_num', 1 );
		return FALSE;
	}
	$page->setNext( 'init' );
	$msg = "DB Connection check was successfull. <br />
	<br />
	Click 'Initialize DB' button to create initial data for contact demo.<br /><br />
	";
	$page->addData( 'message', $msg );
	return TRUE;
}

function init_database( $page, $method )
{
	$page->getData( 'dba_ini', $db_init );
	$page->addData( 'err_num', 0 );
	$sql = new orm\Sql( $db_init );
	$sql->execSQL( SetUp_Contact::getDropContact() );
	$sql->execSQL( SetUp_Contact::getCreateContact() );
	$sql->table( SetUp_Contact::getContactTable() );
	for( $i = 0; $i < 10; $i ++ ) 
		$sql->execInsert(  SetUp_Contact::getContactData( $i ) );
	
	$sql->clear();
	$sql->execSQL( SetUp_Contact::getDropConnect() );
	$sql->execSQL( SetUp_Contact::getCreateConnect() );
	$sql->table( SetUp_Contact::getConnectTable() );
	for( $i = 0; $i < 10; $i ++ ) {
		$sql->execInsert(  SetUp_Contact::getConnectData( $i ) );
	}
	$page->addData( 'title',  'Initialize DB' );
	$page->setNext( 'done' );
	$msg = "(If you are seeing this message without strange output)<br />
	initializing DB was successfull!!! <br />
	<br />
	Click 'Back to Demo Top Page' button to start demo!<br /><br />
	";
	$page->addData( 'message', $msg );
}

function done_init( $page, $method )
{
	jump_to_url( '../index.php' );
}

?>
<!DOCTYPE html><html lang="en"><!-- InstanceBegin template="/Templates/cenaDta.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!-- DW6 -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../common/cena.css" rel="stylesheet" type="text/css">
<!-- InstanceBeginEditable name="doctitle" -->
<title>Initialize Demo ::Cena-DTA Development</title>
<!-- InstanceEndEditable -->
<style type="text/css">
<!--
.style1 {
}
-->
</style>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body>
<div id="header">
  <p><a href="../index.php" class="headerTitle"><strong>Cena</strong> DTA Developments </a></p>
  <div class="menus">|&nbsp;<a href="../index.php">Top</a>&nbsp;|&nbsp;<a href="http://wsjp.blogspot.com/" target="_blank"></a></div>
  <p align="left" class="headDesc">for HTML5 applications using local databases.</p>
</div>
<div id="contents"><!-- InstanceBeginEditable name="contents" -->
  <h1><span class="bread"><a href="../index.php">Cena-DTA</a>:: </span>Initialize Contact Database </h1>
  <div style="width:80%; border: solid 2px #E0E0E0; margin:15px auto 25px auto; padding:0px 7px 7px 7px; ">
  <form name="form1" method="post" action="contact_init.php">
  <h2><?php echo $title;?></h2>
  <?php if( $err_num ) { ?>
  <p align="center"><span style="font-weight:bold; color:red;"><?php echo $message;?></span></p>
  <?php } else { ?>
  <p><?php echo $message;?></p>
  <?php } ?>
  <?php echo $page->getNextActHiddenTag(); ?>
  <p align="center">
    <input type="submit" name="Submit" value="<?php echo $page->getButtonTitle();?>" style="font-weight:bold; font-size:larger;">
  </p>
  </form>
  </div>
  <p>
    <input type="button" name="top" value="Back to Cena-DTA Dev Top" onClick="location.href='../index.php'">
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
