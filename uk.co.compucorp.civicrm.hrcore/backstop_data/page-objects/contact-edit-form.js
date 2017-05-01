var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = (function () {
  var edit_button = '#actions .button.edit';

  return page.extend({

    /**
     * Clicks the contact edit
     */
    editForm: function () {
      var casper = this.casper;

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click(edit_button);
          casper.wait(5000);
        }.bind(this));
      }.bind(this));
    }
  });
})();
