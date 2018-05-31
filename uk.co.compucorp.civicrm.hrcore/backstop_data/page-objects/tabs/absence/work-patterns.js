const Tab = require('../tab');

module.exports = class AbsenceWorkPatternsTab extends Tab {
  constructor () {
    super(...arguments);
    this.readySelector = 'absence-tab-work-patterns table';
    this.tabTitle = 'Work Patterns';
  }

  /**
   * Shows the Custom Work Pattern modal
   */
  async showModal () {
    await this.puppet.click('[ng-click="workpatterns.openModal()"]');
    await this.puppet.waitFor('absence-tab-custom-work-pattern-modal .modal-body > .row', { visible: true });
  }
};
