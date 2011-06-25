<?php
/* class.ext_func.php
 * charset: utf-8（雀の往来）
*/
// expected char encoding
define('APP_ENCODING', mb_internal_encoding());

// +--------------------------------------------------------------+
function addslashes_safe( $value )
{
	if( have_value( $value ) ) $value = addslashes( $value );
	return $value;
}

// +--------------------------------------------------------------+
function file_stat( $file_name )
{
	$stat = FALSE;
	if( file_exists( $file_name ) ) {
		$stat = lstat( $file_name );
		$stat[ 'acc_time' ] = date( 'Y-m-d H:i:s', $stat[ 'atime' ] );
		$stat[ 'mod_time' ] = date( 'Y-m-d H:i:s', $stat[ 'mtime' ] );
		$stat[ 'chg_time' ] = date( 'Y-m-d H:i:s', $stat[ 'ctime' ] );
		$stat[ 'size_kb' ]  = sprintf( '%0.0f', $stat[ 'size' ] / 1024 );
		$stat[ 'size_mb' ]  = sprintf( '%0.2f', $stat[ 'size' ] / 1024 / 1024 );
		$stat[ 'size_gb' ]  = sprintf( '%0.2f', $stat[ 'size' ] / 1024 / 1024 / 1024 );
	}
	return $stat;
}

// +--------------------------------------------------------------+
function sprintf_utf8()
{
	$args = func_get_args();
	$use_encode = 'EUC-JPwin';
	for ($i = 1; $i < count($args); $i++) {
		$args [$i] = iconv( 'UTF-8', $use_encode, $args [$i] );
	}
	return iconv( $use_encode, 'UTF-8', call_user_func_array('sprintf', $args) );
}

// +--------------------------------------------------------------+
function reload( $arg=NULL ) {
	$url = $_SERVER['PHP_SELF'];
	jump_to_url( $url, $arg );
}

// +--------------------------------------------------------------+
function jump_to_url( $url, $arg=NULL ) {
	$url = htmlspecialchars( $url );
	if( $arg ) {
		if( substr( $arg, 0, 1 ) != '?' ) $arg = '?' . $arg;
		$url .= $arg;
	}
	if( WORDY ) {
	  echo "<a href='{$url}'>{$url}</a><br>";
	}
	else {
	  header( "Location: {$url}" );
	}
	exit;
}

// +--------------------------------------------------------------+
if( !function_exists( 'file_get_contents' )  ) {
	function file_get_contents( $filename ) {
		$fp = fopen( $filename, 'rb' );
		$contents = NULL;
		while( $text = fgets( $fp ) ) {
			$contents .= $text;
		}
		fclose( $fp );
		return $contents;
	}
}
// +--------------------------------------------------------------+
if( !function_exists( 'file_put_contents' )  ) {
	function file_put_contents( $filename, $contents ) {
		$fp = fopen( $filename, 'wb' );
		if( flock( $fp, LOCK_EX ) ) 
		{
			ftruncate( $fp );
			fwrite( $fp, $contents );
			flock( $fp, LOCK_UN );
			fclose( $fp );
			return TRUE;
		}
		return FALSE;
	}
}

// +--------------------------------------------------------------+
function file_convert_encoding( $file_name, $to='UTF-8', $from='SJIS-win' )
{
	$contents  = & file_get_contents( $file_name );
	$contents  = & mb_convert_encoding( $contents, $to, $from );
	return file_put_contents( $file_name, $contents );
}

// +--------------------------------------------------------------+
function user_message( $err, $msg, $opt=array() )
{
	return display_message( $msg, $err, $opt );
}

// +--------------------------------------------------------------+
function display_message( $msg, $err=0, $opt=array() )
{
	if( $err > 0 ) {
		$tbl_color = '#CC3300';
		$tbl_msg   = 'エラーがありました';
	} 
	else {
		$tbl_color = '#6699CC';
		$tbl_msg   = 'メッセージ';
	}
	if( is_array( $opt ) ) extract( $opt );
	if( !$width ) $width = '100%';
?>
<br>
<table class="err_box" width="<?php echo $width; ?>"  border="0" align="center" cellpadding="2" cellspacing="2" bgcolor="<?php echo $tbl_color;?>">
  <tr>
    <td align="center"><?php echo "<font color=white><b>{$tbl_msg}</b></font>\n"; ?></td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF"><?php echo $msg;?></td>
  </tr>
</table>
<br>
<?php
}

