var page = require('./../page');
var data = require('./../../data/onboarding-wizard-data');

module.exports = (function () {
  return page.extend({
    /**
     * Navigate to Address Page
     *
     * @return {*}
     */
    reachAddressPage: function () {
      var casper = this.casper;

      casper.fillSelectors('form.webform-client-form', data.personalDetails, false);

      casper.click('.webform-next');

      return casper.waitForSelector('input[value="Address"]');
    },
    
    /**
     * Navigate to Contact Info Page
     *
     * @return {*}
     */
    reachContactInfoPage: function () {
      var casper = this.casper;

      return this.reachAddressPage().then(function () {
        casper.fillSelectors('form.webform-client-form', data.address, false);

        casper.click('.webform-next');

        return casper.waitForSelector('input[value="Contact Info"]');
      });
    },

    /**
     * Navigate to Payroll Page
     *
     * @return {*}
     */
    reachPayrollPage: function () {
      var casper = this.casper;

      return this.reachContactInfoPage().then(function () {
        casper.fillSelectors('form.webform-client-form', data.contactInfo, false);

        casper.click('.webform-next');

        return casper.waitForSelector('input[value="Payroll"]');
      });
    },

    /**
     * Navigate to Emergency Contact Page
     *
     * @return {*}
     */
    reachEmergencyContactPage: function () {
      var casper = this.casper;

      return this.reachPayrollPage().then(function () {
        casper.fillSelectors('form.webform-client-form', data.payroll, false);

        casper.click('.webform-next');

        return casper.waitForSelector('input[value="Emergency Contact"]');
      });
    },

    /**
     * Navigate to Dependent Page
     *
     * @return {*}
     */
    reachDependentPage: function () {
      var casper = this.casper;

      return this.reachEmergencyContactPage().then(function () {
        casper.fillSelectors('form.webform-client-form', data.emergencyContacts, false);

        casper.click('.webform-next');

        return casper.waitForSelector('input[value="Dependants"]', function () {
          casper.click('#edit-submitted-do-you-have-any-dependants-1');
        });
      });
    },

    /**
     * Navigate to Profile Picture Page
     *
     * @return {*}
     */
    reachProfilePicturePage: function () {
      var casper = this.casper;
      return this.reachDependentPage().then(function () {
        casper.waitUntilVisible('.webform-component-fieldset', function () {
          casper.fillSelectors('form.webform-client-form', data.dependents, false);

          casper.click('.webform-next');

          return casper.waitForSelector('input[value="Profile Picture"]');
        })
      });
    }
  });
})();
