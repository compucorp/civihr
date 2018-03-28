var modal = require('./modal');

module.exports = modal.extend({
  /**
   * Opens the "due date" datepicker
   *
   * @return {Object}
   */
  pickDueDate: function () {
    this.chromy.click(this.modalRoot + ' [ng-model="documentModal.document.activity_date_time"]');
    this.chromy.waitUntilVisible('.uib-datepicker-popup');

    return this;
  },

  /**
   * Shows the given field
   *
   * @param  {String} fieldName
   * @return {Object}
   */
  showField: function (fieldName) {
    this.chromy.click(this.modalRoot + ' a[ng-click*="show' + fieldName + 'Field"]');

    return this;
  },

  /**
   * Selects an assignee for the document
   *
   * @return {Object}
   */
  selectAssignee: function () {
    this.chromy.click(this.modalRoot + ' [ng-model="documentModal.document.assignee_contact"] .ui-select-match');
    this.chromy.waitUntilVisible('.select2-with-searchbox:not(.select2-display-none)');

    return this;
  },

  /**
   * Selects the type of document
   *
   * @return {Object}
   */
  selectType: function () {
    this.chromy.click(this.modalRoot + ' [ng-model="documentModal.document.activity_type_id"] .ui-select-match');
    this.chromy.waitUntilVisible('.select2-with-searchbox:not(.select2-display-none)');

    return this;
  },

  /**
   * Opens the given tab
   *
   * @return {Object}
   */
  showTab: function (tabName) {
    this.chromy.click(this.modalRoot + ' a[data-target="#' + tabName.toLowerCase() + 'Tab"]');
    this.chromy.wait(200);

    return this;
  }
});
