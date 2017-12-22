/* jshint esversion: 6 */

( function () {
	'use strict';

	const CLASS_DATATABLE = '#h5pxapikatchu-cts-table';

	// jQuery passed by jQuery framework
	jQuery( document ).ready( function() {
		jQuery( CLASS_DATATABLE ).DataTable( {
    	"paging": false,
			"searching": false,
			"columnDefs": [
    		{ "orderable": false, "targets": 0 }
  		],
			"columns": [
    		{ "width": "min-content" },
				null,
				null,
    		{ "width": "min-content"}
				],
			"order": [[ 3, 'asc' ]],
			"autoWidth": false
		} );
	} );
}) ();
