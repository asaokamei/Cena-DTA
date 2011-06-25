<?php
//define( 'WORDY', 4 );
include( dirname( __FILE__ ) . '/../cenaPhp/class/class.msg_box.php' );
include( dirname( __FILE__ ) . '/../cenaPhp/Dba/Sql.php' );
include( 'setup_contact.php' );

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
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body>
<div id="header">
  <p><a href="../index.php" class="headerTitle"><strong>Cena</strong> DTA Developments </a></p>
  <div class="menus">|&nbsp;<a href="../index.php">Top</a>&nbsp;|&nbsp;<a href="http://wsjp.blogspot.com/" target="_blank"></a></div>
  <p align="left" class="headDesc">for HTML5 applications using local databases.</p>
</div>
<div id="contents"><!-- InstanceBeginEditable name="contents" -->
  <h1><a href="../index.php"><span class="bread">Cena-DTA</span></a> Contact Demo</h1>
  <ul>
    <li class="list"><a href="contact_init.php"><strong>initialize contact database</strong></a><br>
      checks database configuration, and <br>
creates 10 contacts and connections as initial data for testing.</li>
    <li class="list"><a href="contact_env1.php"><strong>contact_env</strong></a><br>
    Server only PHP demo (cenaPhp). <br>
    Requires server running PHP5.3 or higher, and MySQL database. </li>
    <li class="list"><a href="contact_jq3.html"><strong>contact_jq</strong></a><br>
Html5's local DB demo.<br>
    Requires server running PHP5.3 or higher, and MySQL database; browsers with HTML5's WebSqlDatabase (such as Chrome) and jQuery. uses cenaPhp, jqCena, and jqCenaSql. <br>
    </li>
  </ul>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
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
