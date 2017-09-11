var page = require('./page');

module.exports = (function () {
  var stepsUrls = {
    2: '_qf_MapField_display=true',
    3: '_qf_Preview_display=true',
    4: '_qf_Summary_display=true'
  };

  return page.extend({
    /**
     * Displays L&A Import Form Step 2 by uploading a sample import file and
     * clicking on next.
     *
     * @return Page instance.
     */
    showStep2: function () {
      var filePath = 'backstop_data/uploads/leave-and-absences-import-data.csv';

      this.casper.page.uploadFile('input[name=uploadFile]', filePath);
      this.casper.fillSelectors('#DataSource', {
        '#skipColumnHeader': true
      }, false);
      this.submitAndWaitForStep(2);

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
      this.submitAndWaitForStep(3);

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
      this.submitAndWaitForStep(4);

      return this;
    },

    /**
     * Clicks on next button (.validate) and waits for Step URL.
     *
     * @param {Number} step - the step to wait for.
     */
    submitAndWaitForStep: function (step) {
      var casper = this.casper;
      var urlRegExp = new RegExp(stepsUrls[step]);

      casper.thenClick('.crm-leave-and-balance-import .validate')
      .then(function () {
        return casper.waitForUrl(urlRegExp);
      });
    },

    /**
     * Waits until the import form is visible.
     */
    waitForReady: function () {
      this.waitUntilVisible('.crm-leave-and-balance-import');
    }
  });
})();
