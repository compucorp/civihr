'use strict';

module.exports = function (casper) {
  var config = require('../site-config');
  var loginFormSelector = 'form#user-login-form';

  casper
    .echo('Logging in before starting...', 'INFO')
    .thenOpen(config.url + '/welcome-page', function () {
      casper.then(function () {
        casper.waitForSelector(loginFormSelector, function () {
          casper.waitWhileSelector(loginFormSelector, function () {
            casper.echo('Logged in', 'INFO');
          }, function () {
            casper.echo('Login form visible and timeout reached!', 'RED_BAR');
          }, 5000);
          casper.fill(loginFormSelector, config.credentials, true);
        }, function () {
          casper.echo('Login form not found!', 'RED_BAR');
        }, 8000);
      });
  });
};
