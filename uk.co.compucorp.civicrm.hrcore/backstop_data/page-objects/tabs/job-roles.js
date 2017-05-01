var tab = require('./tab');

module.exports = (function () {
  return tab.extend({
    readySelector: '.hrjobroles-basic-details',
    tabTitle: 'Job Roles',

    /**
     * Clicks on the delete button
     */
    attemptDelete: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.hrjobroles-list-role-item [ng-click*="removeRole"]');
        this.waitForModal();
      }.bind(this));
    },

    /**
     * Clicks on the edit button of a job role
     *
     * @return {object}
     */
    edit: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.tab-pane.active form > .btn-tab-action');
        casper.wait(100);
      });

      return this;
    },

    /**
     * Opens the ui-select with the given name
     *
     * @param  {string} name
     * @return {object}
     */
    openDropdown: function (name) {
      casper.then(function () {
        var common = 'jobroles.edit_data[job_roles_data.id]';

        casper.click('[ng-model="' + common + '[\'' + name + '\']"] > a');
        casper.wait(100);
      });

      return this;
    },

    /**
     * Show the form for adding a new job role
     */
    showAddNew: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.btn-primary[ng-click*="add_new_role"]');
      });
    },

    /**
     * Changes active tab
     *
     * @param  {string} tabName
     * @return {object}
     */
    switchToTab: function (tabName) {
      var casper = this.casper;

      casper.then(function () {
        casper.clickLabel(tabName);
      });

      return this;
    }
  });
})();
