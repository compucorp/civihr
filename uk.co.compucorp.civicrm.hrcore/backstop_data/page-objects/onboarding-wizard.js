var page = require('./page');

module.exports = page.extend({
  /**
   * Navigate to Address Page
   */
  reachAddressPage: function () {
    this.chromy.click('.webform-next');
    this.chromy.wait('input[value="Address"]');
  },

  /**
   * Navigate to Contact Info Page
   */
  reachContactInfoPage: function () {
    this.reachAddressPage();
    this.chromy.click('.webform-next');
    this.chromy.wait('input[value="Contact Info"]');
  },

  /**
   * Navigate to Payroll Page
   */
  reachPayrollPage: function () {
    this.reachContactInfoPage();
    this.chromy.click('.webform-next');
    this.chromy.wait('input[value="Payroll"]');
  },

  /**
   * Navigate to Emergency Contact Page
   */
  reachEmergencyContactPage: function () {
    this.reachPayrollPage();
    this.chromy.click('.webform-next');
    this.chromy.wait('input[value="Emergency Contact"]');
  },

  /**
   * Navigate to Dependent Page
   */
  reachDependentPage: function () {
    this.reachEmergencyContactPage();
    this.chromy.click('.webform-next');
    this.chromy.wait('input[value="Dependants"]');
    this.chromy.click('#edit-submitted-do-you-have-dependants-1');
  },

  /**
   * Navigate to Profile Picture Page
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
