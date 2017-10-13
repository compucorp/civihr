var tab = require('../tab');

module.exports = tab.extend({
  readySelector: '.chr_leave-report__table',
  tabTitle: 'Report',

  /**
   * Open the report section with the given name
   *
   * @param  {string} sectionName
   * @return {object}
   */
  openSection: function (sectionName) {
    var casper = this.casper;

    casper.then(function () {
      casper.click('[ng-click="report.toggleSection(\'' + sectionName + '\')"]');
      casper.waitUntilVisible('.table-nested');
    });

    return this;
  },

  /**
   * Show the actions of the first leave request available
   *
   * @return {object}
   */
  showActions: function () {
    var casper = this.casper;

    casper.then(function () {
      casper.click('.table-nested .dropdown-toggle');
    });

    return this;
  }
});
