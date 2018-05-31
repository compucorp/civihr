const Page = require('./page');

const taskSelector = '.ct-list-task > li:nth-child(1)';
const editableSelectors = {
  assigned: '[editable-ui-select="task.assignee_contact_id[0]"]',
  date: '[editable-bsdate="task.activity_date_time"]',
  subject: '[editable-text="task.subject"]',
  target: '[editable-ui-select="task.target_contact_id[0]"]'
};

module.exports = class Tasks extends Page {
  /**
   * Shows the assignment modal
   *
   * @return {Object} the assignment modal page object
   */
  async addAssignment () {
    await this.puppet.click('a[ng-click*="modalAssignment"]');

    return this.waitForModal('assignment');
  }

  /**
   * Shows the task modal
   *
   * @return {Object} the task modal page object
   */
  async addTask () {
    await this.puppet.click('a[ng-click*="itemAdd"]');

    return this.waitForModal('task');
  }

  /**
   * Opens the advanced filters
   */
  async advancedFilters () {
    await this.puppet.click('a[ng-click*="isCollapsed.filterAdvanced"]');
    await this.puppet.waitFor(500);
  }

  /**
   * Shows the given edit-in-place field
   *
   * @param {string} fieldName
   */
  async inPlaceEdit (fieldName) {
    await this.puppet.click(editableSelectors[fieldName]);
    await this.puppet.waitFor(200);
  }

  /**
   * Opens the first task of the list
   *
   * @return {Object} the task modal page object
   */
  async openTask () {
    await this.puppet.click(taskSelector + ' .task-title > a[ng-click*="modalTask"]');
    await this.puppet.waitFor('.spinner', { hidden: true });

    return this.waitForModal('task');
  }

  /**
   * Shows the "select dates" filter
   */
  async selectDates () {
    await this.puppet.click('.ct-select-dates');
    await this.puppet.waitFor(500);
  }

  /**
   * Expands the "show more" area of the first task of the list
   */
  async showMore () {
    await this.puppet.click(taskSelector + ' a[ng-click*="isCollapsed"]');
    await this.puppet.waitFor(taskSelector + ' article', { visible: true });
    await this.puppet.waitFor(500);
  }

  /**
   * Shows the dropdown of the actions available on any given task
   */
  async taskActions () {
    await this.puppet.click(taskSelector + ' .ct-context-menu-toggle');
  }

  /**
   * Waits until the specified select is visible on the page
   */
  async waitForReady () {
    await this.puppet.waitFor('.ct-container-inner', { visible: true });
    await this.puppet.waitFor(300);
  }
};
