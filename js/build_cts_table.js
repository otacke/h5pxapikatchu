( function() {
	'use strict';

	jQuery( document ).ready( function() {
		jQuery( '#' + h5pxapikatchuCtsTable.classCtsTable ).DataTable({
			'dom': 't',
			'paging': false,
			'searching': false,
			'columnDefs': [
				{ 'orderable': false, 'targets': 0 }
			],
			'columns': [
				{ 'width': 'min-content' },
				null,
				null,
				{ 'width': 'min-content'}
				],
			'order': [ [ 3, 'desc' ] ],
			'autoWidth': false
		});
	});
} () );
