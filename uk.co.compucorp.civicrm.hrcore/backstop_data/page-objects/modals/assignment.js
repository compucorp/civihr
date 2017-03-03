var modal = require('./modal');

module.exports = (function () {
  return modal.extend({

    /**
     * [addDocument description]
     */
    addDocument: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(this.modalRoot + ' a[ng-click="addActivity(documentList)"]');
      }.bind(this));

      return this;
    },

    /**
     * [addTask description]
     */
    addTask: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(this.modalRoot + ' a[ng-click="addActivity(taskList)"]');
      }.bind(this));

      return this;
    },

    /**
     * [pickDate description]
     * @return {[type]} [description]
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
     * [selectType description]
     * @return {[type]} [description]
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
