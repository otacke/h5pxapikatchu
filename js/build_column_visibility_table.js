/* globals jQuery, h5pxapikatchuClassColVisTable */
// Those globals are passed by PHP
( function () {
	'use strict';

	jQuery( document ).ready( function () {
		jQuery( '#' + h5pxapikatchuClassColVisTable ).DataTable( {
			"dom": "t",
			"paging": false,
			"searching": false,
			"columnDefs": [
				{ "orderable": false, "targets": 0 }
			],
			"columns": [
				{ "width": "min-content" },
				{ "width": "min-content"}
				],
			"autoWidth": false
		} );
	} );
}) ();
