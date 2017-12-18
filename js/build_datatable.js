/* jshint esversion: 6 */

( function () {
	'use strict';

	const CLASS_DATATABLE = '#h5pxapikatchu-data-table';

	// jQuery passed by jQuery framework
	jQuery( document ).ready( function() {
		jQuery( CLASS_DATATABLE ).DataTable( {
			dom: 'Bfrtip',
			buttons: [ {
				extend: 'csv',
				// button_label passed by PHP
				text: button_label,
				title: 'h5pxapikatchu-' + new Date().toISOString().substr(0, 10)
			} ]
		} );
	} );
}) ();
