var modal = require('./modal');

module.exports = (function () {
  return modal.extend({

    /**
     * Opens the "due date" datepicker
     *
     * @return {object}
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
     * Shows the given field
     *
     * @param  {string} fieldName
     * @return {object}
     */
    showField: function (fieldName) {
      var casper = this.casper;

      casper.then(function () {
        casper.click(this.modalRoot + ' a[ng-click*="show' + fieldName + 'Field"]');
      }.bind(this));

      return this;
    },

    /**
     * Selects an assignee for the document
     *
     * @return {object}
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
     * Selects the type of document
     *
     * @return {object}
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
     * Expands fully the modal
     *
     * @return {object}
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
