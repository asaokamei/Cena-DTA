<?php
//define( 'WORDY', 2 );
require_once( dirname( __FILE__ ) . '/../cenaPhp/class/class.pgg_JPN.php' );
require_once( dirname( __FILE__ ) . '/../cenaPhp/class/class.msg_box.php' );
require_once( dirname( __FILE__ ) . '/../cenaPhp/class/class.page_mc.php' );
require_once( dirname( __FILE__ ) . '/../cenaPhp/Html/Form.php' );
require_once( dirname( __FILE__ ) . '/../cenaPhp/Dba/Record.php' );
require_once( dirname( __FILE__ ) . '/../cenaPhp/Cena/Record.php' );
require_once( dirname( __FILE__ ) . '/../cenaPhp/Cena/Envelope.php' );
require_once( 'lib_contact_code.php' );
require_once( 'dao.contact100.php' );
require_once( 'setup_contact.php' );

use CenaDta\Dba as orm;
use CenaDta\Cena as cena;

cena\Cena::useEnvelope();
$page = new page_MC();
$dao  = 'dao_contact100';

$page	->addData(    'dao', $dao )
		->setDefault( 'get_page',               '一覧表示' )
		->setAct(     'edit_records',  'edit',  '修正する' )
		->setAct(     'check_records', 'check', '内容を確認する' )
		->setAct(     'new_records',   'new',   '新規登録する' )
		->setAct(     'done_records',  'done',  '内容を反映する' )
;

$page->main();
extract( $page->data() );

// +-----------------------------------------------------------+
function get_page( $page, $method )
{
	$page->getData( 'dao', $dao );
	$obj = $dao::getInstance();
	
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
	$page->addData( 'records', $cenas );
	$page->addData( 'html_type', 'NAME' );
	$page->addData( 'pn', $pn );
	$page->setNext( 'edit' );
	if( WORDY ) wt( $records, 'records' );
}

// +-----------------------------------------------------------+
function done_records( $page, $method )
{
	$page->getData( 'dao', $dao );
	$num_err     = 0;
	$records     = array();
	$get_types = $_POST['rest']['Record'][ $dao ];
	if( !empty( $get_types ) )
	foreach( $get_types as $type => $get_ids ) 
	{
		foreach( $get_ids as $id => $actions ) 
		{
			$rec = $dao::getRecord( $type, $id );
			$rec->setActions( $actions );
			try {
				$rec->validate();
				$rec->doAction();
			} 
			catch( DataInvalid_DbaRecord_Exception $e ) {
				$num_err ++;
			}
			$records[] = $rec;
		}
	}
	$page->addData( 'records', $records );
	if( $num_err ) {
		$page->addData( 'html_type', 'EDIT' );
		$page->setNext( 'check' );
	}
	else {
		$page->setNext( '' ); // to default
	}
}

// +-----------------------------------------------------------+
function check_records( $page, $method )
{
	$page->getData( 'dao', $dao );
	$num_err     = 0;
	$records     = array();
	$get_types = $_POST['rest']['Record'][ $dao ];
	if( !empty( $get_types ) )
	foreach( $get_types as $type => $get_ids ) 
	{
		foreach( $get_ids as $id => $actions ) 
		{
			$rec = $dao::getRecord( $type, $id );
			$rec->setActions( $actions );
			try {
				$rec->validate();
			} 
			catch( DataInvalid_DbaRecord_Exception $e ) {
				$num_err ++;
			}
			$records[] = $rec;
		}
	}
	$page->addData( 'records', $records );
	if( $num_err ) {
		$page->addData( 'html_type', 'EDIT' );
		$page->setNext( 'check' );
	}
	else {
		$page->addData( 'html_type', 'PASS' );
		$page->setNext( 'done' );
	}
}

