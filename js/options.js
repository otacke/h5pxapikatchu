( function () {
	'use strict';

	const CLASS_CHECKBOX_CAPTURE_ALL = 'h5pxapikatchu_capture_all_content_types';
	const CLASS_CHECKBOX_SELECT_CONTENT_TYPE = 'h5pxapikatchu-content-type-selector';
	const CLASS_CHECKBOX_SELECT_CONTENT_TYPE_HIDDEN = 'h5pxapikatchu-content-type-selector-hidden';

	/**
	 * Toggle the content type selector checkboxes between enabled/disabled
	 */
	const toggleCTS = function () {
		const contentTypeSelectors = document.getElementsByClassName( CLASS_CHECKBOX_SELECT_CONTENT_TYPE );

		if ( contentTypeSelectors.length > 0 ) {
			for ( let i = 0; i < contentTypeSelectors.length; i++ ) {
				contentTypeSelectors[i].disabled = !contentTypeSelectors[i].disabled;
				/*
				 * HTML forms do not contain values of checkbox fields that are disabled,
				 * even if they are checked. Readonly doesn't work. Circumvent this here.
				 */
				if ( contentTypeSelectors[i].disabled === true && contentTypeSelectors[i].checked === true ) {
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
	const addHiddenCTS = function ( cts ) {
		const hiddenCTS = cts.cloneNode( true );
		hiddenCTS.setAttribute( 'type' , 'hidden' );
		hiddenCTS.setAttribute( 'id', hiddenCTS.getAttribute( 'id' )
			.replace( CLASS_CHECKBOX_SELECT_CONTENT_TYPE, CLASS_CHECKBOX_SELECT_CONTENT_TYPE_HIDDEN ) );
		hiddenCTS.removeAttribute( 'class' );
		hiddenCTS.removeAttribute( 'disabled' );
		cts.parentNode.insertBefore( hiddenCTS, cts.nextSibling );
	}

	/**
	 * Remove a hidden dummy element from a form for a content type selector.
	 * @param {object} cts - Content Type Selector field to remove hidden field from.
	 */
	const removeHiddenCTS = function ( cts ) {
		const hiddenCts = cts.nextSibling;
		if ( hiddenCts !== null ) {
			hiddenCts.parentNode.removeChild( hiddenCts );
		}
	}

	document.onreadystatechange = function () {
		if ( document.readyState !== 'interactive') {
			return;
		};

		const captureAllFlag = document.getElementById( CLASS_CHECKBOX_CAPTURE_ALL );
		if ( captureAllFlag !== null ) {
			// Need to toggle if checkbox had been saved checked in database
			if ( captureAllFlag.checked === true ) {
				toggleCTS();
			}

			// Add ClickListener to the "Capture All" checkbox
			captureAllFlag.addEventListener( 'click', function () {
				toggleCTS();
			} );
		}
	};
} ) ();
