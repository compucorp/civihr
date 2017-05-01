var page = require('../page');

module.exports = (function () {
  return page.extend({

    /**
     * Defines that the tab is ready when the a specific selector is visible
     * @return {boolean}
     */
    ready: function () {
      return this.casper.visible(this.readySelector);
    }
  });
})();
