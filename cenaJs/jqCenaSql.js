// JavaScript Document
/**
 * jQuery plug in for Cena WebSqlDatabase storage.
 * 
 * @copyright     Copyright 2010-2011, Asao Kamei
 * @link          http://www.workspot.jp/cena/
 * @license       GPLv2
 */

;( function($) {

    // +-------------------------------------------------+
    // + defaults for cenaBind
    // +-------------------------------------------------+
    var db; // database connection.
    var cenaSqlInitOptions = { // real default that will not change. 
        offset   : false,
        limit    : false,
        model    : '',
        order    : false,
        ascend   : true,
        cena_id  : '',
        child    : false,
        WORDY    : 0,                 // debug message. set to 0 to suppress.
        find     : {},
        count    : false, 
        end: "end"
    }
    var cena_env_def = 
        "create table if not exists cena_env( " + 
        "cena_name text unique, cena_id, scheme, model, type, id integer, act, column, value, state )";

    // default values used in the session.
    var cenaSqlOptions = cenaSqlInitOptions;

    db = openDatabase( 'cena_env_data', '0.1', 'cena envelope data', 2*1024*1024 );
    if( db ) $().cena( 'debug', 'created cena_env_data database' );

    db.transaction( function(tx) {
        tx.executeSql( cena_env_def, [], null, null );
    });
    // +-------------------------------------------------+
    // + definition of methods
    // +-------------------------------------------------+
    var methods = 
    {
        // +------------------------------------------------------+
        /**
         *  init: set options for cenaSql
         *	@return this
         */
        init : function( options ) {
            if( typeof( options ) == 'undefined' ) {
                cenaSqlOptions = cenaSqlInitOptions;
            }
            else {
                cenaSqlOptions = $.extend( {}, cenaSqlOptions, options );
            }
            return this;
        },
        // +------------------------------------------------------+
        /**
         *  drop: drop envelope table (cena_env)
         *	@return this
         */
        drop : function() {
            db.transaction( function(tx) {
                tx.executeSql( "drop table cena_env" );
            });
            return this;
        },
        // +------------------------------------------------------+
        /**
         *  clean: drop and re-create envelope table (cena_env)
         *	@return this
         */
        clean : function() {
            db.transaction( function(tx) {
                tx.executeSql( "drop table cena_env", [], null, null );
                tx.executeSql( cena_env_def, [], null, 
                    function( tx, err ) {
                        cenaSqlExecutionFail( 'cenaSql.clean: failed create cena_env <br />', err );
                    }
                );
            });
            return this;
        },
        // +------------------------------------------------------+
        /**
         *  add: add envelope data to db. uses cenaSqlReplace...
         *
         *	@param obj envelope_data
         *		envelope data to add.
         *	@return this
         */
        add : function( envelope_data ) {
            for( var i = 0; i < envelope_data.length; ++i ) {
                cenaSqlReplace( envelope_data[i] );
            }
            return this;
        },
        // +------------------------------------------------------+
        /**
         *  get: reads data from db and bind to envelope source
         *	using cena( 'bind' ). uses cenaSqlSelect...
         *	
         *	@param function callback
         *		callback function for cenaSql's get.
         *		argument is env_data (array of envelope data).
         *	@return this
         */
        get : function( callback ) {
            var cenaGetSqlOption = $.extend( {}, cenaSqlOptions );
            var sqlGet = new cenaSqlSelect( cenaGetSqlOption, callback );
            return this;
        },
        // +------------------------------------------------------+
        /**
         *  count: count number of data matches the criteria in db.
         *
         *	@param function callback
         *		callback function when count is finished.
         *		argument is (integer) count.
         *	@return this
         */
        count : function( callback ) {
            var cenaCountSqlOption = $.extend( {}, cenaSqlOptions );
            var sqlCount = new cenaSqlCount( cenaCountSqlOption, callback );
            return this;
        },
        // +------------------------------------------------------+
        /**
         *  find: set search conditions.
         *
         *	@param object options
         *		specify condition as follows.
         *		column : column name
         *		value  : value of the column
         *		type   : type of comparison (default is equal).
         *	@return this
         */
        find : function( options ) {
            if( typeof( options.type ) == 'undefined' ) {
                options[ 'type' ] = '=';
            }
            cenaSqlOptions.find = $.extend( {}, cenaSqlOptions.find, options );
            return this;
        },
        // +------------------------------------------------------+
        /**
         *  upload: reads updated/new data from db and output
         *	using cena( 'thru' ). uses cenaSqlUpload...
         *	
         *	@param function callback
         *		callback function f( data ) where data is a
         *		simple array of {cena_name,value}.
         *	@return this
         */
        upload : function( callback ) {
            var cenaUploadSqlOption = $.extend( {}, cenaSqlOptions );
            cenaSqlUpload( cenaUploadSqlOption, callback );
            return this;
        },
        // +------------------------------------------------------+
        /**
         * sync: get data for synchronizing local data with master 
         * database. only uploading local data is implemented.
         * 
         * @param function callback
         *     callback function f( data ) where data is a 
         *     cena envelope json object.
         * @return this
         */
        sync : function( callback ) {
            cenaSqlSyncUp( callback );
            return this;
        },
        // +------------------------------------------------------+
        /**
         *  child: reads child data from db for a given cena_id,
         *	and bind using cena( 'bind' ). uses cenaSqlSelect...
         *	
         *	@param object options
         *		options for cenaSql.
         *	@return this
         */
        child : function( callback ) {
            var cenaChildSqlOption = $.extend( {}, cenaSqlOptions );
            cenaChildSqlOption.child = true;
            var sqlChild = new cenaSqlSelect( cenaChildSqlOption, callback );
            return this;
        },
        // +------------------------------------------------------+
        end_of_list : function() {}
        // +------------------------------------------------------+
    }
    // +----------------------------------------------------------+
    // + cenaSql's main routine
    // +----------------------------------------------------------+
    $.fn.cenaSql = function( method ) 
    {
        if( methods[ method ] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
        }
        else
        if( typeof method === 'object' || !method ) {
            return methods.init.apply( this, arguments );
        }
        else {
            $.error( 'Method ' + method + ' does not exist on jQuery.cena' );
        }
    };
    // +-------------------------------------------------+
    /**
     * cenaSqlSyncUp:
     * reads data for upload, and returns cena envelope
     * data to callback function
     */
    var cenaSqlSyncUp = function( callback )
    {
        $().cena( 'debug', 'cenaSqlSyncUp started' );
        var uploadCallBack = callback;
        var env_data = [];
        db.transaction( function(tx) {
            tx.executeSql( 
                "SELECT * FROM cena_env WHERE state IN ( 'updated', 'new' )", 
                [],
                function( tx, rs ) {
                    cenaSqlFormEnvData( rs, {}, env_data );
                    uploadCallBack( env_data );
                },
                function( tx, err ) {
                    cenaSqlExecutionFail( 'cenaSqlUpload', err );
                }
            );
        });
    }
    // +-------------------------------------------------+
    /**
     *  cenaSqlUpload:
     *  reads env_data from database for upload.
     *  binds thru with simple span/input properties only.
     */
    var cenaSqlUpload = function( options, callback )
    {
        $().cena( 'debug', 'cenaSqlUpload started' );
        if( typeof( callback ) == 'undefined' ) {
            var uploadCallBack = function( env_data ) {
                $().cena().
                    cena( 'add', env_data ).
                    cena( 'thru' );
            };
        }
        else {
            var uploadCallBack = callback;
        }
        db.transaction( function(tx) {
            tx.executeSql( 
                "SELECT * FROM cena_env WHERE state IN ( 'updated', 'new' )", 
                [],
                function( tx, rs ) {
                    var data_list = [];
                    var row, length = rs.rows.length;
                    for( var i = 0; i < length; i++ ) {
                        row = rs.rows.item(i);
                        var data = {};
                        data.cena_name = row.cena_name;
                        data.value     = row.value;
                        data_list.push( data );
                    }
                    uploadCallBack( data_list );
                },
                function( tx, err ) {
                    cenaSqlExecutionFail( 'cenaSqlUpload', err );
                }
            );
        });
    };
    // +-------------------------------------------------+
    /**
     *  cenaSqlSelect function performs select on database.
     *  
     *	@param object options
     *		set sql options for search.
     *	@param function callback
     *		callback function when select successfull.
     *		this function is also called when no data.
     */
    var cenaSqlSelect = function( options, callback )
    {
        // get enveleope data from db.
        // should rewrite using subqueries...
        // or better yet, use better scheme...
        var env_data = [];
        var result = [];
        var selOption = options; 

        if( typeof( callback ) == 'undefined' ) { // use default callback. 
            var cenaOptions = $().cena.getOptions();
            var selectCallBack = 
                function( env_data )
                {
                    $()	.cena( cenaOptions )
                        .cena( 'add', env_data )
                        .cena( 'bind' )
                        .cena( 'clean' )
                    ;
                };
        }
        else {
            var selectCallBack = callback;
        }
        db.transaction( function(tx) 
        {
            var stmt = new MakeSqlSelectStmnt( selOption );

            $().cena( 'debug', 'cenaSqlSelect: sql_statement is... <br />' + stmt.sql_statement );
            tx.executeSql( 
                stmt.sql_statement, 
                stmt.sql_variables,
                function( tx, rs ) {
                    cenaSqlReadData( tx, rs );
                },
                function( tx, err ) {
                    cenaSqlExecutionFail( 'cenaSqlSelect', err );
                }
            );
        });
        // +-------------------------------------------------+
        var cenaSqlReadData = function( tx, rs )
        {
            if( cenaSqlOptions.WORDY > 2 ) $().cena( 'debug', 'cenaSqlReadData' );
            var cena_id_list = '', length = rs.rows.length;
            if( !length ) { 
                selectCallBack();
            }
            var env_hash = {};
            for( var i = 0; i < length; ++i ) {
                cena_id_list = cena_id_list + ", '" + rs.rows.item(i).cena_id + "'";
                env_hash[ rs.rows.item(i).cena_id ] = { prop:{}, link:{} };
            }
            cena_id_list = cena_id_list.substr( 2 );
            $().cena( 'debug', 'cenaSqlReadData: ids are... ' + cena_id_list );
            tx.executeSql( 
                'SELECT * FROM cena_env WHERE cena_id IN ( ' + cena_id_list + ' )', [], 
                function( tx, rs ) {
                    cenaSqlFormEnvData( rs, env_hash, env_data );
                    selectCallBack( env_data );
                },
                function( tx, err ) {
                    cenaSqlExecutionFail( 'cenaSqlReadData', err );
                }
            );
        }
    };
    var cenaSqlCount = function( options, callback )
    {
        // get enveleope data from db.
        // should rewrite using subqueries...
        // or better yet, use better scheme...
        var env_data = [];
        var result = [];
        var selOption = options; 
        var countCallBack = callback;

        db.transaction( function(tx) 
        {
            var stmt = new MakeSqlSelectStmnt( selOption, 'count' );

            $().cena( 'debug', 'cenaSqlCount: sql_statement is... <br />' + stmt.sql_statement );
            tx.executeSql( 
                stmt.sql_statement, 
                stmt.sql_variables,
                function( tx, rs ) {
                    if( rs.rows.length ) {
                        var row   = rs.rows.item(0);
                        var count = row.count;
                    }
                    else {
                        var count = 0;
                    }
                    countCallBack( count );
                },
                function( tx, err ) {
                    cenaSqlExecutionFail( 'cenaSqlSelect', err );
                }
            );
        });
    };
    function MakeSqlSelectStmnt( selOption, option )
    {
        var sql_statement = "SELECT DISTINCT cena_id FROM cena_env";
        var sql_variables = [];

        // set up for search. use subquery!
        if( typeof( selOption.find ) != 'undefined' && 
            typeof( selOption.find.column ) != 'undefined' && 
            typeof( selOption.find.type ) != 'undefined' && 
            typeof( selOption.find.value ) != 'undefined' &&
            selOption.find.column && selOption.find.value ) {
            var sub_table = 
                "\n JOIN ( " +
                "   SELECT cena_id FROM cena_env " + 
                "   WHERE model=? AND column=? AND " + 
                "	value " + selOption.find.type + " ?" + 
                " ) AS search_table USING( cena_id ) \n";
            sql_statement = sql_statement + sub_table;
            sql_variables.push( selOption.model );
            sql_variables.push( selOption.find.column );
            sql_variables.push( selOption.find.value );
        }
        var sql_where     = [];
        // setup where statement...
        if( selOption.model ) {
            sql_where.push( "model=?" );
            sql_variables.push( selOption.model );
        }
        if( selOption.child ) { // get children given cena_id. 
            sql_where.push( "act='link'" );
            sql_where.push( "value=?" );
            sql_variables.push( selOption.cena_id );
        }
        else
        if( selOption.cena_id ) { // or, simply search for the cena_id.
            sql_where.push( "cena_id=?" );
            sql_variables.push( selOption.cena_id );
        }
        if( selOption.order ) {
            sql_where.push( "column=?" );
            sql_variables.push( selOption.order );
        }
        if( sql_where.length ) {
            sql_statement = sql_statement + ' WHERE ' + sql_where.join( ' AND ' );
        }
        // setup other stuff: order, limit, offset, ...
        if( selOption.order ) {
            if( selOption.ascend ) {
                var order_direction = 'ASC';
            }
            else {
                var order_direction = 'DESC';
            }
            sql_statement = sql_statement + " ORDER BY value " + order_direction + ", id ASC";
        }
        else {
            sql_statement = sql_statement + " ORDER BY id";
        }
        if( selOption.limit && option!='count' ) {
            sql_statement = sql_statement + " LIMIT ?";
            sql_variables.push( selOption.limit );
        }
        if( selOption.offset ) {
            sql_statement = sql_statement + " OFFSET ?";
            sql_variables.push( selOption.offset );
        }
        if( option == 'count' ) {
            sql_statement = "SELECT COUNT(*) AS count FROM ( " + sql_statement + ")";
        }
        this.sql_statement = sql_statement;
        this.sql_variables = sql_variables;
    }
    // +-------------------------------------------------+
    // + cenaSqlExecutionFail
    // +-------------------------------------------------+
    var cenaSqlExecutionFail = function( name, err )
    {
        $().cena( 'debug', 'SQL Error!!! in ' + name );
        $().cena( 'debug', 'Error Code:' + err.code + ', message:' + err.message );
    }
    // +-------------------------------------------------+
    /**
     *  listner for onSubmit event of cena form.
     *	ignores the data if cena_name's value is the same.
     *	replaces the data with value based on cena_name.
     */
    // +-------------------------------------------------+
    $.fn.cenaSql.submitCenaData = function( cena_data ) {
        var local_data = cena_data;
        db.transaction( function(tx) {
            var name, val;
            for( var idx=0; idx < local_data.length; ++idx )
            {
                name = local_data[idx][ 'name' ];
                val  = local_data[idx][ 'val' ];
                $().cena( 'debug', 'cenaSql: submitting: ' + name + ' with ' + val );
                tx.executeSql(
                    "SELECT ? as cena_name, ? as value "
                    + "UNION ALL "
                    + "SELECT ? as cena_name, value FROM cena_env WHERE cena_name=?",
                    [ local_data[idx][ 'name' ], local_data[idx][ 'val' ], 
                      local_data[idx][ 'name' ], local_data[idx][ 'name' ] ],
                    function( tx, rs ) {
                        submitCenaDataReplace( tx, rs );
                    },
                    function( tx, err ) {
                        $().cena( 'debug', 'Submit FAILED! : '+name+'='+val );
                        cenaSqlExecutionFail( 'cenaSql.submitCenaData', err );
                    }
                );
            }
        });
        // +--------------------------------+
        var submitCenaDataReplace = function( tx, rs ) {
            var new_row   = rs.rows.item(0);
            var cena_name = new_row[ 'cena_name' ];
            var new_value = new_row[ 'value' ];
            var sqlType;
            if( rs.rows.length == 1 ) {
                $().cena( 'debug', 'submitCenaDataReplace: ' 
                    + cena_name + ' : new=' + new_value );
                sqlType = 'add';
            }
            else {
                var old_row = rs.rows.item(1);
                if( new_row['value'] == old_row['value'] ) return; 
                $().cena( 'debug', 'submitCenaDataReplace: ' 
                    + cena_name + ' : ' + old_row['value'] + ' => ' + new_value );
                sqlType = 'mod';
            }
            // replace cena_name data with new value.
            var env_data = $().cena.getEnvDataFromCenaName( cena_name );
            var act      = $().cena.getActionFromCenaName( cena_name );
            var column   = $().cena.getColumnFromCenaName( cena_name );
            if( sqlType == 'add' ) { // new data. insert it. 
                var sqlExec  =
                    "INSERT INTO cena_env( " + 
                    "cena_name, cena_id, scheme, model, type, id, act, column, value, state ) " + 
                    "values ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
                var sqlVals  = 
                    [ cena_name, env_data.cena_id, env_data.scheme, env_data.model, 
                      env_data.type, env_data.id, act, column, new_value, 'updated' ];
            }
            else
            if( sqlType == 'mod' ) { // modifying existing data. update it.
                var sqlExec  =
                    "UPDATE cena_env SET value=?, state=? WHERE cena_name=?";
                var sqlVals  = 
                    [ new_value, 'updated', cena_name ];
            }
            else {
                var sqlExec  =
                    "replace into cena_env( " + 
                    "cena_name, cena_id, scheme, model, type, id, act, column, value, state )" + 
                    "values( ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
                var sqlVals  = 
                    [ cena_name, env_data.cena_id, env_data.scheme, env_data.model, 
                      env_data.type, parseInt( env_data.id ), act, column, new_value, 'updated' ];
            }
            tx.executeSql( 
                sqlExec, 
                sqlVals,
                function( tx, rs ) {
                    $().cena( 'debug', 'submitCenaDataReplace: '
                        + sqlType + ' : ' + cena_name + ' with ' + new_value );
                },
                function( tx, err ) {
                    cenaSqlExecutionFail( 'cenaSql.updateEventListener', err );
                }
            );
        }
    };
    // +-------------------------------------------------+
    /**
     *  listner for onChange event of cena form elements.
     *	replaces the data with value based on cena_name.
     */
    // +-------------------------------------------------+
    var updateEventListener = function( elem ) {
        if( typeof( elem.checkValidity ) != 'undefined' ) {
            if( !elem.checkValidity() )
                $().cena( 'debug', 'cenaSql.updateEventListener: ' 
                + 'validity check failed on ' 
                + $(elem).attr( 'name' ) + ' to ' + $(elem).val() );
        }
        var cena_name = $( elem ).attr( 'name' );
        var value     = $( elem ).val();
        $().cena( 'debug', 'cenaSql: updating ' + cena_name + ' with ' + value );
        db.transaction( function(tx) {
            var env_data = $().cena.getEnvDataFromCenaName( cena_name );
            var act      = $().cena.getActionFromCenaName( cena_name );
            var column   = $().cena.getColumnFromCenaName( cena_name );
            tx.executeSql( 
                    "replace into cena_env( " + 
                    "cena_name, cena_id, scheme, model, type, id, act, column, value, state )" + 
                    "values( ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )", 
                    [ cena_name, env_data.cena_id, env_data.scheme, env_data.model, 
                      env_data.type, env_data.id, act, column, value, 'updated' ],
                function( tx, rs ) {
                    $().cena( 'debug', 'cenaSql: updated ' + cena_name + ' with ' + value );
                },
                function( tx, err ) {
                    cenaSqlExecutionFail( 'cenaSql.updateEventListener', err );
                }
            );
        });
    }
    // +-------------------------------------------------+
    /**
     *  cenaSqlFormEnvData:
     *  converts rs (result set) to env_data.
     */
    var cenaSqlFormEnvData = function( rs, env_hash, env_data )
    {
        var cena_id;
        var length = rs.rows.length, row;
        for( var i = 0; i < length; i++ ) 
        {
            row     = rs.rows.item(i);
            cena_id = row.cena_id;
            if( cenaSqlOptions.WORDY > 3 ) $().cena( 'debug', '- ' + cena_id );
            if( typeof( env_hash[ cena_id ] ) == 'undefined' ) {
                env_hash[ cena_id ] = { prop:{}, link:{} };
            }
            env_hash[ cena_id ].cena_id = cena_id;
            env_hash[ cena_id ].scheme  = row.scheme;
            env_hash[ cena_id ].model   = row.model;
            env_hash[ cena_id ].type    = row.type;
            env_hash[ cena_id ].id      = row.id;
            if( row.act == 'prop' ) {
                env_hash[ cena_id ].prop[ row.column ] = row.value;
            }
            else
            if( row.act == 'link' ) {
                env_hash[ cena_id ].link[ row.column ] = row.value;
            }
        }
        for( cena_id in env_hash ) {
            env_data.push( env_hash[ cena_id ] );
        }
        env_data;
    };
    // +-------------------------------------------------+
    /**
     *  cenaSqlReplace insert/replace env_data to db
     */
    var cenaSqlReplace = function( env_data )
    {
        var column, cena_name, value;
        db.transaction( function(tx) 
        {
            //$().cena( 'debug', 'cenaSqlReplace:tx: got ' + cena_name + '=' + value );
            // save properties to cena_data table.
            var act = 'prop';
            for( column in env_data.prop ) 
            {
                cena_name = $().cena.getCenaName( env_data, act, column );
                value     = env_data.prop[ column ];
                $().cena( 'debug', 'cenaSqlReplace:tx: starting ' + cena_name + '=' + value );
                tx.executeSql( 
                    "replace into cena_env( " + 
                    "cena_name, cena_id, scheme, model, type, id, act, column, value, state )" + 
                    "values( ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )", 
                    [ cena_name, env_data.cena_id, env_data.scheme, env_data.model, 
                      env_data.type, env_data.id, act, column, value, env_data.type ],
                    null,
                    function( tx, err ) {
                        cenaSqlExecutionFail( 'cenaSqlReplace/set', err );
                    }
                );
            }
            // save relationsto cena_data table.
            act = 'link';
            for( column in env_data.link ) 
            {
                cena_name = $().cena.getCenaName( env_data, act, column );
                value     = env_data.link[ column ];
                $().cena( 'debug', 'cenaSqlReplace:tx: starting ' + cena_name + '=' + value );
                tx.executeSql( 
                    "replace into cena_env( " + 
                    "cena_name, cena_id, scheme, model, type, id, act, column, value, state )" + 
                    "values( ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )", 
                    [ cena_name, env_data.cena_id, env_data.scheme, env_data.model, 
                      env_data.type, env_data.id, act, column, value, env_data.type ],
                    null,
                    function( tx, err ) {
                        cenaSqlExecutionFail( 'cenaSqlReplace/rel', err );
                    }
                );
            }
        });
    };

}) ( jQuery );
// -------------------- jqCenaSql Plug-In ----------------------
