const path = require('path');
const Page = require('./page');

module.exports = class LeaveAbsenceImport extends Page {
  /**
   * Displays L&A Import Form Step 2 by uploading a sample import file and
   * clicking on next.
   *
   * @return Page instance.
   */
  async showStep2 () {
    const filePath = path.join(__dirname, '..', 'uploads/leave-and-absences-import-data.csv');
    const fileInput = await this.puppet.$('input[name="uploadFile"]');

    await fileInput.uploadFile(filePath);
    await this.puppet.click('[name="skipColumnHeader"]');
    await this.submitAndWaitForStep(2);
  }

  /**
   * Displays L&A Import Form Step 3 by displaying step 2 and then clicking
   * on next.
   *
   * @return Page instance.
   */
  async showStep3 () {
    await this.showStep2();
    await this.submitAndWaitForStep(3);
  }

  /**
   * Displays L&A Import Form Step 4 by displaying step 3 and then clicking
   * on next.
   *
   * @return page instance.
   */
  async showStep4 () {
    await this.showStep3();
    await this.submitAndWaitForStep(4);
  }

  /**
   * Clicks on "next" button and waits for the next step to be ready
   * (a step is ready when its breadcrumb in the wizard is active)
   *
   * @param {Number} step
   */
  async submitAndWaitForStep (step) {
    await this.puppet.click('.crm-leave-and-balance-import .validate');
    await this.puppet.waitFor(`.crm_wizard__title .nav-pills li.active:nth-child(${step})`);
  }

  /**
   * Waits until the import form is visible.
   */
  async waitForReady () {
    await this.puppet.waitFor('.crm-leave-and-balance-import');
  }
};
