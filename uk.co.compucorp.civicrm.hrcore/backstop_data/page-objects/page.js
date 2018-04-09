const _ = require('lodash');

module.exports = {
  /**
   * Initializes the page and removes any code warnings from the page
   *
   * @param  {Object} puppet
   * @param  {Boolean} clearDialogs if true it will close modals and notifications
   * @return {Object}
   */
  async init (puppet, clearDialogs) {
    clearDialogs = typeof clearDialogs !== 'undefined' ? !!clearDialogs : true;

    this.puppet = puppet;
    !!this.waitForReady && await this.waitForReady();

    let href = await this.puppet.evaluate(() => document.location.href);
    let isAdmin = href.indexOf('civicrm/') > 1;

    await this.puppet.evaluate(function (isAdmin) {
      let selector = isAdmin ? '#content > #console' : '#messages .alert';
      let errorsWrapper = document.querySelector(selector);

      errorsWrapper && (errorsWrapper.style.display = 'none');
    }, isAdmin);

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
  extend (page) {
    return _.assign(Object.create(this), page);
  },

  /**
   * Waits for the modal dialog to load. By default it waits for the .modal class
   * in dialog otherwise user can specify a custom waitSelector. Once model is
   * visible it loads the respective modalModule (if any)
   *
   * @param {String} modalModule
   * @param {String} waitSelector
   * @return {Object} the modal
   */
  async waitForModal (modalModule, waitSelector) {
    await this.puppet.waitFor(waitSelector || '.modal', { visible: true });
    await this.puppet.waitFor(300);

    if (modalModule) {
      return require('./modals/' + modalModule).init(this.puppet, false);
    }
  }
};

/**
 * Closes any modal currently open
 */
async function closeAnyModal () {
  const openModalSelector = '.modal.in';

  let result = await this.puppet.$(openModalSelector);

  if (result) {
    await this.puppet.click(openModalSelector + ' .close[ng-click="cancel()"]');
    await this.puppet.waitFor(300);
  }
}

/**
 * Closes any notification currently open
 */
async function closeNotifications () {
  const notificationSelector = 'a.ui-notify-cross.ui-notify-close';

  let result = await this.puppet.$(notificationSelector);

  if (result) {
    await this.puppet.click(notificationSelector);
    await this.puppet.waitFor(500);
  }
}
