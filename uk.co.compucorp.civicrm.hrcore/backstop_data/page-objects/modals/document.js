var modal = require('./modal');

module.exports = (function () {
  return modal.extend({

    /**
     * [pickDueDate description]
     * @return {[type]} [description]
     */
    pickDueDate: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(this.modalRoot + ' [ng-model="document.activity_date_time"]');
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
        casper.click(this.modalRoot + ' a[ng-click*="show' + fieldName + 'Field"]');
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
        casper.click(this.modalRoot + ' [ng-model="document.assignee_contact_id[0]"] .ui-select-match');
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
        casper.click(this.modalRoot + ' [ng-model="document.activity_type_id"] .ui-select-match');
        casper.waitUntilVisible('.select2-with-searchbox');
      }.bind(this));

      return this;
    },

    /**
     * [showMore description]
     * @return {[type]} [description]
     */
    showMore: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(this.modalRoot + ' [ng-click*="expanded"]');
        casper.wait(200);
      }.bind(this));

      return this;
    }
  });
})();
