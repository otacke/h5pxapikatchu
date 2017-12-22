// Global variable of H5P framework
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
			},
			success: function ( response ) {}
		});
	};

	document.onreadystatechange = function () {
		// Add xAPI EventListener if H5P content is present
		if ( document.readyState === 'complete' && H5P && H5P.externalDispatcher ) {
			H5P.externalDispatcher.on( 'xAPI', function ( event ) {
				// debug_enabled passed by PHP
				if ( debug_enabled === '1' ) {
					console.log( event.data.statement );
				}
				// Retrieve id number from object URL
				const regex = new RegExp( '[?&]id(=([^&#]*)|&|#|$)' );
				const id = regex.exec( event.data.statement.object.id )[2];

				// captureAllH5pContentTypes and h5pContentTypes passed by PHP
				if ( captureAllH5pContentTypes === '1' || h5pContentTypes.includes( id ) ) {
					// wpAJAXurl passed by PHP
					sendAJAX( wpAJAXurl, event.data.statement );
				}
			} );
		}
	};
} ) ();
