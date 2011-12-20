<?php
namespace CenaDta\Html;
/**
 *	A simple page controller class. 
 *
 *	@copyright     Copyright 2010-2011, Asao Kamei
 *	@link          http://www.workspot.jp/cena/
 *	@license       GPLv2
 */
require_once( dirname( __FILE__ ) . '/../class/class.pgg_JPN.php' );

class Control
{
	const  PGG_ID = 'Html_Control-pgg_id';
	
	// ---------------------------------
	/**	list of methods for action.
	 */
	var $methods = array();
	
	// ---------------------------------
	/** variables for controlling action.
	 */
	var $curr_act      = FALSE;      // current action
	var $next_act      = FALSE;      // what's next? 
	var $act_name      = 'act';      // name of control variable.
	
	// ---------------------------------
	/**	default method.
	 */
	var $default_func  = FALSE; 
	var $default_title = 'top';      // title of default action
	
	/** title of each action. used in main button. 
	 */
	var $titles        = array();    // list of titles for each action.
	var $sub_button    = 'none';     // sub button type like reset, back...
	
	// ---------------------------------
	/** stores data used in/out of Page instance.
	 */
	var $data = array();
	
	// ---------------------------------
	/** data passed to next action (usually after post).
	 */
	var $pgg  = FALSE;
	
	// ---------------------------------
	/**
	 *	for messages and error controls
	 */
	const  MESSAGE      = 0;
	const  NOTICE       = 1;
	const  ERROR        = 2;
	const  CRITICAL     = 3;
	const  FATAL        = 4;
	var    $msg_array    = array();
	var    $error_level = 0;
	var    $disp_func   = FALSE;
	
