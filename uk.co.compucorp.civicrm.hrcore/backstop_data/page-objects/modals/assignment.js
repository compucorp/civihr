/* global jQuery */

var modal = require('./modal');

module.exports = modal.extend({

  /**
   * Clicks the "add document" button
   *
   * @return {Object}
   */
  addDocument: function () {
    this.chromy.click(this.modalRoot + ' a[ng-click="addActivity(documentList)"]');

    return this;
  },

  /**
   * Clicks the "add task" button
   *
   * @return {Object}
   */
  addTask: function () {
    this.chromy.click(this.modalRoot + ' a[ng-click="addActivity(taskList)"]');

    return this;
  },

  /**
   * Opens a date picker
   *
   * @return {Object}
   */
  pickDate: function () {
    this.chromy.click(this.modalRoot + ' [ng-model="assignment.dueDate"]');
    this.chromy.waitUntilVisible('.uib-datepicker-popup');

    return this;
  },

  /**
   * Selects an assignment type, so that the rest of the modal is shown
   *
   * @return {Object}
   */
  selectType: function () {
    this.chromy.evaluate(function (modalRoot) {
      var select = document.querySelector(modalRoot + ' select[name="assignment"]');

      select.selectedIndex = 2;

      jQuery(select).change();
    }, [this.modalRoot]);
    this.chromy.wait(500);

    return this;
  }
});
