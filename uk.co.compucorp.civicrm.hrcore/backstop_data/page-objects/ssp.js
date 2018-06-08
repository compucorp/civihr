const Page = require('./page');

module.exports = class SSP extends Page {
  /**
   * Waits for the notification badges for tasks and manager leaves to be visible
   *
   * @NOTE these badges do not appear for every user that backstop might be logged in
   * as. To keep things simple for now, in case the badges are absent the exception
   * will be simply caught to avoid the scenarios to fail
   */
  async waitForReady () {
    try {
      await this.puppet.waitFor('tasks-notification-badge .chr_leave_notification', { visible: true });
      await this.puppet.waitFor('manager-notification-badge .chr_leave_notification', { visible: true });
    } catch (ex) {}
  }
};
