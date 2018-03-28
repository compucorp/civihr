var modal = require('./modal');

module.exports = modal.extend({
  /**
   * Opens a ui-select dropdown
   *
   * @return {Object}
   */
  openDropdown: function (name) {
    var common = '[ng-model="modalCtrl.selectedData.%{name}"] input';

    this.chromy.click(common.replace('%{name}', name));
    this.chromy.wait(100);

    return this;
  }
});
