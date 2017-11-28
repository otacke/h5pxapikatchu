var H5P = H5P || {};

(function() {
  'use strict';
  
  document.onreadystatechange = function () {
    // Add xAPI EventListener if H5P content is present
    if (document.readyState === 'complete') {
      if (H5P && H5P.externalDispatcher) {
        H5P.externalDispatcher.on('xAPI', function (event) {
          console.log(event.data.statement);
        });
      }
    }
  };
})();
