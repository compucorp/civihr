var modal = require('./page');

module.exports = modal.extend({
  /**
   * Opens Completed tasks modal
   */
  openCompletedTasksModal: function () {
    this.chromy.click('.pane-views-tasks-block a.show-complete-tasks');
    this.chromy.wait(function () {
      // = CasperJS.waitWhileVisible()
      var dom = document.querySelector('.loading-spinner');

      return dom === null || (dom.offsetWidth <= 0 && dom.offsetHeight <= 0);
    });
    this.chromy.waitUntilVisible('.view-Tasks');
  },

  /**
   * Opens Create New Task modal
   */
  openCreateNewTaskModal: function () {
    this.chromy.click('.create-new-task');
    this.chromy.waitUntilVisible('#civihr-employee-portal-civi-tasks-form');
  },

  /**
   * The page always gives false positives for some reason in Chromy, so we need
   * to wait a couple of seconds for it to "stabilize" before taking the screenshot
   */
  waitForReady: function () {
    this.chromy.wait(2000);
  }
});
