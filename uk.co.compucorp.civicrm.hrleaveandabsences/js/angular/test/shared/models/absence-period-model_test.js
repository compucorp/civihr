define([
  'leave-absences/shared/models/absence-period-model',
  'mocks/apis/absence-period-api-mock',
  'common/mocks/services/hr-settings-mock',
], function () {
  'use strict'

  describe('AbsencePeriod', function () {
    var $provide, AbsencePeriod, AbsencePeriodAPI, $rootScope;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks', 'common.mocks',
      function (_$provide_) {
        $provide = _$provide_;
      }));

    beforeEach(inject(function (_AbsencePeriodAPIMock_, _HR_settingsMock_) {
      $provide.value('AbsencePeriodAPI', _AbsencePeriodAPIMock_);
      $provide.value('HR_settings', _HR_settingsMock_);
    }));

    beforeEach(inject(function (_AbsencePeriod_, _AbsencePeriodAPI_, _$rootScope_) {
      AbsencePeriod = _AbsencePeriod_;
      AbsencePeriodAPI = _AbsencePeriodAPI_;
      $rootScope = _$rootScope_;

      spyOn(AbsencePeriodAPI, 'all').and.callThrough();
    }));

    it('has expected interface', function () {
      expect(Object.keys(AbsencePeriod)).toEqual(['all', 'current']);
    });

    describe('all()', function () {
      var promise;

      beforeEach(function () {
        promise = AbsencePeriod.all();
      });

      afterEach(function () {
        //to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        promise.then(function (response) {
          expect(AbsencePeriodAPI.all).toHaveBeenCalled();
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

    describe('current()', function () {
      var promise;

      beforeEach(function () {
        //shift current date to precede mock periods
        var currentDate = new Date(2016, 6, 6);

        jasmine.clock().mockDate(currentDate);
        promise = AbsencePeriod.current();
      });

      afterEach(function () {
        //to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        promise.then(function (response) {
          expect(AbsencePeriodAPI.all).toHaveBeenCalled();
        });
      });

      it('returns model instance', function () {
        promise.then(function (response) {
          expect(response.init).toBeDefined();
        });
      });

      describe('with past absence periods', function () {
        var promise;

        beforeEach(function () {
          //shift current date to precede mock periods
          var pastDate = new Date(2013, 1, 1);

          jasmine.clock().mockDate(pastDate);
          promise = AbsencePeriod.current();
        });

        afterEach(function () {
          //to excute the promise force an digest
          $rootScope.$apply();
        });

        it('calls equivalent API method', function () {
          promise.then(function (response) {
            expect(AbsencePeriodAPI.all).toHaveBeenCalled();
          });
        });

        it('returns no model instance', function () {
          promise.then(function (response) {
            expect(response).toBeNull();
          });
        });
      });
    });
  });
});
