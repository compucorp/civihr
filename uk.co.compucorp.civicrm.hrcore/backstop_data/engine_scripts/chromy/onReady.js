var page = require('../../page-objects/contact-summary');

module.exports = function (chromy, scenario, vp) {
  console.log('SCENARIO > ' + scenario.label);
  require('./clickAndHoverHelper')(chromy, scenario);

  page.init(chromy);
};
