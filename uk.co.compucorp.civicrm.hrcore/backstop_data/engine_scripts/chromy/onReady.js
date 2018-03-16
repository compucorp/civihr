var page = require('../../page-objects/contact-summary');

module.exports = function (chromy, scenario, vp) {
  require('./clickAndHoverHelper')(chromy, scenario);

  page.init(chromy);
};
