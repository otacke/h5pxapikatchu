let $ = jQuery;

$(document).ready(function() {
  // TODO: comment on ID
  $('#h5pxapikatchu-data-table').DataTable({
    dom: 'Bfrtip',
    buttons: [
      { extend: 'csv', text: 'DOWNLOAD', title: 'h5pxapikatchu-' + new Date().toISOString().substr(0, 10) }
    ]
  });
});
