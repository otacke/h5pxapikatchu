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
				action: 'h5pxapikatchu_delete_data'
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
		});
	};

	var getData = function( options, callback, settings ) {
		var data, wasFiltered;

		jQuery.ajax({
			url: h5pxapikatchuDataTable.wpAJAXurl,
			type: 'post',
			data: {
				action: 'h5pxapikatchu_get_data',
				options: JSON.stringify( options )
			},
			success: function( response ) {
				response = JSON.parse( response );

				data = response.data.map( function( row ) {
					return Object.values( row ).map( function( value ) {
						return value || '';
					});
				});

				wasFiltered = (
					'' !==  options.search.value ) ||
					options.columns
						.map( function( column ) {
							return column.search.value;
						})
						.some( function( filter ) {
							return  '' !== filter;
						});

				callback({
					data: data,
					recordsTotal: response.recordsTotal,
					recordsFiltered: wasFiltered ? response.data.length : response.recordsTotal
				});
			},
			error: function() {
				alert( h5pxapikatchuDataTable.errorMessage );
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
				sendAJAX( h5pxapikatchuDataTable.wpAJAXurl );
			}
			this.blur();
		});

		return anchor;
	};

	jQuery( document ).ready( function() {
		var datatableParams = {
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
