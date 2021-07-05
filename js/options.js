( function() {
	'use strict';

	var CLASS_CHECKBOX_CAPTURE_ALL = 'h5pxapikatchu_capture_all_content_types';
	var CLASS_CHECKBOX_SELECT_CONTENT_TYPE = 'h5pxapikatchu-content-type-selector';
	var CLASS_CHECKBOX_SELECT_CONTENT_TYPE_HIDDEN = 'h5pxapikatchu-content-type-selector-hidden';
	var CLASS_CHECKBOX_EMBED_ALLOWED = 'embed_supported';

	/**
	 * Add a hidden dummy element to pass form content of disabled fields
	 * @param {object} cts - Content Type Selector field to add hidden field to.
	 */
	var addHiddenCTS = function( cts ) {
		var hiddenCTS = cts.cloneNode( true );
		hiddenCTS.setAttribute( 'type', 'hidden' );
		hiddenCTS.setAttribute( 'id', hiddenCTS.getAttribute( 'id' )
			.replace( CLASS_CHECKBOX_SELECT_CONTENT_TYPE, CLASS_CHECKBOX_SELECT_CONTENT_TYPE_HIDDEN ) );
		hiddenCTS.removeAttribute( 'class' );
		hiddenCTS.removeAttribute( 'disabled' );
		cts.parentNode.insertBefore( hiddenCTS, cts.nextSibling );
	};

	/**
	 * Remove a hidden dummy element from a form for a content type selector.
	 * @param {object} cts - Content Type Selector field to remove hidden field from.
	 */
	var removeHiddenCTS = function( cts ) {
		var hiddenCts = cts.nextSibling;
		if ( null !== hiddenCts ) {
			hiddenCts.parentNode.removeChild( hiddenCts );
		}
	};

	/**
	 * Toggle the content type selector checkboxes between enabled/disabled
	 */
	var toggleCTS = function() {
		var contentTypeSelectors = document.getElementsByClassName( CLASS_CHECKBOX_SELECT_CONTENT_TYPE );
		var i;

		if ( 0 < contentTypeSelectors.length ) {
			for ( i = 0; i < contentTypeSelectors.length; i++ ) {
				contentTypeSelectors[i].disabled = ! contentTypeSelectors[i].disabled;

				/*
				 * HTML forms do not contain values of checkbox fields that are disabled,
				 * even if they are checked. Readonly doesn't work. Circumvent this here.
				 */
				if ( true === contentTypeSelectors[i].disabled && true === contentTypeSelectors[i].checked ) {
					addHiddenCTS( contentTypeSelectors[i]);
				} else {
					removeHiddenCTS( contentTypeSelectors[i]);
				}
			}
		}
	};

	document.onreadystatechange = function() {
		var captureAllFlag, debugAllowed;

		if ( 'interactive' !== document.readyState ) {
			return;
		};

		captureAllFlag = document.getElementById( CLASS_CHECKBOX_CAPTURE_ALL );
		if ( null !== captureAllFlag ) {

			// Need to toggle if checkbox had been saved checked in database
			if ( true === captureAllFlag.checked ) {
				toggleCTS();
			}

			// Add ClickListener to the "Capture All" checkbox
			captureAllFlag.addEventListener( 'click', function() {
				toggleCTS();
			});
		}

		// Warning for allow embed option
		debugAllowed = document.getElementById( CLASS_CHECKBOX_EMBED_ALLOWED );
		if ( null !== captureAllFlag ) {
			debugAllowed.addEventListener( 'click', function() {
				if ( debugAllowed.checked ) {
					alert( h5pxapikatchuOptions.l10n.embedAllowedWarning );
				}
			});
		}
	};
}  () );
