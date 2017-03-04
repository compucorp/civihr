var modal = require('./modal');

module.exports = (function () {
  return modal.extend({

    /**
     * [pickDate description]
     * @return {[type]} [description]
     */
    pickDate: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(this.modalRoot + ' [ng-model="task.activity_date_time"]');
        casper.waitUntilVisible('.uib-datepicker-popup');
      }.bind(this));

      return this;
    },

    /**
     * [showField description]
     * @param  {[type]} fieldName [description]
     * @return {[type]}           [description]
     */
    showField: function (fieldName) {
      var casper = this.casper;

      casper.then(function () {
        casper.click(this.modalRoot + ' a[ng-click*="showField' + fieldName + '"]');
      }.bind(this));

      return this;
    },

    /**
     * [selectAssignee description]
     * @return {[type]} [description]
     */
    selectAssignee: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(this.modalRoot + ' [ng-model="task.assignee_contact_id[0]"] .ui-select-match');
        casper.waitUntilVisible('.select2-with-searchbox');
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
        casper.click(this.modalRoot + ' [ng-model="task.activity_type_id"] .ui-select-match');
        casper.waitUntilVisible('.select2-with-searchbox');
      }.bind(this));

      return this;
    }
  });
})();
