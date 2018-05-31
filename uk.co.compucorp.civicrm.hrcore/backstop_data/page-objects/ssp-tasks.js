const Page = require('./page');

module.exports = class SSPTasks extends Page {
  /**
   * Opens Completed tasks modal
   */
  async openCompletedTasksModal () {
    await this.puppet.click('.pane-views-tasks-block a.show-complete-tasks');
    await this.puppet.waitFor('.loading-spinner', { hidden: true });
    await this.puppet.waitFor('.view-Tasks', { visible: true });
  }

  /**
   * Opens Create New Task modal
   */
  async openCreateNewTaskModal () {
    await this.puppet.click('.create-new-task');
    await this.puppet.waitFor('#civihr-employee-portal-civi-tasks-form', { visible: true });
  }

  /**
   * The page always gives false positives for some reason in Chrome, so we need
   * to wait a couple of seconds for it to "stabilize" before taking the screenshot
   */
  async waitForReady () {
    await this.puppet.waitFor(4000);
  }
};
