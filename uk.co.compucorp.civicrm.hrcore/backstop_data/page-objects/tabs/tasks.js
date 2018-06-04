const Tab = require('./tab');

module.exports = class TasksTab extends Tab {
  constructor () {
    super(...arguments);

    this.readySelector = '.ct-page-contact';
    this.tabTitle = 'Tasks';
  }
};
