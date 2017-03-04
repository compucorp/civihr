var tab = require('./tab');

module.exports = (function () {
  return tab.extend({
    readySelector: '.ct-page-contact',
    tabTitle: 'Tasks'
  });
})();