class fmt
{
	/* ------------------------------------------------------------ */
	function fmt() {}
	/* ------------------------------------------------------------ */
	function val_type( $val ) {
		$type = "'{$val}'";
		if( $val === FALSE ) {
			$type = 'FALSE';
		}
		else 
		if( is_null( $val ) ) {
			$type = 'NULL';
		}
		else 
		if( !have_value( $val ) ) {
			$type = 'EMPTY STRING';
		}
		else 
		if( !isset( $val ) ) {
			$type = 'NOT SET';
		}
		return $type;
	}
	/* ------------------------------------------------------------ */
	function disp_pswd( $password ) 
	{
		$leng = strlen( $password );
		return str_repeat( '*', $leng );
	}
	/* ------------------------------------------------------------ */
	function currency( $currency, $decimals=0 ) 
	{
		return number_format( $currency, $decimals );
	}
	/* ------------------------------------------------------------ */
	function jpn_yobi( $w )
	{
		$wget = array( '日','月','火','水','木','金','土','日' );
		return $wget[ $w ];
	}
	/* ------------------------------------------------------------ */
	function number( $number, $decimals=2 ) 
	{
		return number_format( $number, $decimals );
	}
	/* ------------------------------------------------------------ */
	function jpn_time( $time )
	{
		list( $time, $min, $sec ) = explode( ':', $time );
		if( have_value( $sec ) ) {
			$ret_val = "{$time}時{$min}分{$sec}秒";
		}
		else 
		if( have_value( $min ) ) {
			$ret_val = "{$time}時{$min}分";
		}
		else 
		if( have_value( $time ) ) {
			$ret_val = "{$time}時";
		}
		else
		{
			$ret_val = "";
		}
		return $ret_val;
	}
	/* ------------------------------------------------------------ */
	function jpn_timespan( $time )
	{
		list( $time, $min, $sec ) = explode( ':', $time );
		if( have_value( $sec ) ) {
			$ret_val = "{$time}時間{$min}分{$sec}秒";
		}
		else 
		if( have_value( $min ) ) {
			$ret_val = "{$time}時間{$min}分";
		}
		else 
		if( have_value( $time ) ) {
			$ret_val = "{$time}時間";
		}
		else
		{
			$ret_val = "";
		}
		return $ret_val;
	}
	/* ------------------------------------------------------------ */
	function jpn_time2( $time )
	{
		list( $time, $min, $sec ) = explode( ':', $time );
		$ret_val = "{$time}時{$min}分";
		return $ret_val;
	}
	/* ------------------------------------------------------------ */
	function parse_datetime( $str )
	{
		$date = self::std_date( $str, $e_part );
		$time = self::std_time( $str );
		
		return trim( "$date $time" );
	}
	/* ------------------------------------------------------------ */
	function std_time( $time )
	{
		if( preg_match( '/([0-9]{1,2})[:]([0-9]{1,2})[:]([0-9]{1,2})/', $time, $regs ) ) {
			list( $h, $m, $s ) = array( $regs[1], $regs[2], $regs[3] );
			$t_part = sprintf( "%4d:%02d:%02d", $h, $m, $s );
		}
		else 
		if( preg_match( '/([0-9]{1,2})時([0-9]{1,2})分([0-9]{1,2})秒/', $time, $regs ) ) {
			list( $h, $m, $s ) = array( $regs[1], $regs[2], $regs[3] );
			$t_part = sprintf( "%4d:%02d:%02d", $h, $m, $s );
		}
		return $t_part;
	}
	/* ------------------------------------------------------------ */
	function std_date( $date, &$e_part )
	{
		if( preg_match( '/([0-9]{4})[-.\/]([0-9]{1,2})[-.\/]([0-9]{1,2})(.*)/', $date, $regs ) ) {
			list( $year, $month, $day ) = array( $regs[1], $regs[2], $regs[3] );
			$d_part = sprintf( "%4d-%02d-%02d", $year, $month, $day );
			$e_part = $regs[4];
		}
		else 
		if( preg_match( '/([0-9]{4})年([0-9]{1,2})月([0-9]{1,2})日(.*)/', $date, $regs ) ) {
			$e_part = NULL;
			list( $year, $month, $day ) = array( $regs[1], $regs[2], $regs[3] );
			$d_part = sprintf( "%4d-%02d-%02d", $year, $month, $day );
			$e_part = $regs[4];
		}
		else 
		if( ereg( '([0-9]{4})([012][0-9])([012][0-9])(.*)', $date, $regs ) ) {
			list( $year, $month, $day ) = array( $regs[1], $regs[2], $regs[3] );
			$d_part = sprintf( "%4d-%02d-%02d", $year, $month, $day );
			$e_part = $regs[4];
		}
		else 
		if( ( $utime = strtotime( $date ) ) !== FALSE ) {
			$d_part = date( 'Y-m-d', $utime );
		}
		return $d_part;
	}
	/* ------------------------------------------------------------ */
	function jpn_date2( $date )
	{
		// $date in 'YYYY-mm-dd'
		$date = fmt::std_date( $date, $ext );
		list( $year, $month, $day ) = explode( '-', $date );
		$month = (int) $month;
		$day   = (int) $day;
		$ret_val = "{$year} 年 {$month} 月 {$day} 日";
		
		return $ret_val . $ext;
	}
	/* ------------------------------------------------------------ */
	function jpn_small_date( $date )
	{
		list( $year, $month, $day ) = explode( '-', $date );
		$date = sprintf( '%02d/%d/%d', substr( $year, 2 ), $month, $day );
		return $date;
	}
	/* ------------------------------------------------------------ */
	function slash_date( $date )
	{
		$date = str_replace( '-', '/', $date );
		return $date;
	}
	/* ------------------------------------------------------------ */
	function jpn_wk( $date )
	{
		$date = fmt::std_date( $date, $ext );
		$weeks = array( '日', '月', '火', '水', '木', '金', '土' );
		$wk    = date( 'w', cal_date::makeTime( $date ) );
		
		return $weeks[ $wk ];
	}
	/* ------------------------------------------------------------ */
	function jpn_date_wk( $date )
	{
		$date = fmt::jpn_date( $date ) . '（' . fmt::jpn_wk( $date ) . '）';
		return $date;
	}
	/* ------------------------------------------------------------ */
	function jpn_date( $date )
	{
		// $date in 'YYYY-mm-dd'
		$date = fmt::std_date( $date, $ext );
		list( $year, $month, $day ) = explode( '-', $date );
		if( $year ) {
			$ret_val = "{$year}年";
			if( $month ) {
				$month    = (int) $month;
				$ret_val .= "{$month}月";
				if( $day ) {
					$day      = (int) $day;
					$ret_val .= "{$day}日";
				}
			}
		}
		// $ret_val = "{$year}年{$month}月{$day}日";
		
		return $ret_val . $ext;
	}
	/* ------------------------------------------------------------ */
	function hnendo( $year )
	{
		if( is_numeric( $year ) && $year > 1988 ) {
			$heisei = $year - 1988;
			return "平成{$heisei}年度";
		}
		return $year;
	}
	/* ------------------------------------------------------------ */
	function heisei( $year )
	{
		if( is_numeric( $year ) && $year > 1988 ) {
			$heisei = $year - 1988;
			return "平成{$heisei}年";
		}
		return $year;
	}
	/* ------------------------------------------------------------ */
	function h( $year )
	{
		if( is_numeric( $year ) && $year > 1988 ) {
			$heisei = $year - 1988;
			return "H{$heisei}";
		}
		return $year;
	}
	/* ------------------------------------------------------------ */
	function get_nengo_date( $date, $dbar='-' ) 
	{
		$nengo = array(
			'H' => 1988, 
			'S' => 1925,
			'T' => 1911,
			'M' => 1867
		);
		$jpn  = substr( $date, 0, 1 );
		$year = substr( $date, 1, 2 );
		$mon  = substr( $date, 3, 2 );
		$day  = substr( $date, 5, 2 );
		
		if( isset( $nengo[ $jpn ] ) ) {
			$year += $nengo[ $jpn ];
			$west_date = "{$year}-{$mon}-{$day}";
		}
		else {
			$west_date = $date;
		}
		if( WORDY ) echo "get_nengo_date( $date, $dbar='-' ) => {$west_date}<br>\n";
		return $west_date;
	}
	/* ------------------------------------------------------------ */
	function jpn_nengou( $date )
	{
		// $date in 'YYYY-mm-dd'
		$date = fmt::std_date( $date, $ext );
		$jpn = array( 
			'平成' => '1989-01-08', 
			'昭和' => '1926-12-25', 
			'大正' => '1912-07-30', 
			'明治' => '1868-01-25' );
		$ret_val = $date;
		list( $year, $month, $day ) = explode( '-', $date );
		foreach( $jpn as $gou => $start )
		{
			if( WORDY > 5 ) echo "{$gou}:$start <br>";
			if( $date >= $start ) {
				$month   = (int) $month;
				$day     = (int) $day;
				$year    = $year - substr( $start, 0, 4 ) + 1;
				$ret_val = "{$gou}{$year}年{$month}月{$day}日";
				if( WORDY > 5 ) echo '==> ' . $ret_val;
				break;
			}
		}
		return $ret_val . $ext;
	}
	/* ------------------------------------------------------------ */
	/**
	 * pad UTF-8 string with *correct* width (1/2 byte).
	 * specify minus width to align right.  
	 */
	function mb_pad( $width, $str, $pad=' ' )
	{
		$strwidth = mb_strwidth( $str );
		$align    = 'R';
		$padding  = '';
		if( $width < 0 ) {
			$align = 'L';
			$width = - $width;
		}
		if( $width - $strwidth > 0 ) {
			$padding = str_repeat( $pad, $width - $strwidth );
		}
		if( $align == 'L' ) {
			$string = $str . $padding;
		}
		else {
			$string = $padding . $str;
		}
		return $string;
	}
	/* ------------------------------------------------------------ */
}

