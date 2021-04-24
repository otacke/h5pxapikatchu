( function() {
	'use strict';

	jQuery( document ).ready( function() {
		jQuery( '#' + h5pxapikatchuColVisTable.classColVisTable ).DataTable({
			'dom': 't',
			'paging': false,
			'searching': false,
			'columnDefs': [
				{ 'orderable': false, 'targets': 0 }
			],
			'columns': [
				{ 'width': 'min-content' },
				{ 'width': 'min-content'}
				],
			'autoWidth': false
		});
	});
} () );
