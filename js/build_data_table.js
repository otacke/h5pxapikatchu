( function() {
	'use strict';

	/**
	 * Send an AJAX request to insert xAPI data.
	 * @param {string} wpAJAXurl - URL for AJAX call.
	 */
	var sendAJAX = function( wpAJAXurl ) {
		jQuery.ajax({
			url: wpAJAXurl,
			type: 'post',
			data: {
				action: 'delete_data'
			},
			success: function( response ) {
				if ( '"done"' === response ) {
					location.reload();
				} else {
					alert( errorMessage );
				}
			},
			error: function() {
				alert( errorMessage );
			}
		});
	};

	/**
	 * Create a button.
	 * @param string $label The label for the button.
	 * @param string $question The question.
	 * @return object DOM object for the button.
	 */
	var createButton = function( label, question ) {
		var anchor = document.createElement( 'a' );
		anchor.classList.add( 'btn', 'btn-default', 'buttons-csv', 'buttons-html5' );
		anchor.setAttribute( 'tabindex', '0' );
		anchor.setAttribute( 'aria-controls', 'h5pxapikatchu-data-table' );
		anchor.setAttribute( 'href', '#' );
		anchor.innerHTML = '<span>' + label + '</span>';

		anchor.addEventListener( 'click', function() {
			var choice = confirm( question );
			if ( true === choice ) {
				sendAJAX( wpAJAXurl );
			}
			this.blur();
		});

		return anchor;
	};

	jQuery( document ).ready( function() {
		jQuery( '#' + classDataTable ).DataTable({
			'dom': 'Bfrtip',
			'columnDefs': [
				{
					'visible': false,
					'targets': h5pxapikatchuColumnsHidden.map( function( id ) {
						return parseInt( id );
					})
				}
			],
			'buttons': [
				{
					'extend': 'colvis',
					'text': buttonLabelColumnVisibility
				},
				{
					'extend': 'csv',
					'text': buttonLabelDownload,
					'title': 'h5pxapikatchu-' + new Date().toISOString().substr( 0, 10 )
				}
			],
			'language': languageData,
			'initComplete': function() {
				var buttonGroup = document.getElementsByClassName( 'dt-buttons btn-group' )[0];
				buttonGroup.appendChild( createButton( buttonLabelDelete, dialogTextDelete ) );
			}
		});

	});
} () );