class cal_date
{
	/* ------------------------------------------------------------ */
	function today() 
	{
		return date( 'Y-m-d' );
	}
	/* ------------------------------------------------------------ */
	function date( $year, $month=1, $day=1 ) 
	{
		$time = cal_date::mkdate( $year, $month, $day );
		if( $year && $month && $day ) 
			return date( 'Y-m-d', $time );
		else FALSE;
	}
	/* ------------------------------------------------------------ */
	function jpnYobi( $w )
	{
		$wget = array( '日','月','火','水','木','金','土','日' );
		return $wget[ $w ];
	}
	/* ------------------------------------------------------------ */
	function std_date( $date, &$e_part )
	{
		return fmt::std_date( $date, $e_part );
	}
	/* ------------------------------------------------------------ */
	function mkdate( $year, $month=1, $day=1 ) 
	{
		return mktime( 0,0,0, $month, $day, $year );
	}
	/* ------------------------------------------------------------ */
	function modDate( $date, $d=0, $m=0, $y=0 ) 
	{
		return cal_date::getDate( $d, $m, $y, $date );
	}
	/* ------------------------------------------------------------ */
	function getDate( $d=0, $m=0, $y=0, $date=NULL ) 
	{
		if( !$date ) $date = cal_date::today();
		
		list( $year, $month, $day ) = split( '-', $date );
		$time = mktime( 0,0,0, $month+$m, $day+$d, $year+$y );
		return date( 'Y-m-d', $time );
	}
	/* ------------------------------------------------------------ */
	function checkDate( $date, $dbar='-' ) 
	{
		list( $year, $month, $day ) = split( $dbar, $date );
		if( !$year || !$month || !$day ) return FALSE;
		if( 1  > (int) $month ) return FALSE;
		if( 12 < (int) $month ) return FALSE;
		
		return checkdate( $month, $day, $year );
	}
	/* ------------------------------------------------------------ */
	function makeTime( $date ) 
	{
		list( $year, $month, $day ) = split( '-', $date );
		return mktime( 0,0,0, $month, $day, $year );
	}
	/* ------------------------------------------------------------ */
	function countDates( $date1, $date2 ) 
	{
		$utime1 = cal_date::makeTime( $date1 );
		$utime2 = cal_date::makeTime( $date2 );
		$udiff  = $utime2 - $utime1;
		$udays  = $udiff / ( 60 * 60 * 24 );
		if( WORDY > 3 ) echo "<i>cal_date::countDates( $date1, $date2 ) => {$udays} days</i><br>\n";
		return $udays;
	}
	/* ------------------------------------------------------------ */
	function sortDate1_to_2( &$date1, &$date2 ) 
	{
		if( $date1 > $date2 ) {
			$temp_date = $date1;
			$date1     = $date2;
			$date2     = $temp_date;
			return FALSE;
		}
		return TRUE;
	}
	/* ------------------------------------------------------------ */
	function firstDateOfMonth( $year, $month ) 
	{
		$date  = cal_date::date( $year, $month, 1 );
		if( WORDY > 3 ) echo "<i>cal_date::firstDateOfMonth( $year, $month ) => {$date} </i><br>\n";
		return $date;
	}
	/* ------------------------------------------------------------ */
	function lastDateOfMonth( $year, $month ) 
	{
		$time = mktime( 0,0,0, $month+1, 0, $year );
		$date = date( 'Y-m-d', $time );
		if( WORDY > 3 ) echo "<i>cal_date::lastDateOfMonth( $year, $month ) => {$date} </i><br>\n";
		return $date;
	}
	/* ------------------------------------------------------------ */
	function countAge( $bday, $today=NULL ) 
	{
		// $bday is birthday in "Y-m-d" format.
		// $today is current date in "Y-m-d" format.
		// returns age.
		if( is_null( $today ) ) $today = date( 'Y-m-d' );
		$b_year = substr( $bday,  0, 4 );
		$t_year = substr( $today, 0, 4 );
		$age    = $t_year - $b_year - 1; 
		
		$b_md   = substr( $bday,  6, 5 );
		$t_md   = substr( $today, 6, 5 );
		if( $t_md >= $b_md ) $age ++;
		
		if( WORDY ) echo "countAge( $bday, $today ) => age={$age}";
		return $age;
	}
	// +----------------------------------------------------------------+
	function get_week_day( $week_day, $to_day=NULL )
	{
		// 日付の含む週の曜日を返す。
		//  $week_day:  0:Sunday, 1:Monday, 5:Friday, 6:Saturday
		$wordy_val = 5;
		
		if( !$to_day ) $to_day = date( 'Y-m-d' );
		list( $y, $m, $d ) = explode( '-', $to_day );
		
		$wk_day = date( 'w', mktime( 0,0,0, $m, $d, $y ) );
		if( WORDY > $wordy_val ) echo "y=$y, m=$m, d=$d, w=$wk_day<br>\n";
		$add_to_date = $week_day - $wk_day;
		if( $add_to_date < 0 ) $add_to_date += 7;
		
		$get_week_day = date( 'Y-m-d', mktime( 0,0,0, $m, $d+$add_to_date, $y ) );
		if( WORDY > $wordy_val ) echo "get_week_day( $week_day, $to_day ) => $get_week_day<br>\n";
		return $get_week_day;
	}
	/* ------------------------------------------------------------ */
}

