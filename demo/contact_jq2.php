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
require_once( 'dao.contact110.php' );
require_once( 'setup_contact.php' );

use CenaDta\Dba as orm;
use CenaDta\Cena as cena;

//Cena::useEnvelope();
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
	cena\Cena::pushEnvelope();
	cena\Cena::setRelation();
	
	$obj = dao_contact100::getInstance();
	$obj->makeSQL( orm\Sql::SELECT )
		->execSQL()
		->fetchRecords( $records )
	;
	cena\Cena::getCenaByRec( $records );
	
	$obj = dao_contact110::getInstance();
	$obj->order( $obj->getIdName() )
		->makeSQL( orm\Sql::SELECT )
		->execSQL()
		->fetchRecords( $records )
	;
	cena\Cena::getCenaByRec( $records );
	
	$env_data = cena\Envelope::popEnvJsData();
	$page->addData( 'env_data', $env_data );
	$page->addData( 'html_type', 'NAME' );
	$page->addData( 'pn', $pn );
	$page->setNext( 'edit' );
	if( WORDY ) wt( $env_data, 'env_data' );
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
<div id="contents">
<!-- InstanceBeginEditable name="contents" -->
<script language="javascript" type="text/javascript" src="../common/jquery-1.4.2.min.js"></script>
<script language="javascript" type="text/javascript" src="../cenaJs/jqCena.js"></script>
<script language="javascript" type="text/javascript" src="../cenaJs/jqCenaSql.js"></script>
<script language="javascript" type="text/javascript">
var env_data = <?php echo $env_data; ?>;

$().ready( function () {
	// ----------------------------------------------------------------------------------
	// new with jQuery plug-ins
	$().cena( 'message', 'jqCena, jQuery PlugIn <br />' );
	
	// ----------------------------------------------------------------------------------
	// save env_data to database
	//$().cenaSql( 'drop' );
	$()	.cenaSql()                   // start cenaSql. 
		.cenaSql( 'clean' )          // drop table and create again. 
		.cenaSql( 'add', env_data )  // add data to Sql db.
	;
	var types = {
		contact_offset: 'int',
		contact_limit:  'int',
		order_column:   'string',
		order_ascend:   'boolean',
		find_column:    'string',
		find_value:     'string',
		find_type:      'string',
		curr_new_id:    'int'
	};
	var defaults = {
		contact_offset: 0,
		contact_limit:  6,
		order_column:   '',
		order_ascend:   true,
		find_column:    '',
		find_value:     '',
		find_type:      '=',
		curr_new_id:    1
	};
	$().cena.storeTypes( types );
	$().cena.storeDefaults( defaults, true );
	// ----------------------------------------------------------------------------------
	// bind env_data to envelope (in form)
	$() .cena( { 	                             // initialize cena.
					env_src:   '#cena_env_src',  // source envelop id.
					env_post:  '#cena_post',     // id to post bound envelope. 
					bind_type: 'replace'         // show contents.
				})
		//.cena( 'add', env_data )
		.cenaSql( 'get' )
		.cena( 'bind', 	
				{                           // bind env_data to envelope.
					model: 'dao_contact100' // bind only this model.
				})
		.cena( 'clean' )
		.cena( 'message', '<br />End of bind. Now elements Activated<br />' )
		.cena( 'activate' )
	;
	/*
	$( '.cena_element' ).change( function() {
		$().cena( 'message', 'update : ' + $(this).attr( 'name' ) + ' to ' + $(this).val() );
	});
*/
	
}); // 
</script>
<h1><span class="bread"><a href="../index.php">Cena-DTA</a>:: <a href="index.php">Contact Demo</a>:: contact_jq</span>Download Data </h1>
<div style="width:600px; ">
    <div id="cena_msg">
      <p>&nbsp;      </p>
      <p align="center">
        <input type="button" name="top2" value="コンタクトの一覧" onClick="location.href='contact_jq3.html'">
      </p>
      <p>&nbsp; </p>
    </div>
    <table class="tblHover" id="cena_post" width="100%">
      <thead>
        <tr>
          <th>名前<br>
            Cena ID</th>
          <th>性別</th>
          <th>分類</th>
          <th>日付</th>
        </tr>
      </thead>
      <tbody>
        <tr id="cena_env_src">
          <td height="25"><input type="text" size="25" name="contact_name" class="cena_element" value="" />
              <br>
            <span class="cena_element" cena="cena_id" name></span></td>
          <td align="center"><label>
            <input type="radio" name="contact_gender" class="cena_element" value="1">
            男性</label>
              <label>
              <input type="radio" name="contact_gender" class="cena_element" value="2">
                女性</label>          </td>
          <td align="center"><select name="contact_type" class="cena_element">
              <option value="1">友達</option>
              <option value="2">仕事</option>
              <option value="3">家族</option>
              <option value="4">その他</option>
            </select>          </td>
          <td align="center"><input type="date" size="10" name="contact_date" class="cena_element" value="" />          </td>
        </tr>
      </tbody>
    </table>
    <p>
      <?php if( $pn ) pn_disp_all( $pn ); ?>
      &nbsp;</p>
    <p align="center"></p>
    <div id="cena_debug"></div>
  </div>
  <p>
    <input type="button" name="top" value="コンタクトの一覧" onClick="location.href='contact_jq3.html'">
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
