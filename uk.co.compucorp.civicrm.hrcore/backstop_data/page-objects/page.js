module.exports = class Page {
  constructor (puppet, clearDialogs = true) {
    this.puppet = puppet;
    this.clearDialogs = clearDialogs;
  }

  /**
   * Initializes the page and removes any code warnings from the page
   *
   * @param  {Object} puppet
   * @param  {Boolean} clearDialogs if true it will close modals and notifications
   * @return {Object}
   */
  async init () {
    !!this.waitForReady && await this.waitForReady();

    const href = await this.puppet.evaluate(() => document.location.href);
    const isAdmin = href.indexOf('civicrm/') > 1;

    await this.puppet.evaluate(function (isAdmin) {
      const selector = isAdmin ? '#content > #console' : '#messages .alert';
      const errorsWrapper = document.querySelector(selector);

      errorsWrapper && (errorsWrapper.style.display = 'none');
    }, isAdmin);

    if (this.clearDialogs) {
      await closeAnyModal.call(this);
      await closeNotifications.call(this);
    }
  }

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
      const Modal = require('./modals/' + modalModule);
      const modal = new Modal(this.puppet, false);

      await modal.init();

      return modal;
    }
  }
};

/**
 * Closes any modal currently open
 */
async function closeAnyModal () {
  const openModalSelector = '.modal.in';

  const result = await this.puppet.$(openModalSelector);

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

  const result = await this.puppet.$(notificationSelector);

  if (result) {
    await this.puppet.click(notificationSelector);
    await this.puppet.waitFor(500);
  }
}
