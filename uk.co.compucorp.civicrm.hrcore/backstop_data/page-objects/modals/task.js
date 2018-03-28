var modal = require('./modal');

module.exports = modal.extend({
  /**
   * Opens a date picker
   *
   * @return {Object}
   */
  pickDate: function () {
    this.chromy.click(this.modalRoot + ' [ng-model="task.activity_date_time"]');
    this.chromy.waitUntilVisible('.uib-datepicker-popup');

    return this;
  },

  /**
   * Shows a given field
   *
   * @param  {String} fieldName
   * @return {Object}
   */
  showField: function (fieldName) {
    this.chromy.click(this.modalRoot + ' a[ng-click*="showField' + fieldName + '"]');

    return this;
  },

  /**
   * Selects the task's assignee
   *
   * @return {Object}
   */
  selectAssignee: function () {
    this.chromy.click(this.modalRoot + ' [ng-model="task.assignee_contact_id[0]"] .ui-select-match');
    this.chromy.waitUntilVisible('.select2-with-searchbox:not(.select2-display-none)');

    return this;
  },

  /**
   * Select the task type
   *
   * @return {Object}
   */
  selectType: function () {
    this.chromy.click(this.modalRoot + ' [ng-model="task.activity_type_id"] .ui-select-match');
    this.chromy.waitUntilVisible('.select2-with-searchbox:not(.select2-display-none)');

    return this;
  }
});
