/* eslint-env amd, jasmine */

define([
  'mocks/apis/absence-period-api-mock',
  'leave-absences/absence-tab/components/annual-entitlement-change-log.component'
], function () {
  describe('Annual entitlement change log', function () {
    var $provide, $rootScope, AbsencePeriod, ctrl;
    var contactId = 204;
    var periodId = 304;

    beforeEach(module('leave-absences.mocks', 'absence-tab',
    function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (AbsencePeriodAPIMock) {
      $provide.value('AbsencePeriodAPI', AbsencePeriodAPIMock);
    }));

    beforeEach(inject(function ($componentController, _$rootScope_,
    _AbsencePeriod_) {
      $rootScope = _$rootScope_;
      AbsencePeriod = _AbsencePeriod_;

      spyOn(AbsencePeriod, 'all').and.callThrough();

      ctrl = $componentController('annualEntitlementChangeLog', null, {
        contactId: contactId,
        periodId: periodId
      });
    }));

    describe('on init', function () {
      it('sets absence period equal to null', function () {
        expect(ctrl.absencePeriod).toBe(null);
      });

      it('sets loading component to true', function () {
        expect(ctrl.loading.component).toBe(true);
      });

      it('gets the absence period using the provided period id', function () {
        expect(AbsencePeriod.all).toHaveBeenCalledWith({
          id: periodId
        });
      });
    });

    describe('after init', function () {
      var expectedAbsencePeriod;

      beforeEach(function () {
        AbsencePeriod.all({
          id: periodId
        })
        .then(function (periods) {
          expectedAbsencePeriod = periods[0];
        });

        $rootScope.$digest();
      });

      it('stores the absence period', function () {
        expect(ctrl.absencePeriod).toEqual(expectedAbsencePeriod);
      });

      it('sets loading component to false', function () {
        expect(ctrl.loading.component).toBe(false);
      });
    });
  });
});
