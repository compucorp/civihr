var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = (function () {
  var taskSelector = '.ct-list-task > li:nth-child(1)';
  var editableSelectors = {
    assigned: '[editable-ui-select="task.assignee_contact_id[0]"]',
    date: '[editable-bsdate="task.activity_date_time"]',
    subject: '[editable-text="task.subject"]',
    target: '[editable-ui-select="task.target_contact_id[0]"]'
  };

  return page.extend({

    /**
     * Shows the assignment modal
     *
     * @return {Promise} resolves with the assignment modal page object
     */
    addAssignment: function () {
      var casper = this.casper;

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click('a[ng-click*="modalAssignment"]');
          resolve(this.waitForModal('assignment'));
        }.bind(this));
      }.bind(this));
    },

    /**
     * Shows the task modal
     *
     * @return {Promise} resolves with the task modal page object
     */
    addTask: function () {
      var casper = this.casper;

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click('a[ng-click*="itemAdd"]');
          resolve(this.waitForModal('task'));
        }.bind(this));
      }.bind(this));
    },

    /**
     * Opens the advanced filters
     *
     * @return {object}
     */
    advancedFilters: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('a[ng-click*="isCollapsed.filterAdvanced"]');
        casper.wait(500);
      });

      return this;
    },

    /**
     * Shows the given edit-in-place field
     *
     * @param {string} fieldName
     * @return {object}
     */
    inPlaceEdit: function (fieldName) {
      var casper = this.casper;

      casper.then(function () {
        casper.click(editableSelectors[fieldName]);
        casper.wait(200);
      });

      return this;
    },

    /**
     * Opens the first task of the list
     *
     * @return {Promise} resolves with the task modal page object
     */
    openTask: function () {
      var casper = this.casper;

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click(taskSelector + ' .task-title > a[ng-click*="modalTask"]')
          resolve(this.waitForModal('task'));
        }.bind(this));
      }.bind(this));
    },

    /**
     * Shows the "select dates" filter
     */
    selectDates: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.ct-select-dates');
        casper.wait(500);
      });
    },

    /**
     * Expands the "show more" area of the first task of the list
     *
     * @return {object}
     */
    showMore: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(taskSelector + ' a[ng-click*="isCollapsed"]');
        casper.waitUntilVisible(taskSelector + ' article', function () {
          casper.wait(500);
        });
      });

      return this;
    },

    /**
     * Shows the dropdown of the actions available on any given task
     *
     * @return {object}
     */
    taskActions: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(taskSelector + ' .ct-context-menu-toggle');
      });

      return this;
    },

    /**
     * Waits until the specified select is visible on the page
     */
    waitForReady: function () {
      var casper = this.casper;

      casper.waitUntilVisible('.ct-container-inner', function () {
        casper.wait(300);
      });
    }
  });
})();
