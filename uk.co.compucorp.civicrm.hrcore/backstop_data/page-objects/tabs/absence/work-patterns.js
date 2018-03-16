var tab = require('../tab');

module.exports = tab.extend({
  readySelector: 'absence-tab-work-patterns table',
  tabTitle: 'Work Patterns',

  /**
   * Shows the Custom Work Pattern modal
   */
  showModal: function () {
    this.chromy.click('[ng-click="workpatterns.openModal()"]');
    this.chromy.waitUntilVisible('absence-tab-custom-work-pattern-modal .modal-body > .row');
  }
});
