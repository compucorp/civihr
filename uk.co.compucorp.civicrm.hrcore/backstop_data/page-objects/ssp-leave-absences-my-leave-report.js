/* globals Event */

const _ = require('lodash');
const page = require('./page');

module.exports = page.extend({
  /**
   * Selects the days mode for the opened leave request
   *
   * @param {String} mode single|multiple
   */
  async changeRequestDaysMode (mode) {
    const optionIndex = ['multiple', 'single'].indexOf(mode) + 1;

    await this.puppet.click('[ng-model="detailsTab.uiOptions.multipleDays"]:nth-child(' + optionIndex + ')');
  },

  /**
   * User clicks on the edit/respond action
   *
   * @param {Number} row number corresponding to leave request in the list
   * @return {Object} the request modal
   */
  async editRequest (row) {
    await this.puppet.click('body > ul.dropdown-menu:nth-of-type(' + (row || 1) + ') li:first-child a');
    await this.puppet.waitFor('.modal-content .spinner:nth-child(1)', { hidden: true });
    await this.puppet.waitFor('leave-request-popup-details-tab .spinner', { hidden: true });

    return this.waitForModal('ssp-leave-request', '.chr_leave-request-modal__form');
  },

  /**
   * Expands deduction field to show selectors
   *
   * @param {String} type from|to
   */
  async expandDeductionField (type) {
    const fieldSelector = '[ng-switch="detailsTab.uiOptions.times.' + type + '.amountExpanded"] a';

    await this.puppet.waitFor(fieldSelector);
    await this.puppet.click(fieldSelector);
  },

  /**
   * Opens the Leave Request Modal for a new request of the given type
   *
   * @param {String} requestType leave|sickness|toil
   */
  async newRequest (requestType) {
    await this.puppet.click('leave-request-record-actions .dropdown-toggle');
    await this.puppet.click(`.leave-request-record-actions__new-${requestType}`);

    await this.puppet.waitFor('.chr_leave-request-modal__tab .form-group', { visible: true });
  },

  /**
   * Opens the dropdown for staff actions like edit/respond, cancel.
   *
   * @param {Number} row number corresponding to leave request in the list
   */
  async openActionsForRow (row) {
    await this.puppet.waitFor('tr:nth-child(1)  div[uib-dropdown] a:nth-child(1)');
    await this.puppet.click('div:nth-child(2) > div > table > tbody > tr:nth-child(' + (row || 1) + ')  div[uib-dropdown] a:nth-child(1)');
  },

  /**
   * Opens the given section of my report pageName
   *
   * @param {String} section
   */
  async openSection (section) {
    await this.puppet.click('td[ng-click="report.toggleSection(\'' + section + '\')"]');
    await this.puppet.waitFor(function () {
      const spinners = document.querySelectorAll('.spinner');

      return Array.prototype.every.call(spinners, function (dom) {
        return dom === null || (dom.offsetWidth <= 0 && dom.offsetHeight <= 0);
      });
    });
  },

  /**
   * Selects the request Absence Type by the given label
   *
   * @param {String} absenceTypeLabel ex. "Holiday in Hours"
   */
  async selectRequestAbsenceType (absenceTypeLabel) {
    await this.puppet.evaluate(function (absenceTypeLabel) {
      const absenceTypeSelect = document.querySelector('[name=absenceTypeSelect]');

      absenceTypeSelect.selectedIndex = _.findIndex(absenceTypeSelect.querySelectorAll('option'), function (option) {
        return option.text.search(absenceTypeLabel) !== -1;
      }); // Select the needed option
      absenceTypeSelect.dispatchEvent(new Event('change')); // Trigger onChange event
    }, absenceTypeLabel);
  },

  /**
   * Selects a date in the datepicker
   *
   * @param {String} type from|to
   * @param  {Number} weekPosition eg. 2 for second week in the calendar
   * @param  {Number} weekDayPosition eg. 1 for Monday or 4 for Thursday
   */
  async selectRequestDate (type, weekPosition, weekDayPosition) {
    const daySelector = '.uib-daypicker tr:nth-child(' + weekPosition + ') td:nth-child( ' + weekDayPosition + ') button';

    await this.puppet.click('[ng-model="detailsTab.uiOptions.' + type + 'Date"]');
    await this.puppet.waitFor(daySelector);
    await this.puppet.click(daySelector);
    await this.puppet.waitFor('[ng-switch="detailsTab.uiOptions.times.' + type + '.amountExpanded"]', { visible: true });
  },

  /**
   * Wait for the page to be ready
   */
  async waitForReady () {
    await this.puppet.waitFor('.spinner', { visible: false });
    await this.puppet.waitFor('td[ng-click="report.toggleSection(\'pending\')"]', { visible: true });
  },

  /**
   * Waits for the request balance to be calculated
   */
  async waitUntilRequestBalanceIsCalculated () {
    await this.puppet.waitFor('[ng-show="detailsTab.uiOptions.showBalance"]', { visible: true });
  }
});
