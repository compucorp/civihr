const tab = require('../tab');

module.exports = tab.extend({
  readySelector: 'absence-tab-work-patterns table',
  tabTitle: 'Work Patterns',

  /**
   * Shows the Custom Work Pattern modal
   */
  async showModal () {
    await this.puppet.click('[ng-click="workpatterns.openModal()"]');
    await this.puppet.waitFor('absence-tab-custom-work-pattern-modal .modal-body > .row', { visible: true });
  }
});