/* ------------------------------------------------------------ */
function have_value( $var )
{
	if( is_array( $var ) ) { 
		return( count( $var ) ); 
	}
	else
	if( is_object( $var ) ) {
		return TRUE;
	}
	else
	if( "$var" == "" ) { 
		return FALSE; 
	}
	else { 
		return TRUE; 
	}
}

/* ------------------------------------------------------------ */
function pivot_row_column( & $all_data )
{
	reset( $all_data );
	$result_array = array();
	if( is_array( $all_data ) ) 
	{
		while( list( $key, $sub_data ) = each( $all_data ) ) 
		{
			if( is_array( $sub_data ) ) {
				while( list( $row, $data ) = each( $sub_data ) ) {
					$result_array[ $row ][ $key ] = $data;
				}
			}
			else {
				$result_array[ $key ] = $sub_data;
			}
		}
	}
	$all_data = $result_array;
}

/* ------------------------------------------------------------ */
function reverse_value_and_key( $input )
{
	$result = NULL;
	if( is_array( $input) && !empty( $input ) ) {
		foreach( $input as $key => $val ) {
			$result[ $val ] = $key;
		}
	}
	return $result;
}

/* ------------------------------------------------------------ */
class ext_arr
{
	/* ----------------------------------------------------- */
	function only_diff( &$data1, &$data2 )
	{
		$diff = array();
		if( !empty( $data1 ) ) 
		foreach( $data1 as $key => $val ) {
			if( isset( $data2[ $key ] ) && 
			    $val == $data2[ $key ] ) {
			}
			else {
				$diff[ $key ] = $val;
			}
		}
		return $diff;
	}
	/* ----------------------------------------------------- */
	function flip( &$input )
	{
		$result = array();
		if( !is_array( $input ) ) return $result;
		if( empty(     $input ) ) return $result;
		reset( $input );
		
		foreach( $input as $row => $value ) {
			$result[ $value ] = $row;
		}
		return $result;
	}
	/* ----------------------------------------------------- */
	function extract( &$input, $idx )
	{
		$result = array();
		if( !is_array( $input ) ) return $result;
		if( empty(     $input ) ) return $result;
		reset( $input );
		
		foreach( $input as $row => $data ) 
		{
			if( isset( $data[ $idx ] ) ) {
				$result[ $row ] = $data[ $idx ];
			}
		}
		return $result;
	}
	/* ----------------------------------------------------- */
	function flip_idx( &$input, $idx )
	{
		$result = array();
		if( !is_array( $input ) ) return $result;
		if( empty(     $input ) ) return $result;
		reset( $input );
		
		foreach( $input as $row => $data ) 
		{
			if( isset( $data[$idx] ) ) {
				$key = $data[$idx];
				$result[ $key ] = $data;
			}
			else {
				$result[ $row ] = $data;
			}
		}
		return $result;
	}
	/* ----------------------------------------------------- */
	function pivot( &$input )
	{
		$result = array();
		if( !is_array( $input ) ) return $result;
		if( empty(     $input ) ) return $result;
		reset( $input );
		
		while( list( $key, $data ) = each( $input ) ) 
		{
			if( is_array( $data ) ) {
				while( list( $row, $value ) = each( $data ) ) {
					$result[ $row ][ $key ] = $value;
				}
			}
			else {
				$result[ $key ] = $data;
			}
		}
		return $result;
	}
	/* ----------------------------------------------------- */
	function head( $input, $head, $head_orig=NULL, $term='_' )
	{
		$result = array();
		if( !is_array( $input ) ) return $result;
		if( empty(     $input ) ) return $result;
		reset( $input );
		
		foreach( $input as $key => $value ) {
			$head_pos = strpos( $key, $term, 1 );
			if( $head_pos === FALSE ) {
				$new_key = $head . $curr_body;
			}
			else {
				$curr_head = substr( $key, 0, $head_pos );
				$curr_body = substr( $key, $head_pos );
				if( $head_orig ) {
					if( $head_orig == $curr_head ) {
						$new_key = $head . $curr_body;
					}
					else {
						$new_key = $key;
					}
				}
				else {
					$new_key = $head . $curr_body;
				}
				if( WORDY ) echo "head( $input, $head, $head_orig, $term ): {$key} => {$new_key} = {$value}<br>";
				$result[ $new_key ] = $value;
			}
		}
		return $result;
	}
	/* ----------------------------------------------------- */
}

