(function ($) {
  'use strict';
  var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;

  /*
  This plugin creates MutationObserver which listens to all changes to DOM Node.
  If change has been done to it's attributes callback will be called.

  @param {function} callback - to be called when attributes change
  @returns {Array} - list of all observed elements
  */
  $.fn.attrchange = function (callback) {
    if (MutationObserver) {
      var options = {
        subtree: false,
        attributes: true
      };

      var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (e) {
          callback.call(e.target, e);
        });
      });

      return this.each(function () {
        observer.observe(this, options);
      });
    }
  }
})(CRM.$);
