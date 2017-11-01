/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/angularMocks',
  'common/services/format-currency.service'
], function (angular, _) {
  'use strict';

  describe('FormatCurrencyService', function () {
    var $rootScope, FormatCurrencyService, Settings, deffered, promise;

    beforeEach(module('common.services'));
    beforeEach(module(function ($provide) {
      $provide.factory('Settings', function () {
        return {
          fetchSeparators: jasmine.any(Function)
        };
      });
    }));

    beforeEach(inject(function (_$q_, _$rootScope_, _Settings_, _FormatCurrencyService_) {
      $rootScope = _$rootScope_;
      Settings = _Settings_;
      FormatCurrencyService = _FormatCurrencyService_;
      deffered = _$q_.defer();
    }));

    var testMap = [
      // [thousandSeparator, decimalSeparator, inputAmount, outputAmount]
      // Common cases
      [',', '*', 0.0, '0'],
      [',', '*', 'xxxxx', '0'],
      [',', '*', '0.8889', '0*88'],
      [',', '*', '-0.8889', '0*88'],
      [',', '*', 12345675, '12,345,675'],
      [',', '*', 123456.75, '123,456*75'],
      [',', '*', '123456.7.5', '12,345,675'],
      [',', '*', '12345675', '12,345,675'],
      [',', '*', '12,345,675', '12,345,675'],
      [',', '*', '00,456,75*76', '45,675*76'],
      [',', '*', '12345675*76', '12,345,675*76'],
      [',', '*', '12,34,56*75*76', '123,456*75'],
      [',', '*', '123 456 75*76', '12,345,675*76'],

      // Edge cases
      ['?', '^', 'abcd!89', '89'],
      ['?', '!', '00000!89', '0!89'],
      ["'", ',', '1234567,76', "1'234'567,76"],
      ['*', '^', '1234567^70', '1*234*567^70'],
      ['$', '%', '12#345&67^76', '123$456$776'],
      [' ', ',', '1234567,76', '1 234 567,76'],
      ['^', ']', '12,34,567]76', '1^234^567]76'],
      ['#', ')', '1#234#567)76', '1#234#567)76'],
      ['!', '@', '   78  9098 @65', '789!098@65'],
      ["'", ',', '1234567,7698', "1'234'567,76"],
      ['.', ',', '1.234,56776', '1.234,56']
    ];

    _.forEach(testMap, function (test) {
      describe('format()', function () {
        beforeEach(function () {
          spyOn(Settings, 'fetchSeparators').and.callFake(function () {
            deffered.resolve({
              decimal: test[1],
              thousand: test[0]
            });

            return deffered.promise;
          });

          promise = FormatCurrencyService.format(test[2]);
        });

        afterEach(function () {
          $rootScope.$apply();
        });

        it('formats ' + test[2] + ' to ' + test[3] + ' by ' + test[1] + ' and ' + test[0] + ' as decimal and thousand separators', function () {
          promise.then(function (amount) {
            expect(amount.formatted).toBe(test[3]);
            expect(+amount.parsed).toEqual(jasmine.any(Number));
          });
        });
      });
    });
  });
});
