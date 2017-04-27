var modal = require('./modal');

module.exports = (function () {
  return modal.extend({

    /**
     * Selects tabs like comments or attachments
     * @return {Object} this object
     */
    selectTab: function (tabName) {
      var casper = this.casper;

      casper.then(function () {
        casper.click('div.chr_leave-request-modal__tab li[heading=' + tabName + '] a');
      });

      return this;
    }
  });
})();
