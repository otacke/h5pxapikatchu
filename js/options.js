/* jshint esversion: 6 */

( function () {
	'use strict';

	let toggleCTS = function () {
		let contentTypeSelectors = document.getElementsByClassName('h5pxapikatchu-content-type-selector');

		if ( contentTypeSelectors.length > 0 ) {
			for ( let i = 0; i < contentTypeSelectors.length; i++ ) {
				// TODO: This might lead to complaints, because diabled fields are not checked in forms
				contentTypeSelectors[i].disabled = !contentTypeSelectors[i].disabled;
			}
		}
	}

	document.onreadystatechange = function () {
		if ( document.readyState !== 'interactive') {
			return;
		};

		let captureAllFlag = document.getElementById('h5pxapikatchu_capture_all_content_types');
		if ( captureAllFlag !== null ) {
			// State from options retrieved from database
			if ( captureAllFlag.checked === true ) {
				toggleCTS();
			}

			captureAllFlag.addEventListener( 'click', function () {
				toggleCTS();
			} );
		}
	};
} ) ();
