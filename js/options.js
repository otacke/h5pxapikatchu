/* jshint esversion: 6 */

( function () {
	'use strict';

	let toggleCTS = function () {
		let contentTypeSelectors = document.getElementsByClassName('h5pxapikatchu-content-type-selector');

		if ( contentTypeSelectors.length > 0 ) {
			for ( let i = 0; i < contentTypeSelectors.length; i++ ) {
				contentTypeSelectors[i].disabled = !contentTypeSelectors[i].disabled;

				/*
				 * HTML forms do not contain values of checkbox fields that are disabled,
				 * even if they are checked. Readonly doesn't work. Circumvent this here.
				 */
				if ( contentTypeSelectors[i].disabled === true && contentTypeSelectors[i].checked === true) {
					addHiddenCTS( contentTypeSelectors[i] );
				} else {
					removeHiddenCTS( contentTypeSelectors[i] );
				}
			}
		}
	}

	/**
	 * Add a hidden dummy element to pass form content of disabled fields
	 * @param {object} cts - Content Type Selector field to add hidden field to.
	 */
	let addHiddenCTS = function ( cts ) {
		let hiddenCTS = cts.cloneNode(true);
		hiddenCTS.setAttribute( 'type' , 'hidden' );
		hiddenCTS.setAttribute( 'id', hiddenCTS.getAttribute( 'id' ).replace( 'h5p_content_type', 'h5p-hidden-cts' ) );
		hiddenCTS.removeAttribute( 'class' );
		hiddenCTS.removeAttribute( 'disabled' );
		cts.parentNode.insertBefore( hiddenCTS, cts.nextSibling );
	}

	/**
	 * Remove a hidden dummy element from a form for a content type selector.
	 * @param {object} cts - Content Type Selector field to remove hidden field from.
	 */
	let removeHiddenCTS = function ( cts ) {
		let hiddenCts = cts.nextSibling;
		if ( hiddenCts !== null ) {
			hiddenCts.parentNode.removeChild( hiddenCts );
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
