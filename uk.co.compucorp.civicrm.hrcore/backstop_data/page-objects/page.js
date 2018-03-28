var _ = require('lodash');
var Promise = require('es6-promise').Promise;

module.exports = {
  /**
   * Initializes the page and removes any code warnings from the page
   *
   * @param  {Object} chromy
   * @param  {Boolean} clearDialogs if true it will close modals and notifications
   * @return {Object}
   */
  init: function (chromy, clearDialogs) {
    clearDialogs = typeof clearDialogs !== 'undefined' ? !!clearDialogs : true;
    this.chromy = chromy;

    !!this.waitForReady && this.waitForReady();

    chromy.evaluate(function () {
      return document.location.href;
    })
      .result(function (href) {
        var isAdmin = href.indexOf('civicrm/') > 1;

        chromy.evaluate(function (isAdmin) {
          var selector = isAdmin ? '#content > #console' : '#messages .alert';
          var errorsWrapper = document.querySelector(selector);

          errorsWrapper && (errorsWrapper.style.display = 'none');
        }, [isAdmin]);
      });

    if (clearDialogs) {
      closeAnyModal.call(this);
      closeNotifications.call(this);
    }

    return this;
  },

  /**
   * Used to extend the main page
   *
   * @param  {Object} page
   *   a collection of methods and properties that will extend the main page
   * @return {Object}
   */
  extend: function (page) {
    return _.assign(Object.create(this), page);
  },

  /**
   * Waits for the modal dialog to load. By default it waits for the .modal class
   * in dialog otherwise user can specify a custom waitSelector. Once model is
   * visible it loads the respective modalModule (if any)
   *
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

/**
 * Closes any modal currently open
 *
 * @return {Object}
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
 * @return {Object}
 */
function closeNotifications () {
  var notificationSelector = 'a.ui-notify-cross.ui-notify-close';

  if (this.chromy.exists(notificationSelector)) {
    this.chromy.click(notificationSelector);
    this.chromy.wait(500);
  }

  return this;
}
