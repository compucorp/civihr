var modal = require('./page');

module.exports = modal.extend({
  /**
   * Opens More Details section
   *
   * @return {object}
   */
  showMoreDetails: function () {
    this.chromy.click('.fieldset-title');
    this.chromy.wait(2000);

    return this;
  }
});
