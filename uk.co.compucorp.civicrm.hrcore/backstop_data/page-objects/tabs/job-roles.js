var tab = require('./tab');

module.exports = tab.extend({
  readySelector: '.job-role__tabs',
  tabTitle: 'Job Roles',

  /**
   * Clicks on the delete button
   */
  attemptDelete: function () {
    this.chromy.click('.job-role [ng-click*="removeRole"]');
    this.waitForModal();
  },

  /**
   * Clicks on the edit button of a job role
   *
   * @return {Object}
   */
  edit: function () {
    this.chromy.click('.tab-pane.active .job-role__actions .btn-link[ng-click$="show()"]');
    this.chromy.wait(100);

    return this;
  },

  /**
   * Opens the ui-select with the given name
   *
   * @param  {String} name
   * @return {Object}
   */
  openDropdown: function (name) {
    var common = 'jobroles.editData[job_roles_data.id]';

    this.chromy.click('[ng-model="' + common + '[\'' + name + '\']"] > a');
    this.chromy.wait(100);

    return this;
  },

  /**
   * Show the form for adding a new job role
   */
  showAddNew: function () {
    this.chromy.click('.btn-primary[ng-click*="jobroles.addNewRole()"]');
  },

  /**
   * Changes active tab
   *
   * @param  {String} tabName
   * @return {Object}
   */
  switchToTab: function (tabName) {
    this.chromy.click('[heading="' + tabName + '"] > a');

    return this;
  }
});
