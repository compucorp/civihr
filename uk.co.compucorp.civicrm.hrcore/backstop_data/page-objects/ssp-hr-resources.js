var modal = require('./../page');

module.exports = modal.extend({
  /**
   * Opens See Resources section
   *
   * @return {object}
   */
  seeResources: function () {
    this.chromy.click('.fieldset-title');
    this.chromy.wait(2000); // wait for animation to complete

    return this;
  }
});
