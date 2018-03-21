var page = require('../page-objects/page');

module.exports = function (chromy, scenario, vp) {
  require('./clickAndHoverHelper')(chromy, scenario);

  page.init(chromy);
};
