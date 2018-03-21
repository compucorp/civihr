/* global XPathResult */

var modal = require('./modal');

module.exports = modal.extend({
  /**
   * Selects the tab with the given title
   *
   * @param  {string} tabTitle
   */
  selectTab: function (tabTitle) {
    this.chromy.evaluate(function (tabTitle) {
      // = clickLabel
      var xPath = './/a[text()="' + tabTitle + '"]';
      var link = document.evaluate(xPath, document.body, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;

      link.click();
    }, [tabTitle]);
  }
});
