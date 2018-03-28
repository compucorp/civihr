var modal = require('./page');

module.exports = modal.extend({
  /**
   * Opens More Details section
   *
   * @return {Object}
   */
  showMoreDetails: function () {
    this.chromy.click('.fieldset-title');
    this.chromy.wait(2000); // wait for animation to complete

    return this;
  }
});
