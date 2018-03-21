var modal = require('./modal');

module.exports = modal.extend({
  /**
   * Opens a date picker
   *
   * @return {object}
   */
  pickDate: function () {
    this.chromy.click(this.modalRoot + ' [ng-model="task.activity_date_time"]');
    this.chromy.waitUntilVisible('.uib-datepicker-popup');

    return this;
  },

  /**
   * Shows a given field
   *
   * @param  {string} fieldName
   * @return {object}
   */
  showField: function (fieldName) {
    this.chromy.click(this.modalRoot + ' a[ng-click*="showField' + fieldName + '"]');

    return this;
  },

  /**
   * Selects the task's assignee
   *
   * @return {object}
   */
  selectAssignee: function () {
    this.chromy.click(this.modalRoot + ' [ng-model="task.assignee_contact_id[0]"] .ui-select-match');
    this.chromy.waitUntilVisible('.select2-with-searchbox:not(.select2-display-none)');

    return this;
  },

  /**
   * Select the task type
   *
   * @return {object}
   */
  selectType: function () {
    this.chromy.click(this.modalRoot + ' [ng-model="task.activity_type_id"] .ui-select-match');
    this.chromy.waitUntilVisible('.select2-with-searchbox:not(.select2-display-none)');

    return this;
  }
});
