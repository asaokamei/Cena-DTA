<?php
// static class err_box
// for managing errors, exceptions, and messages.

set_exception_handler( array( 'msg_box', 'final_catch' ) );

class msg_box
{
	const  MESSAGE     = 0;
	const  WARNING     = 1;
	const  ERROR       = 2;
	const  CRITICAL    = 3;
	const  FATAL       = 4;
	static $err_num    = 0;       // number of errros
	static $err_level  = 0;       // current error level
	static $message    = array(); // stores only the initial msg
	static $all_msg    = array(); // stores all err/msgs
	static $exception  = NULL;
	// +--------------------------------------------------------------- +
	static function addMsg( $level, $msg, $e=NULL ) 
	{
		self::$all_msg[] = array( $level, $msg, $e );
		if( self::$err_level == self::MESSAGE && $level == self::MESSAGE ) {
			self::$message[] = $msg;
		}
		else
		if( $level > self::$err_level ) {
			self::$message[] = $msg;
			self::$err_level = $level;
			self::$exception = $e;
			// self::$err_num ++;
		}
	}
	// +--------------------------------------------------------------- +
	static function isError() {
		if( self::$err_level >= self::ERROR ) return TRUE;
		return FALSE;
	}
	// +--------------------------------------------------------------- +
	static function noError() {
		return !self::isError();
	}
	// +--------------------------------------------------------------- +
	static function message( $msg, $e=NULL ) {
		self::addMsg( self::MESSAGE, $msg, $e );
	}
	// +--------------------------------------------------------------- +
	static function warning( $msg, $e=NULL ) {
		self::addMsg( self::WARNING, $msg, $e );
	}
	// +--------------------------------------------------------------- +
	static function error( $err_msg, $e=NULL ) {
		self::$err_num ++;
		self::addMsg( self::ERROR, $err_msg, $e );
	}
	// +--------------------------------------------------------------- +
	static function critical( $err_msg, $e=NULL ) {
		self::$err_num ++;
		self::addMsg( self::CRITICAL, $err_msg, $e );
	}
	// +--------------------------------------------------------------- +
	static function fatal( $err_msg, $e=NULL ) {
		self::$err_num ++;
		self::addMsg( self::FATAL, $err_msg, $e );
	}
	// +--------------------------------------------------------------- +
	static function final_catch( $e, $err_msg=NULL ) 
	{
		if( !$err_msg ) $err_msg = 
			'システム上の問題が起こりました。<br><br>' . 
			'もう一度最初からやり直すか、またはリブートしてみてください';
		self::$err_num ++;
		self::addMsg( self::FATAL, $err_msg, $e );
		self::fatal_display();
		exit;
	}
	// +--------------------------------------------------------------- +
	static function getTraceTable() 
	{
		$table = NULL;
		if( !self::$exception ) return $table;
		$trace_all = self::$exception->getTrace();
		$table  = "<table class=\"tblHover\">";
		$table .= "<tr><th>#</th><th>file</th><th>line</th><th>function</th><th>class</th><th>type</th><th>args</th><tr>\n";
		foreach( $trace_all as $idx => $trace ) {
			$args = array();
			if( !empty( $trace['args'] ) )
			foreach( $trace['args'] as $arg ) {
				if( get_class( $arg ) ) {
					$args[] = 'object:' . get_class( $arg );
				}
				else 
				if( is_array( $arg ) ) {
					$args[] = 'array:' . implode( ',', $arg );
				}
				else {
					$args[] = $arg;
				}
			}
			$args = implode( '<br>', $args );
			$table .= "<tr>\n";
			$table .= "  <td>{$idx}</td>\n";
			$table .= "  <td>{$trace{'file'}}</td>\n";
			$table .= "  <td>{$trace{'line'}}</td>\n";
			$table .= "  <td>{$trace{'class'}}</td>\n";
			$table .= "  <td>{$trace{'type'}}</td>\n";
			$table .= "  <td>{$trace{'function'}}</td>\n";
			$table .= "  <td>{$args}</td>\n";
			$table .= "</tr>\n";
		}
		$table .= '</table>';
		return $table;
	}
	// +--------------------------------------------------------------- +
	static function fatal_display() 
	{
		//include( PROJ_FOLDER . '/htdocs/error.php' );
		echo "<strong>Fatal Exception!</strong><br />";
		echo "MESSAGE:「" . self::$exception->getMessage() . "」<br />";
		wt( self::$exception->getTrace(), 
			' in ' . self::$exception->getFile() . 
			' at line #' . self::$exception->getLine() );
		echo '<pre>'; var_dump( self::$exception ); echo '</pre>';
	}
	// +--------------------------------------------------------------- +
	static function getMessage() 
	{
		return implode( '<BR /><BR />', self::$message );
	}
	// +--------------------------------------------------------------- +
	static function display( $opt=NULL ) 
	{
		if( !have_value( self::$message ) ) return;
		
		if( self::$err_num > 0 ) {
			$tbl_class = 'tblErr';
			$tbl_msg   = 'エラーがありました';
		}
		else
		if( self::$err_level > self::MESSAGE ) {
			$tbl_class = 'tblErr';
			$tbl_msg   = '確認してください';
		}
		else {
			$tbl_class = 'tblMsg';
			$tbl_msg   = 'メッセージ';
		}
		if( is_array( $opt ) ) extract( $opt );
		if( !isset( $width ) || !have_value( $width ) ) $width = '90%';
		
		$msg = self::getMessage();
?>
<br>
<table class="<?php echo $tbl_class ?>" width="<?php echo $width; ?>"  border="0" align="center" cellpadding="2" cellspacing="2">
  <tr>
    <th align="center"><?php echo "<font color=white><b>{$tbl_msg}</b></font>\n"; ?></th>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF">
	  <?php echo $msg;?>
	  <?php if( WORDY ) wordy_table( self::$all_msg, 'all messages' ); ?>
	</td>
  </tr>
</table>
<br>
<?php
	}
	// +--------------------------------------------------------------- +
}


?>