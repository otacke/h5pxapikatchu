( function () {
	'use strict';

	// jQuery passed by PHP
	jQuery( document ).ready( function () {
		// classDatatable passed by PHP
		jQuery( '#' + classDataTable ).DataTable( {
			dom: 'Bfrtip',
			buttons: [ {
				extend: 'csv',
				// buttonLabel passed by PHP
				text: buttonLabel,
				title: 'h5pxapikatchu-' + new Date().toISOString().substr( 0, 10 )
			} ]
		} );
	} );
}) ();