/* ------------------------------------------------------------ */
function pivot_input( $input, $idx )
{
	// pick one column from a two dimensional array, and 
	// create an array with the column. 
	if( WORDY > 3 ) echo "pivot_input( $input, $idx )<br>\n";
	if( WORDY > 3 ) print_r( $input );
	$result = array();
	if( is_array( $input ) && !empty( $input ) ) 
	{
		foreach( $input as $key=>$val ) {
			if( isset( $input[$key][$idx] ) ) $result[$key] = $input[$key][$idx];
		}
	}
	return $result;
}

/* ------------------------------------------------------------ */
function html_safe( $value ) {
	return recurse_htmlspecialchars( $value );
}

/* ------------------------------------------------------------ */
function recurse_htmlspecialchars( $value )
{
    if( is_array( $value ) ) {
        reset( $value );
        while( list( $key, $val ) = each( $value ) ) {
            $value[$key] = recurse_htmlspecialchars( $val );
        }
    }
    else {
        $value = htmlspecialchars( $value );
    }
    return $value;
}

/* ------------------------------------------------------------ */
function recurse_check_strlen( $value, $length )
{
    if( is_array( $value ) ) {
        reset( $value );
        while( list( $key, $val ) = each( $value ) ) {
            if( !recurse_check_strlen( $val, $length ) ) return FALSE;
        }
    }
    else {
        if( WORDY > 5 ) echo " -> recurse_check_strlen( $value, $length )<br>\n";
        if( $length == 0 && !strlen( $value ) ) return FALSE; 
        if( $length  > 0 && strlen( $value ) != $length ) return FALSE;
    }
    return TRUE;
}

