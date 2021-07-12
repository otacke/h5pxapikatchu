( function() {
	'use strict';

	/**
	 * Handle deleteData could delete data from database.
	 */
	var handleAJAXDeleteSuccess = function( response ) {
		if ( '"done"' === response ) {
			location.reload();
		} else {
			alert( h5pxapikatchuDataTable.errorMessage );
		}
	};

	/**
	 * Handle getData got data from database.
	 */
	var handleAJAXGetDataSuccess = function( response, callback ) {
		var data;

		response = JSON.parse( response );

		data = response.data.map( function( row ) {
			return Object.values( row ).map( function( value ) {
				return value || '';
			});
		});

		callback({
			data: data,
			recordsTotal: response.recordsTotal,
			recordsFiltered: response.recordsFiltered
		});
	};

	/**
	 * Handle AJAX error.
	 */
	var handleAJAXError = function() {
		alert( h5pxapikatchuDataTable.errorMessage );
	};

	/**
	 * Send an AJAX request to insert xAPI data.
	 * @param {object} params Parameters.
	 * @param {string} params.action Action to perform (WP AJAX endpoint id).
	 * @param {object} [params.payload] Data to pass to AJAX handler.
	 * @param {function} [params.success] Success callback.
	 * @param {function} [params.error] Error callback.
	 */
	var sendAJAX = function( params ) {
		if ( ! params.action ) {
			handleAJAXError();
			return;
		}

		jQuery.ajax({
			url: h5pxapikatchuDataTable.wpAJAXurl,
			type: 'post',
			data: {
				action: params.action,
				payload: params.payload || null
			},
			success: params.success || function() {},
			error: params.error || handleAJAXError
		});
	};

	/**
	 * Delete date from database.
	 */
	var deleteData = function() {
		sendAJAX({
			action: 'h5pxapikatchu_delete_data',
			success: handleAJAXDeleteSuccess,
			error: handleAJAXError
		});
	};

	/**
	 * Get data from database.
	 * @param {object} options Options set by DataTables.
	 * @param {function} callback Callback for DataTables.
	 * @param {object} settings Settings from DataTables.
	 */
	var getData = function( options, callback, settings ) {
		sendAJAX({
			action: 'h5pxapikatchu_get_data',
			payload: JSON.stringify( options ),
			success: function( response ) {
				handleAJAXGetDataSuccess( response, callback );
			},
			error: handleAJAXError
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
				deleteData();
			}
			this.blur();
		});

		return anchor;
	};

	jQuery( document ).ready( function() {
		var datatableParams;
		var footer = document.querySelector( '#h5pxapikatchu-data-table tfoot' );
		if ( footer ) {
			footer.classList.add( 'h5pxapikatchu-no-display' );
		}

		datatableParams = {
			'dom': 'B<"h5pxapikatchu-data-table-top-bar"lf>rt<"h5pxapikatchu-data-table-bottom-bar"ip>',
			'serverSide': true,
			'ajax': getData,
			'columnDefs': [
				{
					'visible': false,
					'targets': h5pxapikatchuDataTable.columnsHidden.map( function( id ) {
						return parseInt( id );
					})
				}
			],
			'buttons': [
				{
					'extend': 'colvis',
					'text': h5pxapikatchuDataTable.buttonLabelColumnVisibility
				}
			],
			'language': h5pxapikatchuDataTable.languageData,
			'initComplete': function() {
				var buttonGroup = document.getElementsByClassName( 'dt-buttons btn-group' )[0];

				if ( footer ) {
					footer.classList.remove( 'h5pxapikatchu-no-display' );
				}

				// Add delete button if allowed to delete
				if ( buttonGroup && '1' === h5pxapikatchuDataTable.userCanDeleteResults ) {
					buttonGroup.appendChild( createButton( h5pxapikatchuDataTable.buttonLabelDelete, h5pxapikatchuDataTable.dialogTextDelete ) );
				}

				// Add drop-down menus for filtering
				this.api().columns().every( function() {
					var column = this;
					var select = jQuery( '<select><option value=""></option></select>' )
						.appendTo( jQuery( column.footer() ).empty() )
						.on( 'change', function() {
							var val = jQuery.fn.dataTable.util.escapeRegex(
								jQuery( this ).val()
							);
							column
								.search( val ? '^' + val + '$' : '', true, false )
								.draw();
							});
					column.data().unique().sort().each( function( d, j ) {
						select.append( '<option value="' + d + '">' + d + '</option>' );
					});
				});
			}
		};

		// Add download button if allowed to download
		if ( '1' === h5pxapikatchuDataTable.userCanDownloadResults ) {
			datatableParams.buttons = datatableParams.buttons || [];
			datatableParams.buttons.push({
				'extend': 'csv',
				'text': h5pxapikatchuDataTable.buttonLabelDownload,
				'title': 'h5pxapikatchu-' + new Date().toISOString().substr( 0, 10 )
			});
		}

		jQuery( '#' + h5pxapikatchuDataTable.classDataTable ).DataTable( datatableParams );

	});
} () );
