const Modal = require('./modal');

module.exports = class DocumentModal extends Modal {
  /**
   * Opens the "due date" datepicker
   */
  async pickDueDate () {
    await this.puppet.click(this.modalRoot + ' [ng-model="documentModal.document.activity_date_time"]');
    await this.puppet.waitFor('.uib-datepicker-popup', { visible: true });
  }

  /**
   * Shows the given field
   *
   * @param {String} fieldName
   */
  async showField (fieldName) {
    await this.puppet.click(this.modalRoot + ' a[ng-click*="show' + fieldName + 'Field"]');
  }

  /**
   * Selects an assignee for the document
   */
  async selectAssignee () {
    await this.puppet.click(this.modalRoot + ' [ng-model="documentModal.document.assignee_contact"] .ui-select-match');
    await this.puppet.waitFor('.select2-with-searchbox:not(.select2-display-none)', { visible: true });
  }

  /**
   * Selects the type of document
   */
  async selectType () {
    await this.puppet.click(this.modalRoot + ' [ng-model="documentModal.document.activity_type_id"] .ui-select-match');
    await this.puppet.waitFor('.select2-with-searchbox:not(.select2-display-none)', { visible: true });
  }

  /**
   * Opens the given tab
   */
  async showTab (tabName) {
    await this.puppet.click(this.modalRoot + ' a[data-target="#' + tabName.toLowerCase() + 'Tab"]');
    await this.puppet.waitFor(200);
  }
};
