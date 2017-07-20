var modal = require('./../page');

module.exports = (function () {
  return modal.extend({

    /**
     * Shows a modal
     *
     * @param {string} btnSelector
     * @param {string} waitUntilSelector
     * @return {object}
     */
    showModal: function (btnSelector, waitUntilSelector) {
      var casper = this.casper;

      casper.then(function () {
        casper.click(btnSelector);
        casper.waitUntilVisible(waitUntilSelector);
      }.bind(this));

      return this;
    },

    /**
     * Opens the datepicker
     *
     * @return {object}
     */
    openDatePicker: function () {
      var casper = this.casper;

      this.showModal('[href="/absence_request/nojs/credit"]', '.modal-civihr-custom__section');
      casper.then(function () {
        casper.wait(2000);
        casper.evaluate(function () {
          jQuery('#edit-absence-request-date-from-datepicker-popup-0').focus();
          jQuery('#edit-absence-request-date-from-datepicker-popup-0').datepicker("show");
        });
      }.bind(this));

      return this;
    }
  });
})();
