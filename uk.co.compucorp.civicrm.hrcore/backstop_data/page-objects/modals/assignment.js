/* global jQuery */

var modal = require('./modal');

module.exports = (function () {
  return modal.extend({

    /**
     * Clicks the "add document" button
     *
     * @return {object}
     */
    addDocument: function () {
      this.chromy.click(this.modalRoot + ' a[ng-click="addActivity(documentList)"]');

      return this;
    },

    /**
     * Clicks the "add task" button
     *
     * @return {object}
     */
    addTask: function () {
      this.chromy.click(this.modalRoot + ' a[ng-click="addActivity(taskList)"]');

      return this;
    },

    /**
     * Opens a date picker
     *
     * @return {object}
     */
    pickDate: function () {
      this.chromy.click(this.modalRoot + ' [ng-model="assignment.dueDate"]');
      this.chromy.waitUntilVisible('.uib-datepicker-popup');

      return this;
    },

    /**
     * Selects an assignment type, so that the rest of the modal is shown
     *
     * @return {object}
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
})();
