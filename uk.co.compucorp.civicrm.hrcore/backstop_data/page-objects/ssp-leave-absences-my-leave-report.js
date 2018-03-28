/* globals Event */

var _ = require('lodash');
var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = page.extend({
  /**
   * Selects the days mode for the opened leave request
   *
   * @param  {String} mode single|multiple
   * @return {Promise}
   */
  changeRequestDaysMode: function (mode) {
    var optionIndex = ['multiple', 'single'].indexOf(mode) + 1;

    this.chromy.click('[ng-model="detailsTab.uiOptions.multipleDays"]:nth-child(' + optionIndex + ')');

    return this;
  },

  /**
   * User clicks on the edit/respond action
   *
   * @param {Number} row number corresponding to leave request in the list
   * @return {Promise}
   */
  editRequest: function (row) {
    var chromy = this.chromy;

    return new Promise(function (resolve) {
      chromy.click('body > ul.dropdown-menu:nth-of-type(' + (row || 1) + ') li:first-child a');
      chromy.wait(function () {
        // = CasperJS.waitWhileVisible()
        var dom = document.querySelector('.modal-content .spinner:nth-child(1)');

        return dom === null || (dom.offsetWidth <= 0 && dom.offsetHeight <= 0);
      });
      chromy.wait(function () {
        // = CasperJS.waitWhileVisible()
        var dom = document.querySelector('leave-request-popup-details-tab .spinner');

        return dom === null || (dom.offsetWidth <= 0 && dom.offsetHeight <= 0);
      });

      resolve(this.waitForModal('ssp-leave-request', '.chr_leave-request-modal__form'));
    }.bind(this));
  },

  /**
   * Expands deduction field to show selectors
   *
   * @param  {String} type from|to
   * @return {Promise}
   */
  expandDeductionField: function (type) {
    var fieldSelector = '[ng-switch="detailsTab.uiOptions.times.' + type + '.amountExpanded"] a';

    this.chromy.wait(fieldSelector);
    this.chromy.click(fieldSelector);

    return this;
  },

  /**
   * Opens the Leave Request Modal for a new request of the given type
   *
   * @param  {String} requestType leave|sickness|toil
   * @return {Promise}
   */
  newRequest: function (requestType) {
    var requestTypes = ['leave', 'sickness', 'toil']; // must be in the same quantity and order as in UI
    var requestTypeButtonIndex = requestTypes.indexOf(requestType) + 1;
    var actionDropdownSelector = 'leave-request-record-actions';
    var actionButtonSelector = actionDropdownSelector + ' .dropdown-menu li:nth-child(' + requestTypeButtonIndex + ') a';

    this.chromy.click(actionDropdownSelector + ' [uib-dropdown] > button');
    this.chromy.wait(actionButtonSelector);
    this.chromy.click(actionButtonSelector);
    this.chromy.waitUntilVisible('.chr_leave-request-modal__tab .form-group');

    return this;
  },

  /**
   * Opens the dropdown for staff actions like edit/respond, cancel.
   *
   * @param {Number} row number corresponding to leave request in the list
   * @return {Object} this object
   */
  openActionsForRow: function (row) {
    this.chromy.wait('tr:nth-child(1)  div[uib-dropdown] a:nth-child(1)');
    this.chromy.click('div:nth-child(2) > div > table > tbody > tr:nth-child(' + (row || 1) + ')  div[uib-dropdown] a:nth-child(1)');

    return this;
  },

  /**
   * Opens the given section of my report pageName
   *
   * @param {String} section
   * @return {Object} this object
   */
  openSection: function (section) {
    this.chromy.click('td[ng-click="report.toggleSection(\'' + section + '\')"]');
    this.chromy.wait(function () {
      // = CasperJS.waitWhileVisible()
      var dom = document.querySelector('.spinner');

      return dom === null || (dom.offsetWidth <= 0 && dom.offsetHeight <= 0);
    });

    return this;
  },

  /**
   * Selects the request Absence Type by the given label
   *
   * @param  {String} absenceTypeLabel ex. "Holiday in Hours"
   * @return {Promise}
   */
  selectRequestAbsenceType: function (absenceTypeLabel) {
    this.chromy.evaluate(function (absenceTypeLabel) {
      var absenceTypeSelect = document.querySelector('[name=absenceTypeSelect]');

      absenceTypeSelect.selectedIndex = _.findIndex(absenceTypeSelect.querySelectorAll('option'), function (option) {
        return option.text.search(absenceTypeLabel) !== -1;
      }); // Select the needed option
      absenceTypeSelect.dispatchEvent(new Event('change')); // Trigger onChange event
    }, [absenceTypeLabel]);

    return this;
  },

  /**
   * Selects a date in the datepicker
   *
   * @param  {String} type from|to
   * @param  {Number} weekPosition eg. 2 for second week in the calendar
   * @param  {Number} weekDayPosition eg. 1 for Monday or 4 for Thursday
   * @return {Promise}
   */
  selectRequestDate: function (type, weekPosition, weekDayPosition) {
    var daySelector = '.uib-daypicker tr:nth-child(' +
      weekPosition + ') td:nth-child( ' + weekDayPosition + ') button';

    this.chromy.click('[ng-model="detailsTab.uiOptions.' + type + 'Date"]');
    this.chromy.wait(daySelector);
    this.chromy.click(daySelector);
    this.chromy.waitUntilVisible('[ng-switch="detailsTab.uiOptions.times.' + type + '.amountExpanded"]');

    return this;
  },

  /**
   * Wait for the page to be ready
   *
   * @return {Object} this object
   */
  waitForReady: function () {
    this.chromy.waitUntilVisible('td[ng-click="report.toggleSection(\'pending\')"]');
  },

  /**
   * Waits for the request balance to be calculated
   *
   * @return {Promise}
   */
  waitUntilRequestBalanceIsCalculated: function () {
    this.chromy.waitUntilVisible('[ng-show="detailsTab.uiOptions.showBalance"]');

    return this;
  }
});
