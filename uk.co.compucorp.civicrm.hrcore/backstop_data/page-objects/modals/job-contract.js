var modal = require('./modal');

module.exports = (function () {
  return modal.extend({

    /**
     * [selectTab description]
     * @param  {[type]} tabTitle [description]
     * @return {[type]}          [description]
     */
    selectTab: function (tabTitle) {
      var casper = this.casper;

      casper.then(function () {
        casper.clickLabel(tabTitle, 'a');
      });
    }
  });
})();
