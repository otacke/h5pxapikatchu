var H5P = H5P || {};

(function () {
  'use strict';

  /**
   * Send an AJAX request to insert xAPI data.
   * @param {string} wpAJAXurl - URL for AJAX call.
   * @param {Object} xapi - JSON object with xAPI data.
   */
  var sendAJAX = function (wpAJAXurl, xapi) {
    jQuery.ajax({
      url: wpAJAXurl,
      type: 'post',
      data: {
        action: 'h5pxapikatchu_insert_data',
        xapi: JSON.stringify(xapi)
      },
      success: function (response) {}
    });
  };

  document.onreadystatechange = function () {
    // Add xAPI EventListener if H5P content is present
    if (document.readyState === 'complete' && H5P && H5P.externalDispatcher) {
      H5P.externalDispatcher.on('xAPI', function (event) {

        // TODO: make optional in WP_OPTIONS
        console.log(event.data.statement);

        sendAJAX(wpAJAXurl, event.data.statement);
      });
    }
  };
})();
