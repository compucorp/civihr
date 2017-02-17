var page = require('../page');

module.exports = (function () {
  return page.extend({

    /**
     * [open description]
     * @return {[type]} [description]
     */
    ready: function () {
      return this.casper.visible(this.readySelector);
    }
  });
})();
