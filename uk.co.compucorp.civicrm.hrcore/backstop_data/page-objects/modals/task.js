const Modal = require('./modal');

module.exports = class TaskModal extends Modal {
  /**
   * Opens a date picker
   */
  async pickDate () {
    await this.puppet.click(this.modalRoot + ' [ng-model="task.activity_date_time"]');
    await this.puppet.waitFor('.uib-datepicker-popup', { visible: true });
  }

  /**
   * Shows a given field
   *
   * @param {String} fieldName
   */
  async showField (fieldName) {
    await this.puppet.click(this.modalRoot + ' a[ng-click*="showField' + fieldName + '"]');
  }

  /**
   * Selects the task's assignee
   */
  async selectAssignee () {
    await this.puppet.click(this.modalRoot + ' [ng-model="task.assignee_contact_id[0]"] .ui-select-match');
    await this.puppet.waitFor('.select2-with-searchbox:not(.select2-display-none)', { visible: true });
  }

  /**
   * Select the task type
   */
  async selectType () {
    await this.puppet.click(this.modalRoot + ' [ng-model="task.activity_type_id"] .ui-select-match');
    await this.puppet.waitFor('.select2-with-searchbox:not(.select2-display-none)', { visible: true });
  }
};
