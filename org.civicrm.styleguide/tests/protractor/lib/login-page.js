// The LoginPage provides helper functions for logging in.
// It abstracts the differences between the login forms on
// each supported CMS.

var prtr = require('./protractor-util');

var loginHandlers = {
  Backdrop: function(username, password, loginType) {
    expect(false).toBe(true); // FIXME: Implement Backdrop
  },
  Drupal: function(username, password, loginType) {
    prtr.withoutAngular(function() {
      browser.get(_CV.CMS_URL + '/user/login');
      element(by.css('#user-login input[name=name]')).sendKeys(username);
      element(by.css('#user-login input[name=pass]')).sendKeys(password);
      element(by.css('#user-login .form-submit')).click();
    });
  },
  Drupal6: function(username, password, loginType) {
    expect(false).toBe(true); // FIXME: Implement Drupal6
  },
  Drupal8: function(username, password, loginType) {
    expect(false).toBe(true); // FIXME: Implement Drupal8
  },
  Joomla: function(username, password, loginType) {
    expect(false).toBe(true); // FIXME: Implement Joomla
  },
  WordPress: function(username, password, loginType) {
    expect(false).toBe(true); // FIXME: Implement WordPress
  }
};

module.exports = {

  /**
   * Perform a login.
   *
   * @param username String, required
   * @param password String, required
   * @param loginType String, optional
   *   Either 'backend' or 'frontend'.
   *   Default: 'backend'.
   */
  login: function(username, password, loginType) {
    loginType = (loginType === undefined) ? 'backend' : loginType;
    return loginHandlers[_CV.CIVI_UF](username, password, loginType);
  },

  /**
   * Perform a login as administrator.
   *
   * @param loginType String, optional
   *   Default: 'backend'.
   */
  loginAsAdmin: function loginAsAdmin(loginType) {
    loginType = (loginType === undefined) ? 'backend' : loginType;
    return loginHandlers[_CV.CIVI_UF](_CV.ADMIN_USER, _CV.ADMIN_PASS, loginType);
  },

  /**
   * Perform a login as demo user.
   *
   * @param loginType String, optional
   *   Default: 'frontend'.
   */
  loginAsDemo: function loginAsDemo(loginType) {
    loginType = (loginType === undefined) ? 'frontend' : loginType;
    return loginHandlers[_CV.CIVI_UF](_CV.DEMO_USER, _CV.DEMO_PASS, loginType);
  }

};
