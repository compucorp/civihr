/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/moment',
  'common/angularMocks',
  'common/filters/angular-date/format-date',
  'common/services/angular-date/date-format',
  'common/services/hr-settings'
], function (angular, moment) {
  'use strict';

  describe('FormatDateFilter', function () {
    var HRSettings, $filter;

    beforeEach(module('common.angularDate'));
    beforeEach(inject(['HR_settings', '$filter',
      function (_HRSettings, _$filter) {
        HRSettings = _HRSettings;
        $filter = _$filter;
      }
    ]));

    it('is defined', function () {
      expect($filter('formatDate')).toBeDefined();
    });

    it('accepts a format parameter', function () {
      expect($filter('formatDate')(moment(), 'D/M/YY')).toEqual(moment().format('D/M/YY'));
    });

    describe('When HR_settings.DATE_FORMAT is not defined', function () {
      it('falls back to "YYYY-MM-DD"', function () {
        expect($filter('formatDate')(moment())).toEqual(moment().format('YYYY-MM-DD'));
      });
    });

    describe('When HR_settings.DATE_FORMAT is defined', function () {
      beforeEach(function () {
        HRSettings.DATE_FORMAT = 'DD/MM/YYYY';
      });

      it('uses HR_settings.DATE_FORMAT', function () {
        expect($filter('formatDate')(moment())).toEqual(moment().format('DD/MM/YYYY'));
      });

      describe('When the format parameter is passed', function () {
        it('has the priority over HR_settings.DATE_FORMAT', function () {
          expect($filter('formatDate')(moment(), 'MM-DD-YY')).toEqual(moment().format('MM-DD-YY'));
        });
      });
    });

    describe('Missing or invalid dates', function () {
      it('returns "Unspecified"', function () {
        expect($filter('formatDate')('')).toEqual('Unspecified');
        expect($filter('formatDate')('29/02/2011')).toEqual('Unspecified');
        expect($filter('formatDate')('0000-00-00 00:00:00')).toEqual('Unspecified');
        expect($filter('formatDate')('testString')).toEqual('Unspecified');
        expect($filter('formatDate')(undefined)).toEqual('Unspecified');
        expect($filter('formatDate')(null)).toEqual('Unspecified');
      });
    });

    describe('Valid dates', function () {
      describe('Formatting to string', function () {
        var dateString;

        beforeEach(function () {
          dateString = '2016-01-22';
        });

        it('formats a moment', function () {
          expect($filter('formatDate')(moment('2016-01-22'))).toBe(dateString);
        });

        it('formats a Date object', function () {
          expect($filter('formatDate')(new Date(2016, 0, 22))).toBe(dateString);
        });

        it('formats a string date', function () {
          expect($filter('formatDate')('22-01-2016')).toBe(dateString);
          expect($filter('formatDate')('22/01/2016')).toBe(dateString);
        });

        it('formats a timestamp', function () {
          expect($filter('formatDate')((new Date(2016, 0, 22)).valueOf())).toBe(dateString);
        });
      });

      describe('Formatting to Date object', function () {
        var dateObj, dateTimeObj;

        beforeEach(function () {
          dateObj = new Date(2016, 0, 22);
          dateTimeObj = new Date(2016, 0, 22, 7, 30, 25);
        });

        it('formats a moment', function () {
          expect($filter('formatDate')(moment('2016-01-22'), Date)).toEqual(dateObj);
        });

        it('formats a Date object', function () {
          expect($filter('formatDate')(dateObj, Date)).toEqual(dateObj);
        });

        it('formats a string date', function () {
          expect($filter('formatDate')('22-01-2016', Date)).toEqual(dateObj);
          expect($filter('formatDate')('22-01-2016 07:30:25', Date)).toEqual(dateTimeObj);
          expect($filter('formatDate')('22/01/2016', Date)).toEqual(dateObj);
          expect($filter('formatDate')('2016-01-22', Date)).toEqual(dateObj);
        });

        it('formats a timestamp', function () {
          expect($filter('formatDate')((dateObj).valueOf(), Date)).toEqual(dateObj);
        });
      });
    });
  });
});
