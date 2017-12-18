/* jshint esversion: 6 */

( function () {
	'use strict';

	const $ = jQuery;
	const CLASS_DATATABLE = '#h5pxapikatchu-data-table';

	$( document ).ready( function() {
		$( CLASS_DATATABLE ).DataTable( {
			dom: 'Bfrtip',
			buttons: [ {
				extend: 'csv',
				// TODO: get localizable label from PHP
				text: 'DOWNLOAD',
				title: 'h5pxapikatchu-' + new Date().toISOString().substr(0, 10)
			} ]
		} );
	} );
}) ();
