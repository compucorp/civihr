/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'common/mocks/services/api/option-group-mock',
  'leave-absences/shared/models/absence-type.model',
  'mocks/apis/absence-type-api-mock'
], function (_, moment) {
  'use strict';

  describe('AbsenceType', function () {
    var $provide, AbsenceType, AbsenceTypeAPI, OptionGroupAPI, $rootScope, $q;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_AbsenceTypeAPIMock_) {
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
    }));

    beforeEach(inject(['api.optionGroup.mock', 'api.optionGroup.mock', function (_OptionGroupAPI_, _OptionGroupAPIMock_) {
      OptionGroupAPI = _OptionGroupAPI_;

      $provide.value('api.optionGroup', _OptionGroupAPIMock_);
    }]));

    beforeEach(inject(function (_AbsenceType_, _AbsenceTypeAPI_, _$rootScope_, _$q_) {
      AbsenceType = _AbsenceType_;
      AbsenceTypeAPI = _AbsenceTypeAPI_;
      $q = _$q_;
      $rootScope = _$rootScope_;

      spyOn(AbsenceTypeAPI, 'all').and.callThrough();
      spyOn(AbsenceTypeAPI, 'calculateToilExpiryDate').and.callThrough();
      spyOn(OptionGroupAPI, 'valuesOf').and.callThrough();
    }));

    it('has expected interface', function () {
      expect(Object.keys(AbsenceType)).toEqual([
        'all',
        'calculateToilExpiryDate',
        'canExpire',
        'loadCalculationUnits'
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

    describe('loadCalculationUnits()', function () {
      var absenceTypes, result;
      beforeEach(function () {
        AbsenceType.all().then(function (_absenceTypes_) {
          absenceTypes = _.cloneDeep(_absenceTypes_);

          return AbsenceType.loadCalculationUnits(_absenceTypes_);
        }).then(function (_result_) {
          result = _.cloneDeep(_result_);
        });
        $rootScope.$digest();
      });

      it('retrieves calculation unit option group', function () {
        expect(OptionGroupAPI.valuesOf).toHaveBeenCalledWith(
          'hrleaveandabsences_absence_type_calculation_unit');
      });

      it('sets calculation unit properties', function () {
        expect(result[0]).toEqual(
          _.assign(absenceTypes[0], {
            calculation_unit_name: 'days',
            calculation_unit_label: 'Days'
          }));
      });
    });
  });
});
