define([
  'leave-absences/shared/models/public-holiday-model',
  'mocks/apis/public-holiday-api-mock',
  'common/mocks/services/hr-settings-mock',
], function () {
  'use strict'

  describe('PublicHoliday', function () {
    var $provide, PublicHoliday, PublicHolidayAPI, $rootScope;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks', 'common.mocks',
      function (_$provide_) {
        $provide = _$provide_;
      }));

    beforeEach(inject(function (_PublicHolidayAPIMock_, _HR_settingsMock_) {
      $provide.value('PublicHolidayAPI', _PublicHolidayAPIMock_);
      $provide.value('HR_settings', _HR_settingsMock_);
    }));

    beforeEach(inject(function (_PublicHoliday_, _PublicHolidayAPI_, _$rootScope_) {
      PublicHoliday = _PublicHoliday_;
      PublicHolidayAPI = _PublicHolidayAPI_;
      $rootScope = _$rootScope_;

      spyOn(PublicHolidayAPI, 'all').and.callThrough();
    }));

    it('has expected interface', function () {
      expect(Object.keys(PublicHoliday)).toEqual(['all', 'isPublicHoliday']);
    });

    describe('all()', function () {
      var promise;

      beforeEach(function () {
        promise = PublicHoliday.all();
      });

      afterEach(function () {
        //to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        promise.then(function (response) {
          expect(PublicHolidayAPI.all).toHaveBeenCalled();
        });
      });

      it('returns model instances', function () {
        promise.then(function (response) {
          expect(response.every(function (modelInstance) {
            return 'init' in modelInstance;
          })).toBe(true);
        });
      });
    });

    describe('isPublicHoliday()', function () {
      describe('when given date is public holiday', function () {
        var promise, testDate = '2016-01-01';

        beforeEach(function () {
          promise = PublicHoliday.isPublicHoliday(testDate);
        });

        afterEach(function () {
          //to excute the promise force an digest
          $rootScope.$apply();
        });

        it('calls equivalent API method', function () {
          promise.then(function (response) {
            expect(PublicHolidayAPI.all).toHaveBeenCalled();
          });
        });

        it('returns true', function () {
          promise.then(function (response) {
            expect(response).toBe(true);
          });
        });
      });

      describe('when given date is not a public holiday', function () {
        var promise, testDate = '2016-01-02';

        beforeEach(function () {
          promise = PublicHoliday.isPublicHoliday(testDate);
        });

        afterEach(function () {
          //to excute the promise force an digest
          $rootScope.$apply();
        });

        it('returns false', function () {
          promise.then(function (response) {
            expect(response).toBe(false);
          });
        });
      });
    });
  });
});
