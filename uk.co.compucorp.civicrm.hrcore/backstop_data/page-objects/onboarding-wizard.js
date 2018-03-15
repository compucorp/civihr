var page = require('./page');

module.exports = (function () {
  return page.extend({
    /**
     * Navigate to Address Page
     *
     * @return {*}
     */
    reachAddressPage: function () {
      this.chromy.click('.webform-next');
      this.chromy.wait('input[value="Address"]');
    },

    /**
     * Navigate to Contact Info Page
     *
     * @return {*}
     */
    reachContactInfoPage: function () {
      this.reachAddressPage();
      this.chromy.click('.webform-next');
      this.chromy.wait('input[value="Contact Info"]');
    },

    /**
     * Navigate to Payroll Page
     *
     * @return {*}
     */
    reachPayrollPage: function () {
      this.reachContactInfoPage();
      this.chromy.click('.webform-next');
      this.chromy.wait('input[value="Payroll"]');
    },

    /**
     * Navigate to Emergency Contact Page
     *
     * @return {*}
     */
    reachEmergencyContactPage: function () {
      this.reachPayrollPage();
      this.chromy.click('.webform-next');
      this.chromy.wait('input[value="Emergency Contact"]');
    },

    /**
     * Navigate to Dependent Page
     *
     * @return {*}
     */
    reachDependentPage: function () {
      this.reachEmergencyContactPage();
      this.chromy.click('.webform-next');
      this.chromy.wait('input[value="Dependants"]');
      this.chromy.click('#edit-submitted-do-you-have-dependants-1');
    },

    /**
     * Navigate to Profile Picture Page
     *
     * @return {*}
     */
    reachProfilePicturePage: function () {
      this.reachDependentPage();
      this.chromy.waitUntilVisible('.webform-component-fieldset');
      this.chromy.type('#edit-submitted-first-dependant-civicrm-1-contact-3-cg99999-custom-100000', 'Duke');
      this.chromy.type('#edit-submitted-first-dependant-civicrm-1-contact-3-cg99999-custom-100001', '1234');
      this.chromy.type('#edit-submitted-first-dependant-civicrm-1-contact-3-cg99999-custom-100010', 'sibling');
      this.chromy.click('.webform-next');
      this.chromy.wait('input[value="Profile Picture"]');
    }
  });
})();
