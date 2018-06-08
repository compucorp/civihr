const Page = require('./page');

module.exports = class OnboardingWizard extends Page {
  /**
   * Navigate to Address Page
   */
  async reachAddressPage () {
    await this.puppet.click('.webform-next');
    await this.puppet.waitFor('input[value="Address"]');
  }

  /**
   * Navigate to Contact Info Page
   */
  async reachContactInfoPage () {
    await this.reachAddressPage();
    await this.puppet.click('.webform-next');
    await this.puppet.waitFor('input[value="Contact Info"]');
  }

  /**
   * Navigate to Payroll Page
   */
  async reachPayrollPage () {
    await this.reachContactInfoPage();
    await this.puppet.click('.webform-next');
    await this.puppet.waitFor('input[value="Payroll"]');
  }

  /**
   * Navigate to Emergency Contact Page
   */
  async reachEmergencyContactPage () {
    await this.reachPayrollPage();
    await this.puppet.click('.webform-next');
    await this.puppet.waitFor('input[value="Emergency Contact"]');
  }

  /**
   * Navigate to Dependent Page
   */
  async reachDependentPage () {
    await this.reachEmergencyContactPage();
    await this.puppet.waitFor('.webform-component-fieldset', { visible: true });
    await this.puppet.type('#edit-submitted-civicrm-1-contact-1-cg99999-fieldset-civicrm-1-contact-1-cg99999-custom-100000', 'Duke');
    await this.puppet.type('#edit-submitted-civicrm-1-contact-1-cg99999-fieldset-civicrm-1-contact-1-cg99999-custom-100001', '1234');
    await this.puppet.click('.webform-next');
    await this.puppet.waitFor('input[value="Dependants"]');
  }

  /**
   * Navigate to Profile Picture Page
   */
  async reachProfilePicturePage () {
    await this.reachDependentPage();
    await this.puppet.waitFor('.webform-component-fieldset', { visible: true });
    await this.puppet.type('#edit-submitted-civicrm-1-contact-3-cg99999-fieldset-civicrm-1-contact-3-cg99999-custom-100000', 'Duke');
    await this.puppet.click('.webform-next');
    await this.puppet.waitFor('input[value="Profile Picture"]');
  }
};
