'use strict';

const Page = require('../../../page-objects/tasks');

module.exports = async engine => {
  const page = new Page(engine);
  await page.init();

  const modal = await page.addTask();
  await modal.showField('Subject');
  await modal.showField('Assignee');
  await modal.showField('Status');
  await modal.showField('Assignment');
};
