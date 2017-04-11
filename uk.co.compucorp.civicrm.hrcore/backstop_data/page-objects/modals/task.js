var modal = require('./modal');

module.exports = (function () {
  return modal.extend({

    /**
     * Opens a date picker
     *
     * @return {object}
     */
    pickDate: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(this.modalRoot + ' [ng-model="task.activity_date_time"]');
        casper.waitUntilVisible('.uib-datepicker-popup');
      }.bind(this));

      return this;
    },

    /**
     * Shows a given field
     *
     * @param  {string} fieldName
     * @return {object}
     */
    showField: function (fieldName) {
      var casper = this.casper;

      casper.then(function () {
        casper.click(this.modalRoot + ' a[ng-click*="showField' + fieldName + '"]');
      }.bind(this));

      return this;
    },

    /**
     * Selects the task's assignee
     *
     * @return {object}
     */
    selectAssignee: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(this.modalRoot + ' [ng-model="task.assignee_contact_id[0]"] .ui-select-match');
        casper.waitUntilVisible('.select2-with-searchbox');
      }.bind(this));

      return this;
    },

    /**
     * Select the task type
     *
     * @return {object}
     */
    selectType: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(this.modalRoot + ' [ng-model="task.activity_type_id"] .ui-select-match');
        casper.waitUntilVisible('.select2-with-searchbox');
      }.bind(this));

      return this;
    }
  });
})();