/* ------------------------------------------------------------ */
function recurse_ereg( $value, $ereg_expr, $mb=FALSE )
{
    if( is_array( $value ) ) {
        reset( $value );
        while( list( $key, $val ) = each( $value ) ) {
            if( !recurse_ereg( $val, $ereg_expr ) ) return FALSE;
        }
    }
    elseif( empty( $value ) ) return TRUE;
    else {
        if( WORDY > 5 ) echo " -> recurse_ereg( $value, $ereg_expr )<br>\n";
        if( $mb ) return mb_ereg( "^{$ereg_expr}$", $value );   
        else      return ereg( "^{$ereg_expr}$", $value );  
    }
    
    return TRUE; 
}

/* ------------------------------------------------------------ */
function recurse_mb_convert_kana( $value, $mb_convert_str )
{
    if( is_array( $value ) ) {
        reset( $value );
        while( list( $key, $val ) = each( $value ) ) {
            $value[$key] = recurse_mb_convert_kana( $val, $mb_convert_str );
        }
    }
    else {
		if( defined( 'PHP_CHAR_SET' ) ) {
			$from_charset = PHP_CHAR_SET;
		}
		else {
			$from_charset = 'UTF-8';
		}
        if( WORDY > 3 ) echo " -> recurse_mb_convert_kana( $value, $mb_convert_str ), $from_charset<br>\n";
        $value = mb_convert_kana( $value, $mb_convert_str, $from_charset );  
    }
    
    return $value; 
}

/* ------------------------------------------------------------ */
function recurse_mb_convert_encoding( $value, $to, $from )
{
    if( is_array( $value ) ) {
        reset( $value );
        while( list( $key, $val ) = each( $value ) ) {
            $value[$key] = recurse_mb_convert_encoding( $val, $to, $from );
        }
    }
    else {
        if( WORDY > 5 ) echo " -> recurse_mb_convert_encoding( $val, $to, $from )<br>\n";
        $value = mb_convert_encoding( $value, $to, $from );  
    }
    
    return $value; 
}

/* ------------------------------------------------------------ */
function recurse_mb_convert_number( $value )
{
    if( is_array( $value ) ) {
        reset( $value );
        while( list( $key, $val ) = each( $value ) ) {
            $value[$key] = recurse_mb_convert_number( $val );
        }
    }
    else {
        if( WORDY > 5 ) echo " -> recurse_mb_convert_number( $value )<br>\n";
		if( defined( PHP_CHAR_SET ) ) {
			$from = PHP_CHAR_SET;
		}
		else {
			$from = 'UTF-8';
		}
        $value = mb_convert_kana( $value, 'sa', $from );
		$value = str_replace( 'ー', '-', $value );
    }
    
    return $value; 
}

/* ------------------------------------------------------------ */
function recurse_evaluation( $value, $func )
{
    if( is_array( $value ) ) {
        reset( $value );
        while( list( $key, $val ) = each( $value ) ) {
            if( !recurse_evaluation( $val, $func ) ) return FALSE;
        }
    }
    else {
        if( WORDY > 5 ) echo " -> recurse_evaluation( $value, $func )<br>\n";
        return  $func( $value );    
    }
    
    return TRUE; 
}

/* ------------------------------------------------------------ */
function recurse_trim( &$value )
{
    if( is_array( $value ) ) {
        reset( $value );
        while( list( $key, $val ) = each( $value ) ) {
			if( recurse_trim( $val ) ) {
            	$value[$key] = $val;
			}
        }
    }
    else {
        if( WORDY > 5 ) echo " -> recurse_trim( '$value' )<br>\n";
        $value = trim( $value );
		return TRUE;
    }
    
    return TRUE; 
}

/* ------------------------------------------------------------ */
function recurse_del_null( & $value )
{
    if ( is_array( $value ) ) {
        return array_map( 'recurse_del_null', $value );
    }
    $value = str_replace( "\0", "", $value );
}

/* ------------------------------------------------------------ */
function sql_safe( $value )
{
	return recurse_func( $value, 'addslashes' );
}

/* ------------------------------------------------------------ */
function recurse_func( $value, $func )
{
    if( is_array( $value ) ) {
        reset( $value );
        while( list( $key, $val ) = each( $value ) ) {
            $value[$key] = recurse_func( $val, $func );
        }
    }
    else {
        if( WORDY > 5 ) echo " -> {$func}( {$value} )<br>\n";
        return $func( $value );
    }
    
    return $value; 
}

