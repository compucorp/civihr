const Page = require('../page-objects/page');

module.exports = async (puppet, scenario, vp) => {
  const page = new Page(puppet);

  await require('./clickAndHoverHelper')(puppet, scenario);
  await page.init();
};
