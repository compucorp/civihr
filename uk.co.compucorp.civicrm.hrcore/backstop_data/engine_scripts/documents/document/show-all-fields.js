'use strict';

const pageObj = require('../../../page-objects/documents');

module.exports = async engine => {
  const page = await pageObj.init(engine);
  const modal = await page.addDocument();

  await modal.showTab('Assignments');
  await modal.showField('Assignee');
  await modal.showField('Assignment');
};
