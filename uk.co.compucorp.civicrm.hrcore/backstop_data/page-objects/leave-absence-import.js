var path = require('path');
var page = require('./page');

module.exports = page.extend({
  /**
   * Displays L&A Import Form Step 2 by uploading a sample import file and
   * clicking on next.
   *
   * @return Page instance.
   */
  showStep2: function () {
    var filePath = path.join(__dirname, '..', 'uploads/leave-and-absences-import-data.csv');

    this.chromy.setFile('input[name="uploadFile"]', filePath);
    this.chromy.check('#skipColumnHeader');
    this.submitAndWait();

    return this;
  },

  /**
   * Displays L&A Import Form Step 3 by displaying step 2 and then clicking
   * on next.
   *
   * @return Page instance.
   */
  showStep3: function () {
    this.showStep2();
    this.submitAndWait();

    return this;
  },

  /**
   * Displays L&A Import Form Step 4 by displaying step 3 and then clicking
   * on next.
   *
   * @return page instance.
   */
  showStep4: function () {
    this.showStep3();
    this.submitAndWait();

    return this;
  },

  /**
   * Clicks on next button (.validate) and waits for Step URL.
   */
  submitAndWait: function () {
    this.chromy.click('.crm-leave-and-balance-import .validate');
    this.chromy.waitLoadEvent();
  },

  /**
   * Waits until the import form is visible.
   */
  waitForReady: function () {
    this.chromy.wait('.crm-leave-and-balance-import');
  }
});
