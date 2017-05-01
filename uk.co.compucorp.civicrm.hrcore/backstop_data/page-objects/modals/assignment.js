var modal = require('./modal');

module.exports = (function () {
  return modal.extend({

    /**
     * Clicks the "add document" button
     *
     * @return {object}
     */
    addDocument: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(this.modalRoot + ' a[ng-click="addActivity(documentList)"]');
      }.bind(this));

      return this;
    },

    /**
     * Clicks the "add task" button
     *
     * @return {object}
     */
    addTask: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(this.modalRoot + ' a[ng-click="addActivity(taskList)"]');
      }.bind(this));

      return this;
    },

    /**
     * Opens a date picker
     *
     * @return {object}
     */
    pickDate: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(this.modalRoot + ' [ng-model="assignment.dueDate"]');
        casper.waitUntilVisible('.uib-datepicker-popup');
      }.bind(this));

      return this;
    },

    /**
     * Selects an assignment type, so that the rest of the modal is shown
     *
     * @return {object}
     */
    selectType: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.evaluate(function (modalRoot) {
          var select = document.querySelector(modalRoot + ' select[name="assignment"]');

          select.selectedIndex = 2;
          jQuery(select).change();
        }, this.modalRoot);

        casper.wait(500);
      }.bind(this));

      return this;
    }
  });
})();
