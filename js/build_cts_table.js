( function () {
	'use strict';

	// jQuery passed by jQuery framework
	jQuery( document ).ready( function () {
		// classCtsTable passed by PHP
		jQuery( '#' + classCtsTable ).DataTable( {
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
