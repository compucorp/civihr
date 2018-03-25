/* global jQuery */

const modal = require('./modal');

module.exports = modal.extend({

  /**
   * Clicks the "add document" button
   */
  async addDocument () {
    await this.puppet.click(this.modalRoot + ' a[ng-click="addActivity(documentList)"]');
  },

  /**
   * Clicks the "add task" button
   */
  async addTask () {
    await this.puppet.click(this.modalRoot + ' a[ng-click="addActivity(taskList)"]');
  },

  /**
   * Opens a date picker
   */
  async pickDate () {
    await this.puppet.click(this.modalRoot + ' [ng-model="assignment.dueDate"]');
    await this.puppet.waitFor('.uib-datepicker-popup', { visible: true });
  },

  /**
   * Selects an assignment type, so that the rest of the modal is shown
   */
  async selectType () {
    await this.puppet.evaluate(function (modalRoot) {
      const select = document.querySelector(modalRoot + ' select[name="assignment"]');

      select.selectedIndex = 2;
      jQuery(select).change();
    }, this.modalRoot);
    await this.puppet.waitFor(500);
  }
});
