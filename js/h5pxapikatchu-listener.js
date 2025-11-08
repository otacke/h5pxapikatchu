var H5P = H5P || {};

( function() {
	'use strict';

	/**
	 * Get top DOM Window object.
	 * @param {Window} [startWindow=window] Window to start looking from.
	 * @return {Window|false} Top window.
	 */
	var getTopWindow = function( startWindow ) {
		var sameOrigin;
		startWindow = startWindow || window;

		try {
			sameOrigin = startWindow.parent.location.host === window.location.host;
		} catch ( error ) {
			sameOrigin = false;
		}

		if ( ! sameOrigin ) {
			return false;
		}

		if ( startWindow.parent === startWindow || ! startWindow.parent ) {
			return startWindow;
		}

		return getTopWindow( startWindow.parent );
	};

	/**
	 * Send an AJAX request to insert xAPI data.
	 * @param {string} wpAJAXurl - URL for AJAX call.
	 * @param {Object} xapi - JSON object with xAPI data.
	 */
	var sendAJAX = function( wpAJAXurl, xapi ) {
		H5PxAPIkatchu.jQuery.ajax({
			url: wpAJAXurl,
			type: 'post',
			data: {
				action: 'insert_data',
				xapi: JSON.stringify( xapi ),
				nonce: getNonceFromScriptSource()
			}
		});
	};

	/**
	 * Handle storing of xAPI statements.
	 * @param {Object} event - Event.
	 */
	var handleXAPI = function( event ) {

		// Retrieve id number from object URL
		var regex = new RegExp( '[?&]id(=([^&#]*)|&|#|$)' );
		var id = regex.exec( event.data.statement.object.id )[2];
		id = id.split( '?' )[0];

		if ( '1' === H5PxAPIkatchu.debugEnabled ) {
			console.log( event.data.statement );
		}

		if ( '1' === H5PxAPIkatchu.captureAllH5pContentTypes || -1 !== H5PxAPIkatchu.h5pContentTypes.indexOf( id ) ) {
			sendAJAX( H5PxAPIkatchu.wpAJAXurl, event.data.statement );
		}
	};

	/**
	 * Get hostname.
	 * @param {string} url URL.
	 * @return {string|null} Hostname.
	 */
	var getHostname = function( url ) {
		var matches = url.match( /^https?\:\/\/([^\/?#]+)(?:[\/?#]|$)/i );
		return matches && matches[1];
	};

	var getNonceFromScriptSource = function() {
		const scripts = document.getElementsByTagName('script');
		for ( let i = 0; i < scripts.length; i++ ) {
			const src = scripts[i].src;
			if ( src.includes( 'h5pxapikatchu-listener.js' ) ) {
				const urlParams = new URLSearchParams( src.split( '?' )[1] );
				return urlParams.get( 'nonce' );
			}
		}
		return null;
	}

	// Get environment variables
	var H5PxAPIkatchu;
	var topWindow;

	topWindow = ( window.H5PxAPIkatchu ) ? window : getTopWindow();
	if ( ! topWindow || ! topWindow.H5PxAPIkatchu ) {
		console.warn( 'Could not find H5PxAPIkatchu object, cannot store xAPI statements for some content. Potentially, the plugin directory is not writable by the server.' );
		return;
	}
	H5PxAPIkatchu = topWindow.H5PxAPIkatchu;

	/**
	 * Add xAPI listeners to all H5P instances that can trigger xAPI.
	 */
	document.addEventListener( 'readystatechange', function() {
		// Add xAPI EventListener if H5P content is present
		if ( 'interactive' === document.readyState ) {
      try {
        if ( H5P.externalDispatcher ) {
          H5P.externalDispatcher.on( 'xAPI', handleXAPI );
        }
      } catch ( error ) {
        console.warn( error );
      }
		}
	});
}  () );
