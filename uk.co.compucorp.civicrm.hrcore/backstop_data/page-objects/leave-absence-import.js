const path = require('path');
const page = require('./page');

module.exports = page.extend({
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
    await this.submitAndWait();
  },

  /**
   * Displays L&A Import Form Step 3 by displaying step 2 and then clicking
   * on next.
   *
   * @return Page instance.
   */
  async showStep3 () {
    await this.showStep2();
    await this.submitAndWait();
  },

  /**
   * Displays L&A Import Form Step 4 by displaying step 3 and then clicking
   * on next.
   *
   * @return page instance.
   */
  async showStep4 () {
    await this.showStep3();
    await this.submitAndWait();
  },

  /**
   * Clicks on next button (.validate) and waits for Step URL.
   */
  async submitAndWait () {
    await this.puppet.click('.crm-leave-and-balance-import .validate');
    await this.puppet.waitForNavigation({ waitUntil: 'domcontentloaded' });
  },

  /**
   * Waits until the import form is visible.
   */
  async waitForReady () {
    await this.puppet.waitFor('.crm-leave-and-balance-import');
  }
});
