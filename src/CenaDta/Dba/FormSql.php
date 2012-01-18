<?php
namespace CenaDta\Dba;
/**
 *	Class for formig SQL statements.
 *
 *	@copyright     Copyright 2010-2011, Asao Kamei
 *	@link          http://www.workspot.jp/cena/
 *	@license       GPLv2
 */

class FormSql
{
	const INSERT_NO_COL = '2';
    /* -------------------------------------------------------------- */
	// static class to form sql statement.
    /* -------------------------------------------------------------- */
    static function insert( $table, $vals, $type=1 )
    {
		if( !$table ) return FALSE;
        $sql  = "INSERT INTO " . $table;
        $columns = NULL;
        $values  = NULL;
		if( $vals && is_array( $vals ) )
        while( list( $col, $val ) = each( $vals ) )
        {
            if( !have_value( $val ) ) continue;
            if( !have_value( $col ) ) continue;
            
            if( $columns ) $columns .= ', ';
			$columns .=  "$col";
			
            if( $values ) $values  .=  ', ';
			$values  .= "$val";
        }
		if( $type == self::INSERT_NO_COL ) {
	        $sql .= " VALUES ( $values )";
		} else {
	        $sql .= " ( $columns ) VALUES ( $values )";
		}
        
        return $sql;
    }
    /* -------------------------------------------------------------- */
    static function update( $table, $vals, $where )
    {
		if( !$table ) return FALSE;
		if( !$vals && !$func ) return FALSE;
        $update = '';
		if( $vals && is_array( $vals ) );
        while( list( $col, $val ) = each( $vals ) )
        {
			if( $val === FALSE ) { // ignore condition when value is FALSE 
				continue;
			}
			else 
			if( !have_value( $val ) ) { // val is empty
				$val_sql = $col;
			}
			else {
				$val_sql = "{$col}={$val}";
			}
            if( have_value( $update ) ) $update .= ', ';
            $update .= $val_sql;
        }
        if( !$update ) return FALSE;
        $sql = "UPDATE {$table} SET {$update} ";
        if( $where) $sql .= "WHERE " . $where;
        
        return $sql;
    }
    /* -------------------------------------------------------------- */
    static function delete( $table, $where )
    {
        if( !$where ) { return FALSE; } // do not delete if no where condition!
        $sql = "DELETE FROM {$table}";
        if( $where  ) $sql .= " WHERE {$where}";
        
        return $sql;
    }
    /* -------------------------------------------------------------- */
    static function select( $table, $cols, $where, $options=array() )
    {
		return 'SELECT '
			. self::select_( $table, $cols, $where, $options );
    }
    /* -------------------------------------------------------------- */
    static function selectDistinct( $table, $cols, $where, $options=array() )
    {
		return 'SELECT DISTINCT '
			. self::select_( $table, $cols, $where, $options );
    }
    /* -------------------------------------------------------------- */
    static function selectUpdate( $table, $cols, $where, $options=array() )
    {
		return 'SELECT '
			. self::select_( $table, $cols, $where, $options ) 
			. ' FOR UPDATE';
    }
    /* -------------------------------------------------------------- */
    static function select_( $table, $cols, $where, $options=array() )
    {
        if( !$table ) { 
			throw new DbSqlException( 'makeSqlSelect: missing table' );
        }
        if( !$cols  ) { $cols = array( "*" ); }
		if( !$cols  ) { 
			$cols = "*"; 
		}
		else
		if( is_array( $cols ) ) { 
			$cols = implode( ", ", $cols ); 
		}
		// options are:
		// group, having, order, misc, limit, offset
		if( !empty( $options ) && is_array( $options ) ) extract( $options );
        $sql = "{$cols} FROM {$table}";
        if( have_value( $where    ) ) $sql .= " WHERE {$where}";
        if( have_value( $group    ) ) $sql .= " GROUP BY {$group}";
        if( have_value( $having   ) ) $sql .= " HAVING {$having}";
        if( have_value( $order_by ) ) $sql .= " ORDER BY {$order_by}";
        if( have_value( $misc     ) ) $sql .= " {$misc}";
		if( $limit  > 0             ) $sql .= " LIMIT {$limit}"; 
		if( $offset > 0             ) $sql .= " OFFSET {$offset}"; 
		
        return $sql;
    }
    /* -------------------------------------------------------------- */
}

?>