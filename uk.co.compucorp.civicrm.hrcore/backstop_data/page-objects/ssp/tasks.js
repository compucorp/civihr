var modal = require('./../page');

module.exports = (function () {
  return modal.extend({

    /**
     * Opens Completed tasks modal
     */
    openCompletedTasksModal: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.pane-views-tasks-block a.show-complete-tasks');
        casper.waitWhileVisible('.loading-spinner');
        casper.waitUntilVisible('.view-Tasks');
      });
    },

    /**
     * Opens Create New Task modal
     */
    openCreateNewTaskModal: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.create-new-task');
        casper.waitUntilVisible('#civihr-employee-portal-civi-tasks-form');
      });
    }
  });
})();
