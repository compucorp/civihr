/* eslint-env amd, jasmine */

define([
  'common/mocks/data/settings.data',
  'common/angular',
  'common/angularMocks',
  'common/services/api',
  'common/services/format-currency.service'
], function (setingsMock) {
  'use strict';

  describe('FormatCurrencyService', function () {
    var $httpBackend, $rootScope, FormatCurrencyService;

    beforeEach(module('common.apis', 'common.services'));
    beforeEach(inject(function (_FormatCurrencyService_, _$httpBackend_, _$rootScope_) {
      FormatCurrencyService = _FormatCurrencyService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;
    }));

    afterEach(function () {
      $httpBackend.flush();
      $rootScope.$apply();
    });

    describe("when ' and , are thousand and decimal separators respectively", function () {
      beforeEach(function () {
        $httpBackend.whenGET(/entity=Setting/).respond(setingsMock.settings);
      });
      describe('addSeperators()', function () {
        describe('when the amount is without decimal', function () {
          it('formats the given amount using defined decimal and thousands separator', function () {
            FormatCurrencyService.addSeperators(6000000000).then(function (result) {
              expect(result).toEqual("6'000'000'000");
            });
          });
        });

        describe('when the amount is with decimal', function () {
          it('formats the given amount using defined decimal and thousands separator', function () {
            FormatCurrencyService.addSeperators(60000000.67).then(function (result) {
              expect(result).toEqual("60'000'000,67");
            });
          });
        });
      });

      describe('removeCharacters()', function () {
        describe('when the amount is without decimal', function () {
          it('removes the non numerical characters except decimal character "." making it calulable', function () {
            FormatCurrencyService.removeCharacters("799'600'000'00").then(function (result) {
              expect(result).toEqual('79960000000');
              expect(+result).toEqual(jasmine.any(Number));
            });
          });
        });

        describe('when the amount is with decimal', function () {
          it('removes the non numerical characters except decimal character "." making it calulable', function () {
            FormatCurrencyService.removeCharacters("799'600'000,00").then(function (result) {
              expect(result).toEqual('799600000.00');
              expect(+result).toEqual(jasmine.any(Number));
            });
          });
        });
      });
    });

    describe('when # and @ are thousand and decimal separators respectively', function () {
      beforeEach(function () {
        setingsMock.settings.values[0].monetaryThousandSeparator = '#';
        setingsMock.settings.values[0].monetaryDecimalPoint = '@';
        $httpBackend.whenGET(/entity=Setting/).respond(setingsMock.settings);
      });

      describe('addSeperators()', function () {
        describe('when the amount is without decimal', function () {
          it('formats the given amount using defined decimal and thousands separator', function () {
            FormatCurrencyService.addSeperators(6000000000).then(function (result) {
              expect(result).toEqual('6#000#000#000');
            });
          });
        });

        describe('when the amount is with decimal', function () {
          it('formats the given amount using defined decimal and thousands separator', function () {
            FormatCurrencyService.addSeperators(60000000.67).then(function (result) {
              expect(result).toEqual('60#000#000@67');
            });
          });
        });
      });

      describe('removeCharacters()', function () {
        describe('when the amount is without decimal', function () {
          it('removes the non numerical characters except decimal character "." making it calulable', function () {
            FormatCurrencyService.removeCharacters('799#600#000$00').then(function (result) {
              expect(result).toEqual('79960000000');
              expect(+result).toEqual(jasmine.any(Number));
            });
          });
        });

        describe('when the amount is with decimal', function () {
          it('removes the non numerical characters except decimal character "." making it calulable', function () {
            FormatCurrencyService.removeCharacters('799#600#000@00').then(function (result) {
              expect(result).toEqual('799600000.00');
              expect(+result).toEqual(jasmine.any(Number));
            });
          });
        });
      });
    });
  });
});
