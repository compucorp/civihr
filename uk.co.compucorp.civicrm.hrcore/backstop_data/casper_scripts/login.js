'use strict';

module.exports = function (casper, scenario) {
  var config = require('../site-config');
  var loginFormSelector = 'form#user-login-form';
  var credentials = config.credentials[scenario.credentials || 'default'];

  casper
    .echo('Logging in with ' + (scenario.credentials || 'default') + ' credentials before starting ...', 'INFO')
    .thenOpen(config.url + '/welcome-page', function () {
      casper.then(function () {
        casper.waitForSelector(loginFormSelector, function () {
          casper.waitWhileSelector(loginFormSelector, function () {
            casper.echo('Logged in', 'INFO');
          }, function () {
            casper.echo('Login form visible and timeout reached!', 'RED_BAR');
          }, 5000);
          casper.fill(loginFormSelector, credentials, true);
        }, function () {
          casper.echo('Login form not found!', 'RED_BAR');
        }, 8000);
      });
  });
};