	/** view 
	 */
	var $view          = FALSE;
	// +-------------------------------------------------------------+
	// main execution routine
	// +-------------------------------------------------------------+
	/**
	 *	constructor. initializes pgg.
	 */
	function __construct() {
		$this->pgg = new \pgg_check( self::PGG_ID );
		$this->pgg->restorePost();
	}
	// +-------------------------------------------------------------+
	/**
	 *	main controler for actions.
	 */
	function action()
	{
		if( WORDY ) wordy_table( $this->method, 'Action::control' );
		foreach( $this->methods as $method ) {
			$func = $method[0];
			$act  = $method[1];
			if( isset( $_REQUEST[ $this->act_name ] ) && 
				$act === $_REQUEST[ $this->act_name ] ) {
				return $this->execute( $method );
			}
		}
		return $this->execute( $this->default_func );
	}
	// +-------------------------------------------------------------+
	/**
	 *	executes method based on action.
	 */
	function execute( $method )
	{
		try {
			$func = $method[0];
			$act  = $method[1];
			$view = $method[2];
			if( WORDY ) wordy_table( $method, "executing:{$func}:".$this->getCurrTitle() );
			if( !have_value( $func ) ) {
				throw new PageMc_Exception( 
					"no function is set, arg={$method{1}}, act={$method{2}}" );
			}
			if( !is_callable( $func ) ) {
				throw new PageMc_Exception( 
					"function is not callable, arg={$method{1}}, act={$method{2}}" );
			}
			$this->curr_act = $act;
			$this->view     = $view;
			call_user_func( $func, $this, $method );
			$this->execView();
		}
		catch( AppException $e ) {
			$this->error( $e->getMessage() );
		}
	}
	// +-------------------------------------------------------------+
	/**
	 *	view (execute a function or include a file). 
	 */
	function execView() {
		if( !have_value( $this->view ) ) {
			return;
		}
		if( is_callable( $this->view ) ) {
			return call_user_func( $this->view, $this );
		}
		if( file_exists( $this->view ) ) {
			$this->get();  // get all data.
			$page = $this; // send this as $page.
			include( $this->view );
		}
	}
	// +-------------------------------------------------------------+
	/**　
	 *	sets view.
	 *	@param mix $view
	 *		either function or filename.
	 *		- function to execute as view. 
	 *		- file name to include as view.
	 */
	function setView( $view ) {
		$this->view = $view;
	}
	// +-------------------------------------------------------------+
	/**　
	 *	sets default method.
	 */
	function setDefault( $func, $options=array() ) {
		$default = array(
			'title' => NULL,
			'view'  => FALSE,
		);
		if( !is_array( $options ) ) {
			$options = array( 'title' => $options ); // for older style.
		}
		$options = array_merge( $default, $options );
		$this->default_func  = array( $func, FALSE, $options[ 'view' ] );
		$this->default_title = $options[ 'title' ];
		return $this;
	}
	// +-------------------------------------------------------------+
	/**
	 *	sets actions for given $act.
	 */
	function setAction( $act, $func, $options=array() ) {
		$default = array(
			'title' => NULL,
			'view'  => FALSE,
		);
		if( !is_array( $options ) ) {
			$options = array( 'title' => $options ); // for older style.
		}
		$options = array_merge( $default, $options );
		$this->methods[] = array( $func, $act, $optoins[ 'view' ] ); 
		$this->titles[ $act ] = $options[ 'title' ];
		return $this;
	}
	// +-------------------------------------------------------------+
	/**
	 *	set or get next_act. 
	 */
	function nextAct( $act=FALSE ) {
		if( $act !== FALSE ) {
			$this->next_act = $act;
		}
		return $this->next_act;
	}
	// +-------------------------------------------------------------+
	/**
	 *	set or get curr_act. 
	 */
	function currAct( $act=FALSE ) {
		if( $act !== FALSE ) {
			// probably should not use this functionality.
			$this->curr_act = $act;
		}
		return $this->curr_act;
	}
	// +-------------------------------------------------------------+
	/**
	 *	checks if current action is default action. 
	 */
	function isDefault() {
		return $this->curr_act === FALSE;
	}
	// +-------------------------------------------------------------+
	/**
	 *	get title of the current action. 
	 */
	function getCurrTitle() {
		if( $this->isDefault() ) {
			return $this->default_title;
		}
		else 
		if( isset( $this->titles[ $this->curr_act ] ) ) {
			return $this->titles[ $this->curr_act ];
		}
		return FALSE;
	}
	// +-------------------------------------------------------------+
	/**
	 *	get title of the next action. 
	 */
	function getNextTitle() {
		if( have_value( $this->titles[ $this->next_act ] ) ) {
			return $this->titles[ $this->next_act ];
		}
		return $this->default_title;
	}
	// +-------------------------------------------------------------+
	//  push/pop methods for passing data to next action via post.
	// +-------------------------------------------------------------+
	/**
	 *	push data so that the data can be popped at next action page.
	 *
	 *	@param mix $data/$name	
	 *		$data array is stored.
	 *		$name string is stored with value specified as 2nd arg.
	 *	@param string $value
	 *		value of $name.
	 *	@return object
	 *		returns $this. 
	 */
	function push() {
		$num = func_num_args();
		$arg = func_get_args();
		if( $num == 1 ) {
			if( is_array( $arg[0] ) ) {
				$this->pgg->setAllVars( $arg[0] );
			}
		}
		else
		if( $num > 1 ) {
			$this->pgg->pushValue( $arg[0], $arg[1] );
		}
		return $this;
	}
	// +-------------------------------------------------------------+
	/**
	 *	pops data from previous action.
	 *
	 *	@param	string $name
	 *		specify name of value to pop. 
	 *		if omitted, returns all of data.
	 *	@return mix
	 *		returns data. 
	 */
	function pop() {
		$num = func_num_args();
		if( $num > 0 ) { 
			// return data of specified name.
			$arg = func_get_args();
			return $this->pgg->popVariables( $arg[0] );
		}
		else { 
			// return all data.
			return $this->pgg->popVariables();
		}
	}
	// +-------------------------------------------------------------+
	/**
	 *	adds token to prevent loading twice.
	 */
	function pushToken() {
		$this->pgg->pushToken();
		return $this;
	}
	// +-------------------------------------------------------------+
	/**
	 *	verifies if is a refresh load. also prevents CSRF.
	 */
	function verifyToken() {
		return $this->pgg->verifyToken();
	}
	// +-------------------------------------------------------------+
	/**
	 *	pops input/hidden tag for next_act. not to use this method. 
	 */
	function popNextActHiddenTag() {
		if( $this->next_act ) {
			if( WORDY ) echo "popNextActHiddenTag: {$this->act_name}={$this->next_act}";
			return 
				"<input type=\"hidden\" name=\"{$this->act_name}\"" . 
				" value=\"{$this->next_act}\">\n";
		}
	}
	// +-------------------------------------------------------------+
	/**
	 *	output input/hidden tags to pass to next action. 
	 *	includes pushed data, next_act, and token. 
	 */
	function savePost() {
		$html  = $this->pgg->savePost();
		$html .= $this->popNextActHiddenTag();
		if( WORDY > 3 ) echo html_safe( $html ) . "<br />";
		return $html;
	}
	// +-------------------------------------------------------------+
	//  add/get methods for storing data into this instance.
	// +-------------------------------------------------------------+
	/**
	 *	adds data to Control instace. 
	 *
	 *	@param mix $data/$name	
	 *		$data array is added.
	 *		$name string is added with value specified as 2nd arg.
	 *	@param string $value
	 *		value of $name.
	 *	@return object
	 *		returns $this. 
	 */
	function add() {
		$num = func_num_args();
		$arg = func_get_args();
		if( $num == 1 ) {
			if( is_array( $arg[0] ) ) {
				$this->data = array_merge( $this->data, $arg[0] );
			}
		}
		else
		if( $num > 1 ) {
			$this->data[ $arg[0] ] = $arg[1]; 
		}
		if( WORDY > 1 ) wordy_table( $this->data[ $name ], "addData: $name" );
		return $this;
	}
	// +-------------------------------------------------------------+
	/**
	 *	get data from Control instance. 
	 *
	 *	@param	string $name
	 *		specify name of value to get. 
	 *		if omitted, returns all of data.
	 *	@return mix
	 *		returns data. 
	 */
	function get() {
		$num = func_num_args();
		if( $num > 0 ) { 
			// return data of specified name.
			$arg = func_get_args();
			return $this->data[ $arg[0] ];
		}
		else { 
			// return all data.
			return $this->data;
		}
	}
	// +-------------------------------------------------------------+
	//  methods for messages and buttons
	// +-------------------------------------------------------------+
	/**
	 *	sets sub button types. 
	 *	types are: 'back', 'reset', and 'none'. default is 'none'. 
	 */
	function setSubButton( $sub ) {
		$this->sub_button = $sub;
		return $this;
	}
	// +-------------------------------------------------------------+
	/**
	 *	get main submit button with next title as value.
	 */
	function getNextButton() {
		$title = $this->getNextTitle();
		$but   = "<input type=\"submit\" name=\"Submit\" value=\"{$title}\" />";
		return $but;
	}
	// +-------------------------------------------------------------+
	/**
	 *	get sub button as specified by setSubButton. 
	 */
	function getSubButton() {
		switch( $this->sub_button ) {
			case 'back':
				$sub = '<input type="button" name="Submit" value="戻る" onClick="history.back();">';
				break;
			case 'reset':
				$sub = '<input type="reset" name="Submit" value="リセット">';
				break;
			case 'none':
			default:
				$sub = '';
				break;
		}
		return $sub;
	}
	// +-------------------------------------------------------------+
	/**
	 *	set message with error level. 
	 *	for messages with normal level, it saves all messages.
	 *	for messages above notice leve, it saves only if 
	 *	level is higher than the current error level. 
	 */
	function setMessage( $msg, $level ) {
		if( $level == self::MESSAGE && $this->errorLevel() == self::MESSAGE ) {
			// save the all message. 
			$this->msg_array[] = $msg;
		}
		else
		if( $level > $this->errorLevel() ) {
			// saves only the last message.
			$this->msg_array = array( $msg );
			$this->errorLevel( $level );
		}
		return $this;
	}
	// +-------------------------------------------------------------+
	/**
	 *	returns message.
	 */
	function getMessage( $glue='<br />' ) {
		if( $glue === FALSE ) {
			$ret = $this->msg_array;
		}
		else {
			$ret = implode( $glue, $this->msg_array );
		}
		return $ret;
	}
	// +-------------------------------------------------------------+
	/**
	 *	saves message.
	 */
	function message( $msg ) {
		return $this->setMessage( $msg, self::MESSAGE );
	}
	// +-------------------------------------------------------------+
	/**
	 *	saves notice message.
	 */
	function notice( $msg ) {
		return $this->setMessage( $msg, self::NOTICE );
	}
	// +-------------------------------------------------------------+
	/**
	 *	saves error message.
	 */
	function error( $msg ) {
		return $this->setMessage( $msg, self::ERROR );
	}
	// +-------------------------------------------------------------+
	/**
	 *	saves ciritcal error message.
	 */
	function critical( $msg ) {
		return $this->setMessage( $msg, self::CRITICAL );
	}
	// +-------------------------------------------------------------+
	/**
	 *	saves fatal error message.
	 */
	function fatal( $msg ) {
		return $this->setMessage( $msg, self::FATAL );
	}
	// +--------------------------------------------------------------- +
	/**
	 *	set or get error level.
	 */
	function errorLevel( $level=FALSE ) {
		if( $level !== FALSE && is_numeric( $level ) ) {
			$this->error_level = $level;
		}
		return $this->error_level;
	}
	// +--------------------------------------------------------------- +
	/**
	 *	checks if error occured (error level above ERROR).
	 */
	function isError() {
		return $this->error_level >= self::ERROR;
	}
	// +--------------------------------------------------------------- +
	/**
	 *	checks if critical error occured (error level above CRITICAL).
	 */
	function isCritical() {
		return $this->error_level >= self::CRITICAL;
	}
	// +--------------------------------------------------------------- +
	/**
	 *	displays messages. 
	 *	specify function to display message in $this->disp_func. 
	 *	if not set, uses self::disp_message. 
	 */
	function display( $options=NULL ) {
		if( $this->disp_func ) {
			return call_user_func( 
				$this->disp_func, 
				$this->msg_array, $this->error_level, $options 
			);
		}
		else {
			return self::disp_message( $this->msg_array, $this->error_level, $options );
		}
	}
	// +-------------------------------------------------------------+
	/**
	 *	default function to display message. 
	 *	overwrite this method or specify function in $this->disp_func.
	 *
	 *	@param array	$msg         array of messages
	 *	@param int   	$err_level   error level
	 *	@param mix   	$options     from user input
	 */
	static function disp_message( $msg, $err_level, $options ) {
		if( !have_value( $msg ) ) return;
		
		if( is_array( $msg ) ) $msg = implode( '<br />', $msg );
		
		if( $err_level > 0 ) {
			$tbl_class = 'tblErr';
			$tbl_msg   = 'エラーがありました';
		}
		else
		if( $err_level > self::MESSAGE ) {
			$tbl_class = 'tblErr';
			$tbl_msg   = '確認してください';
		}
		else {
			$tbl_class = 'tblMsg';
			$tbl_msg   = 'メッセージ';
		}
		if( is_array( $opt ) ) extract( $opt );
		if( !isset( $width ) || !have_value( $width ) ) $width = '90%';
		
?>
<br>
<table class="<?php echo $tbl_class ?>" width="<?php echo $width; ?>"  border="0" align="center" cellpadding="2" cellspacing="2">
  <tr>
    <th align="center"><?php echo "<font color=white><b>{$tbl_msg}</b></font>\n"; ?></th>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF">
	  <?php echo $msg;?>
	</td>
  </tr>
</table>
<br>
<?php
	}
	// +-------------------------------------------------------------+
}



?>