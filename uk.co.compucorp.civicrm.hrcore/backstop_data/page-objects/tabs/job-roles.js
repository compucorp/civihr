const Tab = require('./tab');

module.exports = class JobRolesTab extends Tab {
  constructor () {
    super(...arguments);
    this.readySelector = '.job-role__tabs';
    this.tabTitle = 'Job Roles';
  }

  /**
   * Clicks on the delete button
   */
  async attemptDelete () {
    await this.puppet.click('.job-role [ng-click*="removeRole"]');
    await this.waitForModal();
  }

  /**
   * Clicks on the edit button of a job role
   */
  async edit () {
    await this.puppet.click('.tab-pane.active .job-role__actions .btn-link[ng-click$="show()"]');
    await this.puppet.waitFor(100);
  }

  /**
   * Opens the ui-select with the given name
   *
   * @param {String} name
   */
  async openDropdown (name) {
    const common = 'jobroles.editData[job_roles_data.id]';

    await this.puppet.click('[ng-model="' + common + '[\'' + name + '\']"] > a');
    await this.puppet.waitFor(100);
  }

  /**
   * Show the form for adding a new job role
   */
  async showAddNew () {
    await this.puppet.click('.btn-primary[ng-click*="jobroles.addNewRole()"]');
  }

  /**
   * Changes active tab
   *
   * @param {String} tabName
   */
  async switchToTab (tabName) {
    await this.puppet.click('[heading="' + tabName + '"] > a');
  }
};
