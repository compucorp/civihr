const page = require('../page-objects/page');

module.exports = async (puppet, scenario, vp) => {
  await require('./clickAndHoverHelper')(puppet, scenario);
  await page.init(puppet);
};
