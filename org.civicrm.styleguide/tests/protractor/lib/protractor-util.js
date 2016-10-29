/**
 * Protractor is primarily designed for working with AngularJS pages. However, CiviCRM includes
 * a mix of Angular and non-angular pages.
 *
 * @type {{withoutAngular: module.exports.withoutAngular, withAngular: module.exports.withoutAngular}}
 */
module.exports = {

  /**
   * Temporarily disable Angular handling while executing the callback.
   *
   * @param callback
   * @returns {*}
   */
  withoutAngular: function withoutAngular(callback) {
    var oldSync = browser.ignoreSynchronization;
    browser.ignoreSynchronization = true;
    try {
      return callback();
    } finally {
      browser.ignoreSynchronization = oldSync;
    }
  },

  /**
   * Temporarily enable Angular handling while executing the callback.
   *
   * @param callback
   * @returns {*}
   */
  withAngular: function withoutAngular(callback) {
    var oldSync = browser.ignoreSynchronization;
    browser.ignoreSynchronization = false;
    try {
      return callback();
    } finally {
      browser.ignoreSynchronization = oldSync;
    }
  }

};