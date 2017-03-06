var tab = require('./tab');

module.exports = (function () {
  return tab.extend({
    readySelector: 'form[name="formDocuments"]',
    tabTitle: 'Documents'
  });
})();
