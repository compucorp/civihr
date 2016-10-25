(function($) {
  var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;

  $.fn.attrchange = function(callback) {
    if (MutationObserver) {
      var options = {
        subtree: false,
        attributes: true
      };

      var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(e) {
          callback.call(e.target, e);
        });
      });

      return this.each(function() {
        observer.observe(this, options);
      });
    }
  }
})(CRM.$);
