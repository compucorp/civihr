var _ = require('lodash');
var casper;

// Methods that will override the default CasperJS methods
var overrides = {

  /**
   * [click description]
   * @return {[type]} [description]
   */
  click: function () {
    var selector = arguments[0];

    if (this.exists(selector)) {
      this.originalMethods.click.apply(this, arguments);
    } else {
      this.die('The selector `' + selector + '` doesn\'t exist!', 'ERROR');
    }
  }
}


module.exports = function (_casper_) {
  casper = _casper_;

  if (!casper.originalMethods) {
    casper.originalMethods = {}

    _(overrides)
      .each(function (method, name) {
        casper.originalMethods[name] = casper[name];
        casper[name] = method.bind(casper);
      });
  }

  return casper;
};
