<?php
ob_start();
session_start();

$text   = '';
$result = '';
if( isset( $_REQUEST[ 'test' ] ) &&
    preg_match( '/^[-_a-zA-Z0-9]+$/', $_REQUEST[ 'test' ] ) ) {
	$test = $_REQUEST[ 'test' ];
	$msg  = do_unittest( $test, $result );
}
else
if( isset( $_REQUEST[ 'info' ] ) ) {
    switch( $_REQUEST[ 'info' ] ) {
        case 'shell':
            $test = 'Shell Info';
            $command = 'echo $SHELL';
            break;
        case 'env':
            $test = 'Environment Info';
            $command = 'env';
            break;
        case 'version':
            $test    = 'PHP version';
            $command = "php -v";
            break;
    }
    if( isset( $command ) ) {
        $results = my_exec( $command );
        $msg = $results[ 'stdout' ];
    }
}

function do_unittest( $test, &$result )
{
	$cwd     = dirname( __FILE__ );
	$test    = str_replace( '_', '/', $test );
	$command = "phpunit {$cwd}/{$test}.php";
	$results = my_exec( $command );
	
	$result = $results[ 'stderr' ];
	return $results[ 'stdout' ];
}

function my_exec( $cmd, $input='' )
{
	$proc = proc_open( 
		$cmd, 
		array( 
			0 => array('pipe', 'r'), 
			1 => array('pipe', 'w'), 
			2 => array('pipe', 'w')
		), 
		$pipes
	);
	fwrite( $pipes[0], $input );                fclose( $pipes[0] );
	$stdout = stream_get_contents( $pipes[1] ); fclose( $pipes[1] );
	$stderr = stream_get_contents( $pipes[2] ); fclose( $pipes[2] );
	$rtn    = proc_close( $proc );
	return array(
		'stdout' => $stdout,
		'stderr' => $stderr,
		'return' => $rtn
	);
} 
?>
<!DOCTYPE html><html lange="en"><!-- InstanceBegin template="/Templates/cenaDta.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!-- DW6 -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../common/cena.css" rel="stylesheet" type="text/css">
<!-- InstanceBeginEditable name="doctitle" -->
<title>Unit Tests ::Cena-DTA Development</title>
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
  <p><a href="../../index.php" class="headerTitle"><strong>Cena</strong> DTA Developments </a></p>
  <div class="menus">|&nbsp;<a href="../../index.php">Top</a>&nbsp;|&nbsp;<a href="http://wsjp.blogspot.com/" target="_blank"></a></div>
  <p align="left" class="headDesc">for HTML5 applications using local databases.</p>
</div>
<div id="contents"><!-- InstanceBeginEditable name="contents" -->
<h1><span class="bread"><a href="../../index.php">Cena-DTA</a>:: Unit Test</span>Unit Test for cenaPHP</h1>
    <div>
        <h2>test Utils</h2>
        <p>testing utility, validator in Util folder.</p>
        <ul>
            <li><a href="index.php?test=Util_testUtil">Util test</a></li>
            <li><a href="index.php?test=Util_testValidator">Validator test</a></li>
            <li><a href="index.php?test=Util_testVerify">Verify test</a></li>
        </ul>
        <h2>test Htmls</h2>
        <ul>
            <li>[<a href="index.php?test=Html_testSuite"><strong>Html{Prop|Tags|Form}.php</strong></a>] <br>
                [<a href="index.php?test=Html_testProp">Prop</a>]
                [<a href="index.php?test=Html_testTags">Tags</a>]
                [<a href="index.php?test=Html_testForm">Form</a>] </li>
            <li><a href="index.php?test=Html_testControl">Control</a></li>
        </ul>
    </div>
<p>
  <?php if( isset( $test ) ): ?>
</p>
<h1><a href="index.php">UnitTest</a>:: results: <?php echo $test; ?></h1>
<p><?php echo nl2br( $msg ); ?></p>
<p><?php echo nl2br( $result ); ?></p>
<?php endif; ?>
<!-- InstanceEndEditable --></div>
<div id="footer">
  <table  border="0" align="center" cellpadding="0" cellspacing="0">
    <tr valign="top">
      <td valign="bottom" nowrap class="footDesc"><p>Cena developed 
        by <a href="../../../index.php"><strong>WorkSpot.JP</strong></a>&nbsp;
      </p>
      </td>
      <td width="6">&nbsp;</td>
      <td width="100"><a href="../../../serv/index.php"><img src="../../common/img/bar_ser.gif" width="100" height="30" border="0" alt="Service（業務内容） （写真：デビルズタワー国定公園、アメリカ）"></a></td>
      <td width="100"><a href="../../../expc/index.php"><img src="../../common/img/bar_exp.gif" width="100" height="30" border="0" alt="Experience（実績･経験）　（写真：紀伊半島にある筆薮滝）"></a></td>
      <td width="100"><a href="../../../prof/index.php"><img src="../../common/img/bar_pro.gif" width="100" height="30" border="0" alt="Profile（経歴）　（写真：バッドランド国立公園、アメリカ）"></a></td>
      <td width="100"><a href="../../../tech/index.php"><img src="../../common/img/bar_tec.gif" width="100" height="30" border="0" alt="Technology（技術）　（写真：東京フォーラム）"></a></td>
    </tr>
    <tr valign="top">
      <td colspan="6" align="center" nowrap><span class="copyright">copyright (c) 2010-<?php echo date('Y'); ?> WorkSpot.JP</span></td>
    </tr>
  </table>
</div>
</body>
<!-- InstanceEnd --></html>
