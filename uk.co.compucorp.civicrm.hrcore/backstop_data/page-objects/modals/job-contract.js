var modal = require('./modal');

module.exports = (function () {
  return modal.extend({

    /**
     * Selects the tab with the given title
     *
     * @param  {string} tabTitle
     */
    selectTab: function (tabTitle) {
      var casper = this.casper;

      casper.then(function () {
        casper.clickLabel(tabTitle, 'a');
      });
    }
  });
})();
