/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'common/services/angular-date/date-format',
  'common/services/hr-settings'
], function () {
  'use strict';

  describe('angularDate: DateFormat Unit Test', function () {
    var DateFormat, HRSettings;

    beforeEach(module('common.angularDate', 'common.services'));
    beforeEach(inject(['DateFormat', 'HR_settings',
      function (_DateFormat_, _HRSettings_) {
        DateFormat = _DateFormat_;
        HRSettings = _HRSettings_;
      }
    ]));

    it('DateFormat to be defined', function () {
      expect(DateFormat).toBeDefined();
    });

    it('Initial values should be null', function () {
      expect(DateFormat.dateFormat).toBe(null);
      expect(HRSettings.DATE_FORMAT).toBe(null);
    });

    describe('DateFormat - Async calls', function () {
      it('Should fetch Date format', function () {
        spyOn(DateFormat, 'getDateFormat').and.callFake(function () {
          return {
            then: function (callback) {
              return callback(String('DD/MM/YYYY'));
            }
          };
        });

        DateFormat.getDateFormat().then(function (result) {
          HRSettings.DATE_FORMAT = result;
        });

        expect(DateFormat.getDateFormat).toHaveBeenCalled();
        expect(HRSettings.DATE_FORMAT).toEqual('DD/MM/YYYY');
      });
    });
  });
});