/* ------------------------------------------------------------ */
function recurse_check_date( $dbar, $date )
{
    if( is_array( $date ) ) {
        reset( $date );
        while( list( $key, $val ) = each( $date ) ) {
            if( !recurse_check_date( $dbar, $val ) ) return FALSE;
        }
    }
    else {
        if( WORDY > 5 ) echo " -> {$func}( {$value} )<br>\n";
		if( have_value( $date ) ) {
			list( $year, $month, $day ) = explode( $dbar, $date );
			return @checkdate( $month, $day, $year );
		}
    }
    
    return TRUE; 
}



    /////////////////////////////////////////////////////////////////////////////
    // function str_split(str my_string,int my_width)
    // my_string: 整形したい文字列
    // my_width:  文字幅
    // 文字列を指定した文字幅で改行して整形します。文字列中に英単語が含まれ、かつ
    // 英単語のを分割して改行されるような場合は、その単語を次の行へまわします。
    // 入力フォームのデータをメール本文に挿入する際に使うと便利だと思います。
    //
    // [使い方]
    // $test_str ="適当な文字列です。この文字列をメールで送信するのに、うまく整形";
    // $test_str.="して送るときにこの関数を使用します。\n";
    // $test_str.="Use this function when you send an e-mail with mail-body that ";
    // $test_str.=" is inserted by web form.\n";
    // $test_str.="Is this English OK ?";
    // $mail_body=str_split($test_str,72);
    //
    // Copyright (c) 2003 KITAO Kaoru (CubeWorks Inc.) All rights reserved.
    // taken from: http://www.cubeworks.co.jp/php/script/str_split.txt
    /////////////////////////////////////////////////////////////////////////////
    
    // 与えられた文字列を指定文字幅ごとに改行するための関数
    function eucjp_str_split($str_data,$line_width){
        // 指定文字幅よりも文字数の多い単語が存在する場合はfalseを返す。
        if(ereg("[a-zA-Z0-9]{" . $line_width . ",}",$str_data)){
            return "指定文字幅よりも文字数の多い単語が含まれています。";
        }
        // 与えられた文字列を改行をセパレータとして分割し、配列に格納する
        $temp_array=explode("\n",$str_data);
        // 戻り値の準備（空文字列で初期化）
        $mystr="";
        // 配列に格納された文字列を、一つずつ（一行ずつ）処理する
        // 処理のためにline_split関数を呼び出す
        for($i=0;$i<count($temp_array);$i++){
            $mystr.=eucjp_line_split($temp_array[$i],$line_width);
        }
        return $mystr;
    }

    // 一行分のデータを指定文字幅ごとに改行するための関数
    function eucjp_line_split($line_data,$line_width){
        // 指定文字数ごとにデータを格納するための配列を準備（初期化）
        $this_array=array();
        // 行数を示す変数の準備（テンポラリで使用）
        $i=0;
        
        // 与えられた文字列の先頭から指定文字数分を取得して処理し、もとの文字列がなくなったら終了
        while($line_data<>""){
            // 与えられた文字列から指定文字数分だけ文字列を取得し、元の文字列から取得した文字列を削除
            $this_array[$i]=mb_strcut($line_data,0,$line_width);
            $line_data=mb_substr($line_data,mb_strlen($this_array[$i]));
            
            // 英単語が含まれており、英単語の真中で行が分割された場合に、当該単語を次の行に回す処理
            if(mb_strwidth($this_array[$i])==$line_width){
                if(mb_ereg("[0-9a-zA-Z]",mb_strcut($this_array[$i],mb_strwidth($this_array[$i])-1)) & mb_strwidth(mb_strcut($line_data,0,1))==1){
                    for($j=mb_strwidth($this_array[$i]);$j>0;$j--){
                        $temp_str=mb_strcut($this_array[$i],$j);
                        if( mb_strwidth($temp_str)<>mb_strlen($temp_str) | mb_ereg("[ \.\,]",$temp_str)){
                            $cut_str=mb_strcut($this_array[$i],$j+1);
                            break;
                        }
                    }
                    $this_array[$i]=mb_substr($this_array[$i],0,mb_strrpos($this_array[$i],$cut_str));
                    $line_data=$cut_str . $line_data;
                }
            }
            $i++;
        }
    
        $mystr=implode("\n",$this_array) . "\n";
        return $mystr;
    }



/* ------------------------------------------------------------ */
function mail_jp( $to, $subject, $body, $options=array() )
{
    if( WORDY ) { 
		$char = PHP_CHAR_SET;
		$leng = strlen( $body );
		echo "<br><br><b>mail_jp( $to, $subject, body={$leng} chars ), PHP_CHAR={$char}</b><br>\n"; 
	}
    $op = array();
	if( !isset( $options[ 'mailer' ] ) ) { $options[ 'mailer' ] = 'PHP/'; }
    while( list( $key, $val ) = each( $options ) )
    {
        $key = strtolower( $key );
        switch( $key )
        {
            case "from"  :	$op[] = "From: "     . $val;	break;
            case "reply" :	$op[] = "Reply-To: " . $val;	break;
            case "bcc"   :	$op[] = "Bcc: "      . $val;	break;
            case "cc"    :	$op[] = "Cc: "       . $val;	break;
            case "mailer":	$op[] = "X-Mailer: " . $val;	break;
            default:		$op[] = "{$key}: "   . $val;	break;
        }
    }
    
    if( !empty( $op ) ) $mail_cc = implode( "\n", $op ); 
    else                $mail_cc = "";
    
    if( WORDY ) { 
		echo "mail_to = $to<br>\n"; 
		echo "mail_cc = $mail_cc<br>\n"; 
		echo "subject = $subject<br>\n"; 
		echo "body = <br><pre>", $body, "</pre><br>\n";
	}
    
	if( ENV_CURRENT == ENV_DEVE_WIN32 ) return TRUE;
    if( extension_loaded( 'mbstring' ) ) 
	{
        mb_language( "ja" );
		// 内部文字エンコードを設定する
		mb_internal_encoding( "UTF-8" );
		// 改行問題
		$body    = str_replace( "\r\n", "\n", $body );
        $body    = mb_convert_encoding( $body,    "JIS", 'UTF-8' );
        //$subject = mb_convert_encoding( $subject, "JIS", 'UTF-8' );
        $success = mb_send_mail( $to, $subject, $body, $mail_cc );
    }
    else // in case mb_ functions are not present... 
    {
        // do not know if this works...
		if( defined( PHP_CHAR_SET ) ) {
			$from_charset = PHP_CHAR_SET;
		}
		else {
			$from_charset = 'UTF-8';
		}
        $subject = "=?ISO-2022-JP?B?" . base64_encode( 
            mb_convert_encoding( $subject, "JIS", $from_charset ) ) . "?=";
        $mail_cc .= "";
        $body    = mb_convert_encoding( $body, "JIS", $from_charset );
        $success = mail( $to, $subject, $body, $mail_cc );
    }
    return $success;
}


