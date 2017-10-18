/* eslint-env amd, jasmine */

define([
  'common/moment',
  'leave-absences/shared/models/absence-type.model',
  'mocks/apis/absence-type-api-mock'
], function (moment) {
  'use strict';

  describe('AbsenceType', function () {
    var $provide, AbsenceType, AbsenceTypeAPI, $rootScope, $q;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_AbsenceTypeAPIMock_) {
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
    }));

    beforeEach(inject(function (_AbsenceType_, _AbsenceTypeAPI_, _$rootScope_, _$q_) {
      AbsenceType = _AbsenceType_;
      AbsenceTypeAPI = _AbsenceTypeAPI_;
      $q = _$q_;
      $rootScope = _$rootScope_;

      spyOn(AbsenceTypeAPI, 'all').and.callThrough();
      spyOn(AbsenceTypeAPI, 'calculateToilExpiryDate').and.callThrough();
    }));

    it('has expected interface', function () {
      expect(Object.keys(AbsenceType)).toEqual([
        'all',
        'calculateToilExpiryDate',
        'canExpire'
      ]);
    });

    describe('all()', function () {
      var absenceTypePromise;

      beforeEach(function () {
        absenceTypePromise = AbsenceType.all();
      });

      afterEach(function () {
        // to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        absenceTypePromise.then(function (response) {
          expect(AbsenceTypeAPI.all).toHaveBeenCalled();
        });
      });

      it('returns model instances', function () {
        absenceTypePromise.then(function (response) {
          expect(response.every(function (modelInstance) {
            return 'init' in modelInstance;
          })).toBe(true);
        });
      });
    });

    describe('calculateToilExpiryDate()', function () {
      var absenceTypePromise;
      var absenceTypeID = 2;
      var date = moment();
      var params = {
        key: 'value'
      };

      beforeEach(function () {
        absenceTypePromise = AbsenceType.calculateToilExpiryDate(absenceTypeID, date, params);
      });

      afterEach(function () {
        // to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        absenceTypePromise.then(function () {
          expect(AbsenceTypeAPI.calculateToilExpiryDate).toHaveBeenCalledWith(absenceTypeID, date, params);
        });
      });
    });

    describe('canExpire()', function () {
      describe('passing api parameters', function () {
        var absenceTypeId = 999;

        beforeEach(function () {
          AbsenceType.canExpire(absenceTypeId);
        });

        it('should pass appropiate filter parameters', function () {
          expect(AbsenceTypeAPI.all).toHaveBeenCalledWith({
            accrual_expiration_unit: { 'IS NOT NULL': 1 },
            accrual_expiration_duration: { 'IS NOT NULL': 1 },
            allow_accruals_request: 1,
            id: absenceTypeId,
            options: { limit: 1 },
            return: ['id']
          });
        });
      });

      describe('absence type can expire', function () {
        var absenceTypeId = 1;

        beforeEach(function () {
          AbsenceTypeAPI.all.and.returnValue($q.resolve([]));
        });

        it('should return true', function () {
          AbsenceType.canExpire(absenceTypeId).then(function (expires) {
            expect(expires).toBe(true);
          });
        });
      });

      describe('absence type does not expire', function () {
        var absenceTypeId = 2;

        it('should return false', function () {
          AbsenceType.canExpire(absenceTypeId).then(function (expires) {
            expect(expires).toBe(false);
          });
        });
      });
    });
  });
});
