( function() {
	'use strict';

	/**
	 * Send an AJAX request to delete all xAPI data.
	 * @param {string} wpAJAXurl - URL for AJAX call.
	 */
	var sendAJAX = function( wpAJAXurl ) {
		jQuery.ajax(
			{
				url: wpAJAXurl,
				type: 'post',
				data: {
					action: 'h5pxapikatchu_delete_data',
					nonce: h5pxapikatchuDataTable.nonce
				},
				success: function( response ) {
					if ( '"done"' === response ) {
						location.reload();
					} else {
						alert( h5pxapikatchuDataTable.errorMessage );
					}
				},
				error: function() {
					alert( h5pxapikatchuDataTable.errorMessage );
				}
			}
		);
	};

	/**
	 * Create a button that shows a confirmation dialog before acting.
	 * @param {string} label    The button label.
	 * @param {string} question The confirmation question.
	 * @param {Function} action Called when the user confirms.
	 * @return {Element} DOM anchor element.
	 */
	var createButton = function( label, question, action ) {
		var anchor = document.createElement( 'a' );
		anchor.classList.add( 'btn', 'btn-default', 'buttons-csv', 'buttons-html5' );
		anchor.setAttribute( 'tabindex', '0' );
		anchor.setAttribute( 'aria-controls', 'h5pxapikatchu-data-table' );
		anchor.setAttribute( 'href', '#' );
		anchor.innerHTML = '<span>' + label + '</span>';

		anchor.addEventListener(
			'click',
			function( e ) {
				e.preventDefault();
				var choice = confirm( question );
				if ( true === choice ) {
					action();
				}
				this.blur();
			}
		);

		return anchor;
	};

	/**
	 * Create a download button that triggers a full-dataset CSV export via
	 * a hidden form POST (so the browser handles it as a file download).
	 * @param {string} label The button label.
	 * @return {Element} DOM anchor element.
	 */
	var createDownloadButton = function( label ) {
		var anchor = document.createElement( 'a' );
		anchor.classList.add( 'btn', 'btn-default', 'buttons-csv', 'buttons-html5' );
		anchor.setAttribute( 'tabindex', '0' );
		anchor.setAttribute( 'aria-controls', 'h5pxapikatchu-data-table' );
		anchor.setAttribute( 'href', '#' );
		anchor.innerHTML = '<span>' + label + '</span>';

		anchor.addEventListener(
			'click',
			function( e ) {
				e.preventDefault();

				var form    = document.createElement( 'form' );
				form.method = 'post';
				form.action = h5pxapikatchuDataTable.wpAJAXurl;

				var addField = function( name, value ) {
					var field   = document.createElement( 'input' );
					field.type  = 'hidden';
					field.name  = name;
					field.value = value;
					form.appendChild( field );
				};

				addField( 'action', 'h5pxapikatchu_download_table_data' );
				addField( 'nonce', h5pxapikatchuDataTable.nonceDownloadTableData );

				document.body.appendChild( form );
				form.submit();
				document.body.removeChild( form );

				this.blur();
			}
		);

		return anchor;
	};

	jQuery( document ).ready(
		function() {
			var datatableParams = {
				'dom': 'B<"h5pxapikatchu-data-table-top-bar"lf>rt<"h5pxapikatchu-data-table-bottom-bar"ip>',
				'columnDefs': [{
					'visible': false,
					'targets': h5pxapikatchuDataTable.columnsHidden.map(
						function( id ) {
							return parseInt( id );
						}
					)
				}],
				'buttons': [{
					'extend': 'colvis',
					'text': h5pxapikatchuDataTable.buttonLabelColumnVisibility
				}],
				'language': h5pxapikatchuDataTable.languageData,
				'serverSide': true,
				'processing': true,
				'ajax': {
					'url': h5pxapikatchuDataTable.wpAJAXurl,
					'type': 'POST',
					'data': function( d ) {
						d.action = 'h5pxapikatchu_get_table_data';
						d.nonce  = h5pxapikatchuDataTable.nonceGetTableData;
					}
				},
				'initComplete': function() {
					var api         = this.api();
					var buttonGroup = document.getElementsByClassName( 'dt-buttons btn-group' )[0];

					// Add delete button if allowed
					if ( buttonGroup && '1' === h5pxapikatchuDataTable.userCanDeleteResults ) {
						buttonGroup.appendChild(
							createButton(
								h5pxapikatchuDataTable.buttonLabelDelete,
								h5pxapikatchuDataTable.dialogTextDelete,
								function() { sendAJAX( h5pxapikatchuDataTable.wpAJAXurl ); }
							)
						);
					}

					// Add download button if allowed
					if ( buttonGroup && '1' === h5pxapikatchuDataTable.userCanDownloadResults ) {
						buttonGroup.appendChild(
							createDownloadButton( h5pxapikatchuDataTable.buttonLabelDownload )
						);
					}

					// Fetch distinct column values from the server and build dropdowns
					jQuery.ajax(
						{
							url:  h5pxapikatchuDataTable.wpAJAXurl,
							type: 'POST',
							data: {
								action: 'h5pxapikatchu_get_column_options',
								nonce:  h5pxapikatchuDataTable.nonceGetColumnOptions
							},
							success: function( response ) {
								if ( ! response || ! response.success || ! response.data ) {
									return;
								}
								api.columns().every(
									function( colIndex ) {
										var column  = this;
										var options = response.data[ colIndex ] || [];
										var select  = jQuery( '<select><option value=""></option></select>' )
										.appendTo( jQuery( column.footer() ).empty() )
										.on(
											'change',
											function() {
												column.search( jQuery( this ).val() ).draw();
											}
										);
										jQuery.each(
											options,
											function( i, val ) {
												if ( null !== val && '' !== val ) {
													select.append(
														'<option value="' + jQuery( '<span>' ).text( val ).html() + '">' +
														jQuery( '<span>' ).text( val ).html() +
														'</option>'
													);
												}
											}
										);
									}
								);
							}
						}
					);
				}
			};

			jQuery( '#' + h5pxapikatchuDataTable.classDataTable ).DataTable( datatableParams );
		}
	);
} () );
