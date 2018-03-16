var modal = require('./../page');

module.exports = (function () {
  return modal.extend({

    /**
     * Opens Edit My Details Popup
     *
     * @return {Object}
     */
    showEditMyDetailsPopup: function () {
      this.chromy.click('[href="/my_details/nojs/view"]');
      this.chromy.waitUntilVisible('.modal-civihr-custom__section');

      return this;
    }
  });
})();
