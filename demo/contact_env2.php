<?php
//define( 'WORDY', 4 );
require_once( dirname( __FILE__ ) . '/../cenaPhp/class/class.pgg_JPN.php' );
require_once( dirname( __FILE__ ) . '/../cenaPhp/class/class.msg_box.php' );
require_once( dirname( __FILE__ ) . '/../cenaPhp/class/class.page_mc.php' );
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

cena\Cena::useEnvelope();
$page = new page_MC();
$dao  = 'dao_contact110';

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
function done_records( $page, $method )
{
	$page->getData( 'dao', $dao );
	$num_err     = 0;
	cena\Cena::setRelation();
	cena\Cena::set_models( array( 'dao_contact100', 'dao_contact110' ) );
	cena\Cena::do_cena( $cenas, 'doAction' );
	
	$rec100 = array();
	$rec110 = array();
	$rec100[] = $cenas[ 'dao_contact100' ][0];
	$rec110   = $cenas[ 'dao_contact110' ];
	$page->addData( 'rec100', $rec100 );
	$page->addData( 'rec110', $rec110 );
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
	cena\Cena::setRelation();
	cena\Cena::do_cena( $cenas, 'doValidate' );
	
	$rec100 = array();
	$rec110 = array();
	$rec100[] = $cenas[ 'dao_contact100' ][0];
	if( !empty( $cenas[ 'dao_contact110' ] ) ) 
	foreach( $cenas[ 'dao_contact110' ] as $cena ) {
		if( $cena->getRecord()->execute != orm\Record::EXEC_NONE ) {
			$rec110[] = $cena;
		}
	}
	$page->addData( 'rec100', $rec100 );
	$page->addData( 'rec110', $rec110 );

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
function edit_records( $page, $method )
{
	$page->getData( 'dao', $dao );
	$num_err     = 0;
	cena\Cena::setRelation();
	cena\Cena::do_cena( $cenas );
	$page->addData( 'cenas', $cenas[ $dao ] );
	
	$rec100   = $cenas[ 'dao_contact100' ];
	$parent   = $rec100[0];
	$rec110   = $cenas[ 'dao_contact110' ];
	$max = 10 - count( $rec110 );
	for( $i = 0; $i < $max; $i ++ ) {
		$title = sprintf( "新規#%02d", $i + 1 );
		$child = dao_contact110::getRecord( orm\Record::TYPE_NEW, $title );
		$cena  = cena\Cena::getCenaByRec( $child );
		$cena->setRelation( 'contact_id', $parent );
		$rec110[] = $cena;
	}
	$page->addData( 'rec100', $rec100 );
	$page->addData( 'rec110', $rec110 );
	$page->addData( 'html_type', 'EDIT' );
	$page->setNext( 'check' );
}

// +-----------------------------------------------------------+
function get_page( $page, $method )
{
	$page->getData( 'dao', $dao );
	cena\Cena::setRelation();
	cena\Cena::set_models( array( 'dao_contact100' ) );
	cena\Cena::do_cena( $cenas );
	$rec100 = $cenas[ 'dao_contact100' ];
	$page->addData( 'rec100', $rec100 );
	
	$rec100[0]->loadChildren();
	$records = $rec100[0]->getChildren( 'dao_contact110', FALSE );
	$rec110  = array();
	for( $i = 0; $i < count( $records ); $i++ ) {
		$rec = $records[$i];
		$rec110[] = cena\Cena::getCenaByRec( $rec );
	}
	$page->addData( 'rec110', $rec110 );
	
	$page->addData( 'html_type', 'NAME' );
	$page->addData( 'pn', $pn );
	$page->setNext( 'edit' );
	if( WORDY ) wt( $records, 'records' );
}

// +-----------------------------------------------------------+
function new_records( $page, $method )
{
	$rec    = dao_contact100::getRecord( orm\Record::TYPE_NEW, '新規' );
	$parent = cena\Cena::getCenaByRec( $rec );
	$rec100 = array( $parent );
	
	$rec110 = array();
	for( $i = 0; $i < 10; $i ++ ) {
		$title = sprintf( "新規%02d", $i + 1 );
		$rec   = dao_contact110::getRecord( orm\Record::TYPE_NEW, $title );
		$child = cena\Cena::getCenaByRec( $rec );
		$child->setRelation( 'contact_id', $parent );
		$rec110[] = $child;
	}
		if( $i==0 ) wt( $child, 'child' );
	$page->addData( 'rec100', $rec100 );
	$page->addData( 'rec110', $rec110 );
	$page->addData( 'html_type', 'NEW' );
	$page->setNext( 'check' );
}

// +-----------------------------------------------------------+

?>
<html><!-- InstanceBegin template="/Templates/cenaDta.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!-- DW6 -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../cena/cena.css" rel="stylesheet" type="text/css">
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
<div id="contents"><!-- InstanceBeginEditable name="contents" -->
  <h1><span class="bread"><a href="../index.php">Cena-DTA</a>:: <a href="index.php">Contact Demo</a>:: contact_env</span><a href="contact_env1.php">List of Contacts</a>:: Contact Details </h1>
  <div style="width:600px; ">
  <p><span class="style2"><a href="contact_env2.php?next_act=new">[Add New Contact</a>]</span></p>
  <form name="restic" method="post" action="contact_env2.php">
  <table class="tblHover" width="100%">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Gender</th>
        <th>Type</th>
        <th>Date</th>
        <th>Delete</th>
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
        <th>Connection/Relation</th>
        <th>Type</th>
        <th>Delete</th>
      </tr>
    </thead>
    <tbody>
      <?php
  for( $i=0; $i<count( $rec110 ); $i++ ) { 
    $cena = $rec110[ $i ]; 
?>
      <tr>
        <td height="30"><?php echo $cena->getCenaId(); ?><?php echo $cena->popIdHidden(); ?></td>
        <td><?php echo $cena->popHtml( 'connect_info', $html_type ); ?><br>
          <?php echo $cena->getRelation('contact_id'); ?>
		  <?php echo $cena->popRelations(); ?></td>
        <td align="center"><?php echo $cena->popHtml( 'connect_type',    $html_type  ); ?></td>
        <td><?php echo $cena->popHtmlState( $html_type ); ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <p>&nbsp;</p>
  <p align="center">
	<?php echo $page->getNextActHiddenTag(); ?>
    <input type="submit" name="Submit" value="<?php echo $page->getButtonTitle(); ?>">
  </p>
  </form>
  </div>
  <p>
    <input type="button" name="top" value="List of Contacts" onClick="location.href='contact_env1.php'">
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
