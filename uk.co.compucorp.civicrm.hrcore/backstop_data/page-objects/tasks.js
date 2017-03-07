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
     * [addAssignment description]
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
     * [addTask description]
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
     * [advancedFilters description]
     * @return {[type]} [description]
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
     * [inPlaceEdit description]
     * @return {[type]} [description]
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
     * [openTask description]
     * @return {[type]} [description]
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
     * [selectDates description]
     * @return {[type]} [description]
     */
    selectDates: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.ct-select-dates');
        casper.wait(500);
      });
    },

    /**
     * [showMore description]
     * @return {[type]} [description]
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
     * [taskActions description]
     * @return {[type]} [description]
     */
    taskActions: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(taskSelector + ' .ct-context-menu-toggle');
      });

      return this;
    },

    /**
     * [waitForReady description]
     * @return {[type]} [description]
     */
    waitForReady: function () {
      var casper = this.casper;

      casper.waitUntilVisible('.ct-container-inner', function () {
        casper.wait(300);
      });
    }
  });
})();
