var modal = require('./modal');

module.exports = modal.extend({
  /**
   * Selects tabs like comments or attachments
   * @param {String} tabName like comments or attachments
   * @return {Object} this object
   */
  selectTab: function (tabName) {
    this.chromy.click('div.chr_leave-request-modal__tab li[heading=\'' + tabName + '\'] a');

    return this;
  }
});
