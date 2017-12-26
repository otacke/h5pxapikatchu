/* globals jQuery, classCtsTable */
// Those globals are passed by PHP
( function () {
	'use strict';

	jQuery( document ).ready( function () {
		jQuery( '#' + classCtsTable ).DataTable( {
			"dom": "t",
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
			"order": [[ 3, 'desc' ]],
			"autoWidth": false
		} );
	} );
}) ();
