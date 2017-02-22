var _ = require('lodash');
var Promise = require('es6-promise').Promise;
var customCasperJS = require('../utils/custom-casperjs');

/**
 * [closeAnyModal description]
 * @return {[type]} [description]
 */
function closeAnyModal() {
  var casper = this.casper;
  var openModalSelector = '.modal.in';

  casper.then(function () {
    if (casper.exists(openModalSelector)) {
      casper.click(openModalSelector + ' .close[ng-click="cancel()"]');
      casper.wait(300);
    }
  });

  return this;
}


module.exports = {

  /**
   * [init description]
   * @param  {[type]} casper       [description]
   * @param  {[type]} clearDialogs [description]
   * @return {[type]}              [description]
   */
  init: function (casper, clearDialogs) {
    clearDialogs = typeof clearDialogs !== 'undefined' ? !!clearDialogs : true;

    this.casper = customCasperJS(casper);

    !!this.waitForReady && this.casper.then(function () {
      this.waitForReady();
    }.bind(this));

    if (clearDialogs) {
      closeAnyModal.call(this);
    }

    return this;
  },

  /**
   * [extent description]
   * @param  {[type]} page [description]
   * @return {[type]}      [description]
   */
  extend: function (page) {
    return _.assign(Object.create(this), page);
  },

  /**
   * [waitForModal description]
   * @return {[type]} [description]
   */
  waitForModal: function (modalModule) {
    var casper = this.casper;

    return new Promise(function (resolve) {
      casper.then(function () {
        casper.waitUntilVisible('.modal', function () {
          casper.wait(300);

          if (modalModule) {
            resolve(require('./modals/' + modalModule).init(casper, false));
          } else {
            resolve();
          }
        });
      });
    });
  }
};
