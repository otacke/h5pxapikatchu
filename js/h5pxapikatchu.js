var H5P = H5P || {};

( function() {
	'use strict';

	/**
	 * Send an AJAX request to insert xAPI data.
	 * @param {string} wpAJAXurl - URL for AJAX call.
	 * @param {Object} xapi - JSON object with xAPI data.
	 */
	var sendAJAX = function( wpAJAXurl, xapi ) {
		jQuery.ajax({
			url: wpAJAXurl,
			type: 'post',
			data: {
				action: 'insert_data',
				xapi: JSON.stringify( xapi )
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

		if ( '1' === debugEnabled ) {
			console.log( event.data.statement );
		}

		if ( '1' === captureAllH5pContentTypes || h5pContentTypes.includes( id ) ) {
			sendAJAX( wpAJAXurl, event.data.statement );
		}
	};

	/**
	 * Add xAPI listeners to all H5P instances that can trigger xAPI.
	 */
	document.onreadystatechange = function() {
		var iframes = document.getElementsByTagName( 'iframe' );
		var i;
		var contentWindow;
		var h5pDiv;

		// Add xAPI EventListener if H5P content is present
		if ( 'complete' === document.readyState ) {
			for ( i = 0; i < iframes.length; i++ ) {
				contentWindow = iframes[i].contentWindow;
				try {
					if ( contentWindow.H5P && contentWindow.H5P.externalDispatcher ) {
						contentWindow.H5P.externalDispatcher.on( 'xAPI', handleXAPI );
					}
				} catch ( error ) {
					console.log( error );
				}
			}

			// Add listener if DIVs are used.
			h5pDiv = document.getElementsByClassName( 'h5p-content' );
			if ( 0 !== h5pDiv.length ) {
				try {
					if ( H5P && H5P.externalDispatcher ) {
						H5P.externalDispatcher.on( 'xAPI', handleXAPI );
					}
				} catch ( error ) {
					console.log( error );
				}
			}
		}
	};
}  () );