/* ------------------------------------------------------------ */
function WORDY_header( $header, $msg )
{
	if( WORDY ) {
		echo "<br><b>{$header}{$msg}</b><br><br>";
	}
}


/* ------------------------------------------------------------ */
function wordy_depth2_table( $data, $title=NULL )
{
	echo "<font color=orange>Please use wordy_table (wordy_depth2_table obsolete!)</font>";
	wordy_table( $data, $title );
}
/* ------------------------------------------------------------ */
function wt( $data, $title=NULL )
{
	return wordy_table( $data, $title );
}
/* ------------------------------------------------------------ */
function wtc( $data, $title=NULL )
{
	// wordy-table-compact.
	ob_start();
	wordy_table( $data, $title, TRUE );
	$wt = ob_get_contents();
	ob_end_clean();
	echo str_replace( array( "\n", "\r" ), '', $wt );
}
/* ------------------------------------------------------------ */
function get_obj_info( $obj )
{
	if( !is_object( $obj ) ) return gettype( $obj );
	if( method_exists( $obj, '__toString' ) ) return $obj->__toString();
	return get_class( $obj );
}
function wordy_disp( $text ) {
	if( have_value( $text ) ) {
		return $text;
	}
	else {
		return "<font color=blue>" . fmt::val_type( $data ) . "</font>";
	}
}
/* ------------------------------------------------------------ */
function wordy_table( $data, $title=NULL, $compact=FALSE )
{
	if( is_object  ( $data ) ) { // it is an object!!
		$data = get_object_vars( $data );
	}
	if( $title ) echo "<br><table bgcolor='#C0C0C0' border='0'><tr><td><B>&nbsp;&nbsp;{$title}&nbsp;&nbsp;</b></td></tr></table>\n";
	echo "<table bgcolor='#C0C0C0' border='0' cellpadding='2' cellspacing='2' >\n";
	echo "<tr>\n";
	if( !is_array( $data ) ) {
		echo "  <td bgcolor='#FFFFFF'>" . wordy_disp( $data ) . "</td>\n";
	}
	if( is_array( $data ) && !empty( $data ) )
	foreach( $data as $key => $sub_data ) {
		echo "<td align='center' bgcolor='#E0E0E0'><strong>{$key}</strong></td>\n";
	}
	echo "</tr>\n";
	echo "<tr bgcolor='#FFFFFF' valign='top'>\n";
	if( is_array( $data ) && !empty( $data ) )
	foreach( $data as $sub_data ) 
	{
		if( !is_array( $sub_data ) ) 
		{
			if( is_object( $sub_data ) ) {
				echo "<td align='left' bgcolor='#FFFFFF' valign='top'>OBJ:" . 
					get_obj_info( $sub_data ) . "<br><pre>";
				$obj_vars = print_r( $sub_data, TRUE );
				if( $compact ) $obj_vars = nl2br( $obj_vars );
				echo $obj_vars, "</pre></td>";
			}
			else {
				if( !have_value( $sub_data ) ) {
					$entry = '<font color=blue>' . fmt::val_type( $sub_data ) .'</font>';
				}
				else {
					$entry = $sub_data;
				}
				echo "<td align='center' bgcolor='#FFFFFF' valign='top'>{$entry}</td>";
			}
		}
		else 
		{
			echo "<td><table bgcolor='#D8D8D8' border='0' cellpadding='1' cellspacing='1' >\n";
			if( is_array( $sub_data ) && !empty( $sub_data ) )
			foreach( $sub_data as $key => $entry ) 
			{
				echo "<tr valign='top'><td align='right' bgcolor='#F0F0F0'>{$key}</td><td bgcolor='#FFFFFF'>";
				if( is_object( $entry ) ) {
					echo '<font color=blue>Object:' .get_obj_info( $entry ) . '</font>';
				}
				else 
				if( !is_array( $entry ) ) {
					if( !have_value( $entry ) ) {
						echo '<font color=blue>' . fmt::val_type( $entry ) .'</font>';
					}
					else {
						echo $entry;
					}
				}
				else {
					echo "<pre>";
					$obj_vars = print_r( $entry, TRUE );
					if( $compact ) $obj_vars = nl2br( $obj_vars );
					echo $obj_vars;
					echo "</pre>";
				}
				echo "</td></tr>\n";
			}
			echo "</table></td>\n";
		}
	}
	echo "</tr>\n";
	echo "</table>\n";
}







?>