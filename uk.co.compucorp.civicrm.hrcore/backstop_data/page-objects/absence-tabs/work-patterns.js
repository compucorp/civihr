var tab = require('../tabs/tab');

module.exports = (function () {
  return tab.extend({
    readySelector: 'absence-tab-work-patterns table',
    tabTitle: 'Work Patterns',

    /**
     * Shows the Custom Work Pattern modal
     */
    showModal: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('[ng-click="workpatterns.openModal()"]');
        casper.waitUntilVisible('absence-tab-custom-work-pattern-modal .modal-body > .row');
      });
    }
  });
})();
