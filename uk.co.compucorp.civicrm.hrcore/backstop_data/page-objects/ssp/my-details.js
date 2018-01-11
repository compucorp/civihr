var modal = require('./../page');

module.exports = (function () {
  return modal.extend({

    /**
     * Opens Edit My Details Popup
     *
     * @return {object}
     */
    showEditMyDetailsPopup: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('[href="/my_details/nojs/view"]');
        casper.waitUntilVisible('.modal-civihr-custom__section');
      }.bind(this));

      return this;
    }
  });
})();
