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
        casper.waitUntilVisible('.modal-civihr-custom__footer');
      });
    },
    /**
     * Opens Create New Task modal
     */
    openCreateNewTaskModal: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.clickLabel('Create new task', 'a');
        casper.waitUntilVisible('.modal-civihr-custom__footer');
      });
    }
  });
})();
