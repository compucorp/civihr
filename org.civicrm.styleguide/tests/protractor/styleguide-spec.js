var LoginPage = require('./lib/login-page');
var NavBar = require('./lib/navbar');
var prtr = require('./lib/protractor-util');

describe('civicrm style-guide', function() {

  it('should display the crm-star styleguide', function() {
    prtr.withoutAngular(function() {
      LoginPage.loginAsAdmin();

      NavBar.goto(['Support', 'Developer', 'Style Guide', 'crm-*']);

      expect(browser.getCurrentUrl()).toMatch(/civicrm\/styleguide\/crm-star/)
      expect(element(by.css('.sg-body')).getText()).toMatch(/demonstrates the traditional CSS elements/);
    });
  });

});
