<?php
require_once( dirname( __FILE__ ) . "/../../Dba/Model.php" );
use CenaDta\Dba\Model as ormModel;

// +----------------------------------------------------------------------+
// + コンタクトDAO
// +----------------------------------------------------------------------+

class dao_contact100 extends ormModel
{
	static $dao_table;            // "the" table name. 
	function __construct( $config=NULL )
	{
		//parent::__construct( $config );
		$this->id_name    = "contact_id";
		$this->dao_table  = "utest100_contact";
		$this->table      = $this->dao_table;
		self::$dao_table  = $this->dao_table;
		$this->clear();
		
		$this->del_flag   = '';
		$this->del_value  = '';
		
		$this->new_date = '';
		$this->new_time = '';
		$this->mod_date = '';
		$this->mod_time = '';
	}
	// +----------------------------------------------------------+
	// +----------------------------------------------------------+
	// +----------------------------------------------------------+
	function check_input_prep( &$pgg )
	{
		return $pgg->errGetNum();
	}
	// +----------------------------------------------------------+
	function check_input( &$pgg )
	{
		/* Checking User Input */
		//$pgg->pushChar( 'contact_id',        PGG_VALUE_MISSING_OK, PGG_REG_NUMBER ); // コンタクトID
		$pgg->pushChar( 'contact_name',      PGG_VALUE_MUST_EXIST ); // 名前
		$pgg->pushChar( 'contact_gender',    PGG_VALUE_MUST_EXIST, sel_gender::$item_list ); // 性別
		$pgg->pushChar( 'contact_type',      PGG_VALUE_MUST_EXIST, sel_contact_type::$item_list ); // 分類
		$pgg->pushDate( 'contact_date',      PGG_VALUE_MUST_EXIST ); // 日付
		
		return $pgg->errGetNum();
	}
	// +----------------------------------------------------------+
	function ignore_check_for_new( $data )
	{
		if( !have_value( $data[ 'contact_name' ] ) ) return TRUE;
		return FALSE;
	}
	// +----------------------------------------------------------+
	function listData( &$data )
	{
		if( $this->del_flag && $this->del_value ) {
			$where = "{$this->del_flag}!={$this->del_value}";
		}
		else {
			$where = NULL;
		}
		return $this->selectWhere( $data, $where );
	}
	// +----------------------------------------------------------+
	static function popHtml( $var_name, $html_type, $td, $err=array() )
	{
		$sel = self::getSelInstance( $var_name );
		if( method_exists( $sel, 'popHtml' ) ) {
			return $sel->popHtml( $html_type, $td, $err );
		}
		if( WORDY ) echo "popHtml( $var_name, $html_type, $td, $err ):: <font color=red>method 'popHtml' not exists!</font><br>";
	}
	// +----------------------------------------------------------+
	static function getSelInstance( $var_name )
	{
		static $selectors = array();
		
		if( !isset( $selectors[ $var_name ] ) ) {
			$selectors[ $var_name ] = self::getSelector( $var_name );
		}
		return $selectors[ $var_name ];
	}
	// +----------------------------------------------------------+
	static function getSelector( $var_name )
	{
		switch( $var_name ) {

			case 'contact_id': // コンタクトID
				$sel = new formHidden( 'contact_id', '', '', 'OFF' );
				break;

			case 'contact_name': // 名前
				$sel = new formText( 'contact_name', '15', '', 'ON' );
				break;

			case 'contact_gender': // 性別
				$sel = new sel_gender( 'contact_gender', '', '', 'OFF' );
				break;

			case 'contact_type': // 分類
				$sel = new sel_contact_type( 'contact_type', '', '', 'OFF' );
				break;

			case 'contact_date': // 分類
				$sel = new formText( 'contact_date', '12', '12', 'OFF' );
				//$sel = new selDateDs( 'contact_date', '', '', 'OFF' );
				break;

			default:
				$sel = FALSE;
				break;
		}
		return $sel;
	}
	// +----------------------------------------------------------+
	static function disp_html( $html_type, $td, $err )
	{
		html_forms::setDefault( 'class', 'htmlForms' );
?>
<table class="tblHover" width="100%">
  <tr>
    <th width="25%">コンタクトID</th>
    <td><?php echo dao_contact100::popHtml( 'contact_id', $html_type, $td, $err  ); ?></td>
  </tr>
  <tr>
    <th>名前</th>
    <td><?php echo dao_contact100::popHtml( 'contact_name', $html_type, $td, $err  ); ?></td>
  </tr>
  <tr>
    <th>性別</th>
    <td><?php echo dao_contact100::popHtml( 'contact_gender', $html_type, $td, $err  ); ?></td>
  </tr>
  <tr>
    <th>分類</th>
    <td><?php echo dao_contact100::popHtml( 'contact_type', $html_type, $td, $err  ); ?></td>
  </tr>
  <tr>
    <th>日付</th>
    <td><?php echo dao_contact100::popHtml( 'contact_date', $html_type, $td, $err  ); ?></td>
  </tr>
</table>
<?php
	}
	// +----------------------------------------------------------+
	static function disp_html_row( $html_type, $data, $err )
	{
		html_forms::setDefault( 'class', 'htmlForms' );
?>
<table class="tblHover" width="100%">
<?php
$id_name = $this->getIdName();
?>
<thead><tr>
  <th>ID</th>
  <th>修正</th>
  <th>削除</th>
  <th>名前</th>
  <th>性別</th>
  <th>分類</th>
  <th>日付</th>
</tr></thead>
<tbody>
<?php
  for( $i=0; $i<count( $data ); $i++ ) { 
    $id_name = $this->getIdName();
    $td      = $data[ $i ]; 
	$id      = $td[ $id_name ];
	html_forms::$var_footer="[{$i}]";

?><tr>
  <td><?php echo $id; ?><?php echo html_forms::htmlHidden( $id_name, $id ); ?></td>
  <td><a href="contact100_crud.php?act=mod&<?php echo $id_name;?>=<?php echo $td[$id_name];?>">修正</a></td>
  <td><a href="contact100_crud.php?act=del&<?php echo $id_name;?>=<?php echo $td[$id_name];?>">削除</a></td>
  <td><?php echo dao_contact100::popHtml( 'contact_name',    $html_type, $td, $err  ); ?></td>
  <td align="center"><?php echo dao_contact100::popHtml( 'contact_gender',  $html_type, $td, $err  ); ?></td>
  <td align="center"><?php echo dao_contact100::popHtml( 'contact_type',    $html_type, $td, $err  ); ?></td>
  <td align="center"><?php echo dao_contact100::popHtml( 'contact_date',    $html_type, $td, $err  ); ?></td>
</tr>
<?php } ?>
</tbody></table>

<?php
	}
	// +----------------------------------------------------------+
	static function disp_record_row( $records, $html_type )
	{
		html_forms::setDefault( 'class', 'htmlForms' );
?>
<table class="tblHover" width="100%">
<thead><tr>
  <th>ID</th>
  <th>名前</th>
  <th>性別</th>
  <th>分類</th>
  <th>日付</th>
  <th>削除</th>
  </tr></thead>
<tbody>
<?php
  for( $i=0; $i<count( $records ); $i++ ) { 
    $rec = $records[ $i ]; 
?><tr>
  <td height="30"><?php echo $rec->getId(); ?><?php echo $rec->popIdHidden(); ?></td>
  <td><?php echo $rec->popHtml( 'contact_name', $html_type ); ?></td>
  <td align="center"><?php echo $rec->popHtml( 'contact_gender',  $html_type  ); ?></td>
  <td align="center"><?php echo $rec->popHtml( 'contact_type',    $html_type  ); ?></td>
  <td align="center"><?php echo $rec->popHtml( 'contact_date',    $html_type  ); ?></td>
  <td><?php echo $rec->popDelCheck( $html_type ); ?></td>
  </tr>
<?php } ?>
</tbody></table>

<?php
	}
	// +----------------------------------------------------------+
}
?>