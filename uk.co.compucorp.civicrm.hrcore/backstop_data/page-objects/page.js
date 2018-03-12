var _ = require('lodash');
var Promise = require('es6-promise').Promise;

/**
 * Closes any modal currently open
 *
 * @return {object}
 */
function closeAnyModal () {
  var openModalSelector = '.modal.in';

  if (this.chromy.exists(openModalSelector)) {
    this.chromy.click(openModalSelector + ' .close[ng-click="cancel()"]');
    this.chromy.wait(300);
  }

  return this;
}

/**
 * Closes any notification currently open
 *
 * @return {object}
 */
function closeNotifications () {
  var notificationSelector = 'a.ui-notify-cross.ui-notify-close';

  if (this.chromy.exists(notificationSelector)) {
    this.chromy.click(notificationSelector);
    this.chromy.wait(500);
  }

  return this;
}

module.exports = {

  /**
   * Initializes the page
   *
   * Stores a customized version of CasperJS and then wait for a
   * until a certain "ready" condition is met, if the page is set up to do so
   *
   * @param  {object} chromy
   * @param  {boolean} clearDialogs if true it will close modals and notifications
   * @return {object}
   */
  init: function (chromy, clearDialogs) {
    clearDialogs = typeof clearDialogs !== 'undefined' ? !!clearDialogs : true;

    this.chromy = chromy;
    !!this.waitForReady && this.waitForReady();

    if (clearDialogs) {
      closeAnyModal.call(this);
      closeNotifications.call(this);
    }

    return this;
  },

  /**
   * Used to extend the main page
   *
   * @param  {object} page
   *   a collection of methods and properties that will extend the main page
   * @return {object}
   */
  extend: function (page) {
    return _.assign(Object.create(this), page);
  },

  /**
   * Waits for the modal dialog to load. By default it waits for the .modal class
   * in dialog otherwise user can specify a custom waitSelector. Once model is
   * visible it loads the respective modalModule (if any)
   * @param {String} modalModule
   * @param {String} waitSelector
   * @return {Promise}
   */
  waitForModal: function (modalModule, waitSelector) {
    var chromy = this.chromy;

    return new Promise(function (resolve) {
      chromy.wait(waitSelector || '.modal');
      chromy.wait(300);

      if (modalModule) {
        resolve(require('./modals/' + modalModule).init(chromy, false));
      } else {
        resolve();
      }
    });
  }
};
