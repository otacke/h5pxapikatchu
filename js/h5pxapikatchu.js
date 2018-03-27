/* globals jQuery, debugEnabled, captureAllH5pContentTypes, wpAJAXurl, h5pContentTypes */
// Those globals are passed by PHP, H5P is from H5P framework
var H5P = H5P || {};

( function () {
	'use strict';

	/**
	 * Send an AJAX request to insert xAPI data.
	 * @param {string} wpAJAXurl - URL for AJAX call.
	 * @param {Object} xapi - JSON object with xAPI data.
	 */
 	const sendAJAX = function ( wpAJAXurl, xapi ) {
		jQuery.ajax( {
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
	const handleXAPI = function( event ) {
		if ( debugEnabled === '1' ) {
			console.log( event.data.statement );
		}
		// Retrieve id number from object URL
		const regex = new RegExp( '[?&]id(=([^&#]*)|&|#|$)' );
		const id = regex.exec( event.data.statement.object.id )[2];

		if ( captureAllH5pContentTypes === '1' || h5pContentTypes.includes( id ) ) {
			sendAJAX( wpAJAXurl, event.data.statement );
		}
	};

	/**
	 * Add xAPI listeners to all H5P instances that can trigger xAPI.
	 */
	document.onreadystatechange = function () {
		// Add xAPI EventListener if H5P content is present
		if ( document.readyState === 'complete' ) {
			const iframes = document.getElementsByTagName( 'iframe' );
			for ( var i = 0; i < iframes.length; i++ ) {
				var contentWindow = iframes[i].contentWindow;
				try {
					if ( contentWindow.H5P && contentWindow.H5P.externalDispatcher ) {
						contentWindow.H5P.externalDispatcher.on( 'xAPI', handleXAPI.bind( event ));
					}
				}
				catch ( error ) {
					console.log( error );
				}
			}
		}
	};
} ) ();
