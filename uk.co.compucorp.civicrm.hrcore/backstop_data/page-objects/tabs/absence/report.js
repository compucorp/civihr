const tab = require('../tab');

module.exports = tab.extend({
  readySelector: '.chr_leave-report__table',
  tabTitle: 'Report',

  /**
   * Open the report section with the given name
   *
   * @param  {String} sectionName
   */
  async openSection (sectionName) {
    await this.puppet.click('[ng-click="report.toggleSection(\'' + sectionName + '\')"]');

    // @NOTE when using chromy.waitUntilVisible(selector), it only considers
    // the *first* occurrence of the selector, not *any* occurrence
    // so the "wait for any of occurence of this selector" behaviour had to
    // be achieved manually
    await this.puppet.waitFor(function () {
      const nestedTables = document.querySelectorAll('.table-nested');

      return Array.prototype.some.call(nestedTables, function (table) {
        return table.offsetWidth > 0 && table.offsetHeight > 0;
      });
    });
  },

  /**
   * Show the actions of the first leave request available
   *
   */
  async showActions () {
    await this.puppet.click('.table-nested .dropdown-toggle');
  }
});
