const _ = require('lodash');
const Promise = require('es6-promise').Promise;

module.exports = {
  /**
   * Initializes the page and removes any code warnings from the page
   *
   * @param  {Object} puppet
   * @param  {Boolean} clearDialogs if true it will close modals and notifications
   * @return {Object}
   */
  init: async (puppet, clearDialogs) => {
    clearDialogs = typeof clearDialogs !== 'undefined' ? !!clearDialogs : true;

    this.puppet = puppet;
    !!this.waitForReady && await this.waitForReady();

    let href = await this.puppet.evaluate(() => document.location.href);
    let isAdmin = href.indexOf('civicrm/') > 1;

    if (isAdmin) {
      await this.puppet.evaluate(function () {
        let errorsWrapper = document.querySelector('#content > #console');
        errorsWrapper && (errorsWrapper.style.display = 'none');
      });
    } else {
      await this.puppet.evaluate(function () {
        let errorsWrapper = document.querySelector('#messages .alert');
        errorsWrapper && (errorsWrapper.style.display = 'none');
      });
    }

    if (clearDialogs) {
      await closeAnyModal.call(this);
      await closeNotifications.call(this);
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
  extend: (page) => {
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
  waitForModal: async (modalModule, waitSelector) => {
    return new Promise(async resolve => {
      await this.puppet.waitFor(waitSelector || '.modal');
      await this.puppet.wait(300);

      if (modalModule) {
        let modal = await require('./modals/' + modalModule).init(this.puppet, false);

        resolve(modal);
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
async function closeAnyModal () {
  const openModalSelector = '.modal.in';

  let result = await this.puppet.$(openModalSelector);

  if (result) {
    await this.puppet.click(openModalSelector + ' .close[ng-click="cancel()"]');
    await this.puppet.wait(300);
  }

  return this;
}

/**
 * Closes any notification currently open
 *
 * @return {Object}
 */
async function closeNotifications () {
  const notificationSelector = 'a.ui-notify-cross.ui-notify-close';

  let result = await this.puppet.$(notificationSelector);

  if (result) {
    await this.puppet.click(notificationSelector);
    await this.puppet.wait(500);
  }

  return this;
}
