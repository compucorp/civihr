var modal = require('./modal');

module.exports = (function () {
  return modal.extend({

    /**
     * Opens a ui-select dropdown
     *
     * @return {object}
     */
    openDropdown: function (name) {
      casper.then(function () {
        var common = '[ng-model="modalCtrl.selectedData.%{name}"] input';

        casper.click(common.replace('%{name}', name));
        casper.wait(100);
      });

      return this;
    }
  });
})();
