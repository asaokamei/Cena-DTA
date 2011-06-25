// JavaScript Document

// -------------------- jqCena Plug-In ----------------------
;( function($) {

	// +-------------------------------------------------+
	// + defaults for cenaBind
	// +-------------------------------------------------+
	var cenaInitOptions = { // real default that will not change. 
		cena_msg : '#cena_msg',       // output messages to here. 
		cena_error : '#cena_error',     // output error messages to here. 
		cena_debug : '#cena_debug',     // output debug messages to here. 
		env_src  : "#cena_env_src",   // envelope source to be bound. 
		env_post : "#cena_post",      // bound envelope is appened here. 
		env_class: 'cena_envelope',   // class name of bound envelope
		cena_elem: '.cena_element',   // class of cena's element. 
		cena_form_elem: 'cena_form_elem',   // class of cena's form element. 
		bind_type: 'populate',        // type of bind: populate or replace. 
		WORDY    : 0,                 // debug message. set to 0 to suppress.
		model    : false,             // model name to bind. 
		end: "end"
	}
	// default values used in the session.
	var cenaOptions = cenaInitOptions;
	
	// +-------------------------------------------------+
	// + definition of methods
	// +-------------------------------------------------+
	var methods = 
	{
		// +------------------------------------------------------+
		/** init: initialize cena plug-in. 
		 */
		init : function( options ) {
			if( typeof( options ) == 'undefined' ) {
				cenaOptions = cenaInitOptions;
			}
			else {
				cenaOptions = $.extend( {}, cenaOptions, options );
			}
			return this;
		},
		// +------------------------------------------------------+
		/** message: appends messages to option.cena_msg.
         *	@param string msg
         *		message string. adds '<br />' automatically.
         *	@returns this
		 */
		message : function( msg ) {
			$( cenaOptions.cena_msg ).append( msg + '<br />' );
			return this;
		},
		// +------------------------------------------------------+
		/** debug: appends debug message to option.cena_debug.
         *	set options.WORDY to 1 or greater.
         *	@param string msg
         *		debug message. adds '<br />' automatically.
         *	@returns this
		 */
		debug : function( msg ) {
			if( cenaOptions.WORDY <= 0 ) return this;
			$( cenaOptions.cena_debug ).append( msg + '<br />' );
			return this;
		},
		wordy : function( msg ) {
			$( cenaOptions.cena_debug ).append( msg + '<br />' );
			return this;
		},
		// +------------------------------------------------------+
		/** add: adds envelope data to jQuery's chained data.
         *	@param object env_data
         *		an array of envelope data.
         *	@returns this
         *		return array of envelop data as method chain.
		 */
		add : function( env_data ) {
			var old_data = $( this ).get();
			var new_data = old_data.concat( env_data );
			return $( new_data );
		},
		// +------------------------------------------------------+
		/** bind: binds envelope data to envelop-source. data is
         *	taken from jQuery's method chain, and source is
         *	specified by options. options maybe specified in
         *	as $().cena( {...} ) in prior to bind.
         *	
         *	@param object options
         *		specify bind options.
         *	@return this
		 */
		bind : function( options ) 
		{
			cenaOptions = $.extend( {}, cenaOptions, options );
			$().cena( 'debug', 'bind options: '
				+ 'env_src=' + cenaOptions.env_src
				+ ', env_post=' + cenaOptions.env_post );
			
			// --------- main bind loop ----------
			// 'this' is an array of envelope data. 
			// loop through all data as env_data.
			this.each( function( idx, env_data ) 
			{
				if( cenaOptions.WORDY ) {
					if( cenaOptions.WORDY > 2 ) $().cena( 'debug',  '' );
					$().cena( 'debug',  '#cena:bind for : ' + this.cena_id );
				}
				if( typeof( env_data ) == 'undefined' ) { return; }
				if( cenaOptions.model && env_data.model != cenaOptions.model ) {
					return ;
				}
				var envelope = $( cenaOptions.env_src )
					.clone()
					.addClass(    cenaOptions.env_class )
					.removeAttr(  "id" )
				// now binding envelope with this data...
				// 'this' now is env_data (one of envelope data). 
				cenaBindEnvAndData( envelope, env_data );
				envelope
					.appendTo(    cenaOptions.env_post ) // clone goes here.
					.fadeIn()
				;
			});
			// end of loop on 'this'.
			// --------- main bind loop ----------
			return this;
		},
		// +------------------------------------------------------+
		/** thru: bind envelope data with simple input/span
         *	elements; for uploading form.
         *	@return this
		 */
		thru : function() {
			$().cena( 'debug', 'thru data for binding' );
			$( this ).each( function() {
				$().cena( 'debug', 'thru: env_src=' + cenaOptions.env_src 
					+ ', name=' + this.cena_name + ', value=' + this.value );
				var env_data = this;
				var envelope = $( cenaOptions.env_src )
					.clone()
					.removeAttr(  "id" )
					.addClass( cenaOptions.env_class )
					.show()
					.appendTo( cenaOptions.env_post );
				$( cenaOptions.cena_elem, envelope )
					.each( function() {
						if( this.tagName == 'SPAN' ) {
							$( this )
								.append( env_data.cena_name );
						}
						else {
							$( this )
								.attr( 'name', env_data.cena_name )
								.val( env_data.value );
						}
					})
				$().cena( 'clean' );
			});
		},
		// +------------------------------------------------------+
		/** activate: activates cena's elements to access DBA.
         *	@param function callback
         *		call back function to attach to cena's element.
         *		binds to cena's updateEventDefaultListener as
         *		default, which does nothing but outputs debug msg.
		 */
		activate : function( callback )
		{
			if( cenaOptions.WORDY ) {
				$().cena( 'debug', 'activating : ' + cenaOptions.cena_elem );
			}
			if( typeof( callback ) == 'undefined' || typeof( callback ) != 'function' ) {
				callback = updateEventDefaultListener;
			}
			$( cenaOptions.cena_elem ).change( function() { 
				callback( this ) 
			});
		},
		// +------------------------------------------------------+
		/** clean: hide envelope source (option.env_src).
         *	@return this
		 */
		clean : function() {
			$().cena( 'debug', 'cleaning: ' + cenaOptions.env_src );
			$( cenaOptions.env_src ).hide();
			return this;
		},
		// +------------------------------------------------------+
		/** restart: remove envelope class (generated envelop)
         *	as specified by options.env_class.
         *	@return this
		 */
		restart : function() {
			$().cena( 'debug', 'restart: ' + cenaOptions.env_class );
			$( '.' + cenaOptions.env_class ).remove();
			//$( cenaOptions.cena_msg ).contents().remove();
			return this;
		},
		// +------------------------------------------------------+
		end_of_list : function() {}
		// +------------------------------------------------------+
	}
	var updateEventDefaultListener = function( elem ) {
		if( typeof( elem.checkValidity ) != 'undefined' ) {
			if( !elem.checkValidity() )
				$().cena( 'debug', 'cena.updateEventDefaultListener: ' 
				+ 'validity check failed on ' 
				+ $(elem).attr( 'name' ) + ' to ' + $(elem).val() );
		}
		$().cena( 'debug', 'cena.updateEventDefaultListener: ' 
			+ $(elem).attr( 'name' ) + ' to ' + $(elem).val() );
	}
	// +----------------------------------------------------------+
	// + cena main routine
	// +----------------------------------------------------------+
	$.fn.cena = function( method ) 
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
	// +----------------------------------------------------------+
	/** cena.getOptions: returns current cena option.
     *	@return object
     *		option (cenaOptions).
	 */
	$.fn.cena.getOptions = function() {
		return cenaOptions;
	};
	// +----------------------------------------------------------+
	/** cena.getCenaName: returns cena_name (Cena[scheme][...)
     *	from envelope data.
     *	
     *	@param object env_data
     *		envelope data
     *	@param string act
     *		action as 'new', 'get', etc.
     *	@param string name
     *      column name (or element's name). name maybe
     *		'act.column', which overwrites argument value of act.
     *	@return string
     *		returns cena_name.
	 */
	// +----------------------------------------------------------+
	$.fn.cena.getCenaName = function( env_data, act, name ) {
		var cena_id = env_data[ "cena_id" ];
		var model   = env_data[ "model" ];
		var type    = env_data[ "type" ];
		var id      = env_data[ "id" ];
		if( typeof( name ) != 'undefined' ) {
			var info    = name.split( '.' );
			if( info.length > 1 ) {
				act  = info[0];
				name = info[1];
			}
		}
	
		var cena_name = 'Cena[' + model + '][' + type + '][' + id + ']';
		if( typeof( act ) != 'undefined' ) {
			cena_name = cena_name.concat( '[' + act + ']' );
			if( typeof( name ) != 'undefined' ) {
				cena_name = cena_name.concat( '[' + name + ']' );
			}
		}
		return cena_name;
	};
	// +----------------------------------------------------------+
	// cena.store: stores information to localStorage
	// honestly, not sure if this should reside inside cena...
	// +----------------------------------------------------------+
	
	// +----------------------------------------------------------+
	/** cenaStoreTypes has lists of data and it's types.
     *	should be { store_name: type }, and
     *	type can be 'int', 'boolean', and 'string'.
	 */
	var cenaStoreTypes    = {}; // types of store
	// +----------------------------------------------------------+
	/** cenaStoreDefaults has lists of default value for each data.
     *	should be {store_name: default_value }.
	 */
	var cenaStoreDefaults = {}; // default values
	
	// +----------------------------------------------------------+
	$.fn.cena.storeTypes = function( types ) {
		cenaStoreTypes = $.extend( {}, cenaStoreTypes, types );
		return cenaStoreTypes;
	};
	// +----------------------------------------------------------+
	$.fn.cena.storeDefaults = function( defaults, setForce ) {
		cenaStoreDefaults = $.extend( {}, cenaStoreDefaults, defaults );
		if( setForce ) {
			jQuery.each( 
				cenaStoreDefaults, 
				function( key, val ) {
					var type = cenaStoreTypes[ key ];
					$().cena.storeByType( key, val, type );
				})
			;
		}
		return cenaStoreDefaults;
	};
	// +----------------------------------------------------------+
	$.fn.cena.storeInit = function() {
		jQuery.each( 
			cenaStoreDefaults, 
			function( key, val ) {
				var store_val = $().cena.store( key );
				if( typeof( store_val ) == 'undefined' ) {
					var type = cenaStoreTypes[ key ];
					$().cena.storeByType( key, val, type );
				}
			})
		;
		return cenaStoreDefaults;
	};
	// +----------------------------------------------------------+
	$.fn.cena.storeGetAll = function() {
		var all_value = {};
		jQuery.each( 
			cenaStoreDefaults, 
			function( key, val ) {
				all_value[ key ] = $().cena.store( key );
			})
		;
		return all_value;
	};
	// +----------------------------------------------------------+
	$.fn.cena.store = function( key, val ) {
		if( typeof( cenaStoreTypes[ key ] ) == 'undefined' ) {
			var type = 'string';
		}
		else {
			var type = cenaStoreTypes[ key ];
		}
		return $().cena.storeByType( key, val, type );
	};
	// +----------------------------------------------------------+
	$.fn.cena.storeByType = function( key, val, type ) {
		type.toLowerCase();
		if( type == 'int' ) {
			return $().cena.storeInt( key, val );
		}
		else
		if( type == 'boolean' ) {
			return $().cena.storeBoolean( key, val );
		}
		else {
			return $().cena.storeString( key, val );
		}
	};
	// +----------------------------------------------------------+
	$.fn.cena.storeString = function( key, val ) {
		var cenaStoreHeader = 'cenaStorage.';
		if( typeof( localStorage ) == 'undefined' ) {
			$().cena( 'debug', 'store: localStorage is not supported!' );
			return ;
		}
		if( typeof( key ) == 'undefined' ) {
			// hmm, no input. do not know what to do.
			return ;
		}
		else
		if( typeof( val ) == 'undefined' ) {
			// only key exists. get it from local storage.
			val = localStorage.getItem( cenaStoreHeader + key );
		}
		else {
			// key and val exists. save it to local storage.
			localStorage.setItem( cenaStoreHeader + key, val );
		}
		$().cena( 'debug', 'cena.store: ' + cenaStoreHeader + key + ' = ' + val );
		return val;
	};
	// +----------------------------------------------------------+
	$.fn.cena.storeInt = function( key, val ) {
		if( typeof( key ) == 'undefined' ) { // no input. do nothing. 
			return;
		}
		if( typeof( val ) == 'undefined' ) {
			val = $().cena.storeString( key );
			return parseInt( val );
		}
		$().cena.storeString( key, val );
		return val;
	};
	// +----------------------------------------------------------+
	$.fn.cena.storeBoolean = function( key, val ) {
		if( typeof( key ) == 'undefined' ) { // no input. do nothing. 
			return;
		}
		if( typeof( val ) == 'undefined' ) {
			val = $().cena.storeString( key );
			if( val === 'true' ) return true;
			else return false;
		}
		val = !!val; // force to be boolean.
		$().cena.storeString( key, val ); // stored as string ('true' or 'false').
		return val;
	};
	// +----------------------------------------------------------+
	// + cena.getValue method
	$.fn.cena.getValue = function( env_data, name ) {
		if( !name ) return false;
		if( cenaOptions.WORDY > 2 ) $().cena( 'debug', 'cena.getValue: ' + name );
		var map = { set: 'elements', rel: 'relates' };
		var env_entity, action, column, info = name.split( '.' );
		if( typeof( env_data[ name ] ) != 'undefined' ) {
			return env_data[ name ];
		}
		else
		if( info.length == 2 ) {
			action = info[0];
			column = info[1];
		}
		else {
			action = 'set';
			column = info[0];
		}
		env_entity = map[ action ];
		if( typeof( env_data[ env_entity ] ) == 'undefined' ) return '';
		if( typeof( env_data[ env_entity ][ column ] ) != 'undefined' ) {
			return env_data[ env_entity ][ column ];
		}
		return '';
	};
	// +----------------------------------------------------------+
	/** cena.splitCenaName method splits cena_name into
     *	an array of entities, such as scheme, model...
     *	@param string cena_name
     *		cena_name as 'Cena[model][type][scheme]...'
     *	@returns array
     *		returns array of entities of cena_name.
	 */
	$.fn.cena.splitCenaName = function( cena_name ) {
		return cena_name.replace( /]/g, '' ).split( '[' );
	};
	// +----------------------------------------------------------+
	/** cena.getEnvDataFromCenaName method returns
     *	envelope data from cena_name. Note that this
     *	method does not populate relates/elements fields.
     *	
     *	@param string cena_name
     *		cena_name as 'Cena[model][type][scheme]...'
     *	@returns object
     *		returns envelope data objects.
	 */
	$.fn.cena.getEnvDataFromCenaName = function( cena_name ) {
		var env_data = {
				cena_name: cena_name
			};
		var list = $().cena.splitCenaName( cena_name );
		env_data.scheme  = list[0];
		env_data.model   = list[1];
		env_data.type    = list[2];
		env_data.id      = list[3];
		env_data.cena_id = $().cena.getCenaId( env_data );
		return env_data;
	};
	// +----------------------------------------------------------+
	/** cena.getActionFromCenaName method returns
     *	action from cena_name.
     *	
     *	@param string cena_name
     *		cena_name as 'Cena[model][type][scheme]...'
     *	@returns string
     *		returns action of cena_name.
	 */
	$.fn.cena.getActionFromCenaName = function( cena_name ) {
		var list = $().cena.splitCenaName( cena_name );
		return list[4];
	};
	// +----------------------------------------------------------+
	/** cena.getColumnFromCenaName method returns
     *	column from cena_name.
     *	
     *	@param string cena_name
     *		cena_name as 'Cena[model][type][scheme]...'
     *	@returns string
     *		returns column of cena_name.
	 */
	$.fn.cena.getColumnFromCenaName = function( cena_name ) {
		var list = $().cena.splitCenaName( cena_name );
		return list[5];
	};
	// +----------------------------------------------------------+
	/** cena.getCenaId returns cena_id from envelope data.
     *
     *	@param	object	env_data
     *		envelope data. if data do not have cena_id,
     *		creates cena_id from scheme, model, type, and id.
     *	@returns string
     *		returns cena_id string.
	 */
	$.fn.cena.getCenaId = function( env_data ) {
		if( typeof( env_data[ "cena_id" ] ) == 'undefined' ) {
			var cena_id = 
				env_data.scheme + '.' + env_data.model + '.' + 
				env_data.type   + '.' + env_data.id;
		}
		else {
			var cena_id = env_data[ "cena_id" ];
		}
		return cena_id;
	};
	// +----------------------------------------------------------+
	// + cenaBindEnvAndData method
	function cenaBindEnvAndData( envelope, env_data ) 
	{
		if( cenaOptions.WORDY > 3 ) 
			$().cena( 'debug',  'cenaBindEnvAndData for : ' + env_data.cena_id );
		$( cenaOptions.cena_elem, envelope )          // get all cena_elements in the envelope
			.each( function( index, element ) { // loop thru the elements 
				doBind( index, element );
			}
		);
		// +----------------+ 
		function doBind( index, element )
		{
			// get all necessary information first.
			var tag   = element.tagName;
			var type  = $( element ).attr( 'type' );
			var name  = $( element ).attr( 'name' );
			var value     = $().cena.getValue( env_data, name );
			var cena_name = $().cena.getCenaName( env_data, 'set', name );
			var cena_opt  = $().cena.getOptions();
			
			if( tag != 'SPAN' ) {
				// set active element class for bound elements. 
				if( typeof( cena_opt[ 'cena_form_elem' ] ) != 'undefined' ) {
					$( element ).addClass( cena_opt[ 'cena_form_elem' ] );
				}
			}
			if( tag == 'SPAN' ) {
				doBindSpan( element, value, cena_name );
			}
			else 
			if( tag == 'INPUT' ) {
				if( type == 'radio' ) {
					doBindRadio( element, value, cena_name );
				}
				else
				if( type == 'hidden' ) {
					doBindHidden( element, value, cena_name );
				}
				else {
					doBindInput( element, value, cena_name );
				}
			}
			else 
			if( tag == 'SELECT' ) {
				doBindSelect( element, value, cena_name );
			}
			if( cenaOptions.WORDY > 3 ) {
				var name = $( element ).attr( 'name' );
				var value = $().cena.getValue( env_data, name );
				$().cena( 'debug', 'doBind: '
					+ name + ' with "' + value + '"'
					+ '<br />' );
			}
		};
		// +----------------+ 
		function doBindRadio( element, value, cena_name )
		{
			var checked = false;
			if( $( element ).val() == value ) {
				checked = true; 
			}
			if( cenaOptions.bind_type == 'populate' ) {
				if( checked ) $( element ).attr( { checked: 'checked' } );
				$( element ).attr( 'name',  cena_name );
			}
			else
			if( cenaOptions.bind_type == 'replace' ) {
				if( checked ) {
					var text = $( element ).parent().text();
					$( element ).parent().replaceWith( text );
				}
				else {
					$( element ).parent().replaceWith( '' );
				}
			}
		};
		// +----------------+ 
		function doBindInput( element, value, cena_name )
		{
			if( cenaOptions.bind_type == 'populate' ) {
				$( element ).val( value );
				$( element ).attr( 'name',  cena_name );
			}
			else
			if( cenaOptions.bind_type == 'replace' ) {
				$( element ).replaceWith( value );
			}
		};
		// +----------------+ 
		function doBindHidden( element, value, cena_name )
		{
			// do not replace for hidden elements.
			$( element ).val( value );
			$( element ).attr( 'name',  cena_name );
		};
		// +----------------+ 
		function doBindSpan( element, value, cena_name )
		{
			if( cenaOptions.bind_type == 'populate' ) {
				$( element ).append( value );
			}
			else
			if( cenaOptions.bind_type == 'replace' ) {
				$( element ).replaceWith( value );
			}
		}
		// +----------------+ 
		function doBindSelect( element, value, cena_name )
		{
			if( cenaOptions.bind_type == 'populate' ) {
				$( element ).val( value );
				$( element ).attr( 'name',  cena_name );
			}
			else
			if( cenaOptions.bind_type == 'replace' ) {
				var named_value = $( element ).children( '[value=' + value + ']' ).text();
				$( element ).replaceWith( named_value );
			}
		};
		// +----------------+ 
	};

}) ( jQuery );
// -------------------- jqCena Plug-In ----------------------