// +-----------------------------------------------------------+
function new_records( $page, $method )
{
	$page->getData( 'dao', $dao );
	$records     = array();
	for( $i = 0; $i < 3; $i ++ ) {
		$records[] = $dao::getRecord( orm\Record::TYPE_NEW, $i );
	}
	$page->addData( 'records', $records );
	$page->addData( 'html_type', 'NEW' );
	$page->setNext( 'check' );
}

// +-----------------------------------------------------------+
function edit_records( $page, $method )
{
	$page->getData( 'dao', $dao );
	$records     = array();
	$get_ids = $_POST['rest']['Record'][ $dao ][ orm\Record::TYPE_GET ];
	if( !empty( $get_ids ) )
	foreach( $get_ids as $id => $actions ) {
		$records[] = $dao::getRecord( orm\Record::TYPE_GET, $id );
	}
	$page->addData( 'records', $records );
	$page->addData( 'html_type', 'EDIT' );
	$page->setNext( 'check' );
}

// +-----------------------------------------------------------+

?>
<html><!-- InstanceBegin template="/Templates/cenaDta.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!-- DW6 -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../cena.css" rel="stylesheet" type="text/css">
<!-- InstanceBeginEditable name="doctitle" -->
<title>ワークスポット･ジェーピー</title>
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
.style2 {font-weight: bold}
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
<div id="contents">
<!-- InstanceBeginEditable name="contents" -->
  <h1><span class="bread"><a href="../index.php">Cena-DTA</a>:: <a href="index.php">Contact Demo</a>:: contact_env</span>List of Contacts</h1>
  <div style="width:600px; ">
  <p><a href="contact_env2.php?next_act=new"><span class="style2"><a href="contact_env2.php?next_act=new">[Add New Contact</a>]</span></a></p>
  
  <table class="tblHover" width="100%">
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
          <td align="center"><form name="form1" method="post" action="contact_env2.php" style="margin:0px; padding:0px; ">
            <?php echo $rec->popIdHidden(); ?>
                    <input type="submit" name="Submit2" value="show">
          </form>          </td>
        </tr>
        <?php } ?>
      </tbody>
  </table>
  <p><?php if( $pn ) orm\pn_disp_all( $pn ); ?>&nbsp;</p>
  <p align="center">&nbsp;</p>
  </div>
  <p>
    <input type="button" name="top" value="List of Contacts" onClick="location.href='contact_env1.php'">
  </p>
<!-- InstanceEndEditable -->
</div>
<div id="footer">
  <table  border="0" align="center" cellpadding="0" cellspacing="0">
    <tr valign="top">
      <td valign="bottom" nowrap class="footDesc"><p>Cena developed 
        by <a href="../../index.php"><strong>WorkSpot.JP</strong></a>&nbsp;
      </p>      </td>
      <td width="6">&nbsp;</td>
      <td width="100"><a href="../../serv/index.php"><img src="../../com/img/bar_ser.gif" width="100" height="30" border="0" alt="Service（業務内容） （写真：デビルズタワー国定公園、アメリカ）"></a></td>
      <td width="100"><a href="../../expc/index.php"><img src="../../com/img/bar_exp.gif" width="100" height="30" border="0" alt="Experience（実績･経験）　（写真：紀伊半島にある筆薮滝）"></a></td>
      <td width="100"><a href="../../prof/index.php"><img src="../../com/img/bar_pro.gif" width="100" height="30" border="0" alt="Profile（経歴）　（写真：バッドランド国立公園、アメリカ）"></a></td>
      <td width="100"><a href="../../tech/index.php"><img src="../../com/img/bar_tec.gif" width="100" height="30" border="0" alt="Technology（技術）　（写真：東京フォーラム）"></a></td>
    </tr>
    <tr valign="top">
      <td colspan="6" align="center" nowrap><span class="copyright">copyright (c) 2010-<?php echo date('Y'); ?> WorkSpot.JP</span></td>
    </tr>
  </table>
</div>
</body>
<!-- InstanceEnd --></html>
