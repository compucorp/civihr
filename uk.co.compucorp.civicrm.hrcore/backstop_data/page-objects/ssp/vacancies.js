var modal = require('./../page');

module.exports = (function () {
  return modal.extend({

    /**
     * Opens More Details section
     *
     * @return {object}
     */
    showMoreDetails: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.fieldset-title');
        casper.wait(2000);
      }.bind(this));

      return this;
    }
  });
})();
