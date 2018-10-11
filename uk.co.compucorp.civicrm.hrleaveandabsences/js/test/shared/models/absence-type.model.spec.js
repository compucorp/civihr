/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/mocks/data/absence-type.data',
  'common/mocks/services/api/option-group-mock',
  'leave-absences/shared/models/absence-type.model',
  'leave-absences/mocks/apis/absence-type-api-mock'
], function (_, moment, absenceTypeData) {
  'use strict';

  describe('AbsenceType', function () {
    var $provide, $q, $rootScope, AbsenceType, AbsenceTypeAPI,
      absenceTypeColours, OptionGroup;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks',
      'leave-absences.constants', function (_$provide_) {
        $provide = _$provide_;
      }));

    beforeEach(inject(function (_AbsenceTypeAPIMock_) {
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
    }));

    beforeEach(inject(['api.optionGroup.mock', 'absence-type-colours',
      function (_OptionGroupAPIMock_, _absenceTypeColours_) {
        $provide.value('api.optionGroup', _OptionGroupAPIMock_);

        absenceTypeColours = _absenceTypeColours_;
      }]));

    beforeEach(inject(function (_$q_, _$rootScope_, _AbsenceType_, _AbsenceTypeAPI_, _OptionGroup_) {
      $q = _$q_;
      $rootScope = _$rootScope_;
      AbsenceType = _AbsenceType_;
      AbsenceTypeAPI = _AbsenceTypeAPI_;
      OptionGroup = _OptionGroup_;

      spyOn(AbsenceTypeAPI, 'all').and.callThrough();
      spyOn(AbsenceTypeAPI, 'calculateToilExpiryDate').and.callThrough();
      spyOn(OptionGroup, 'valuesOf').and.callThrough();
    }));

    it('has expected interface', function () {
      expect(Object.keys(AbsenceType)).toEqual([
        'all',
        'calculateToilExpiryDate',
        'canExpire',
        'getUnusedColours',
        'loadCalculationUnits'
      ]);
    });

    describe('all()', function () {
      var results;
      var params = { any: 'param' };

      describe('basic tests', function () {
        beforeEach(function () {
          loadAllAbsenceTypes();
        });

        it('calls equivalent API method', function () {
          expect(AbsenceTypeAPI.all).toHaveBeenCalled();
        });

        it('returns model instances', function () {
          expect(results.every(function (modelInstance) {
            return 'init' in modelInstance;
          })).toBe(true);
        });
      });

      describe('when params are passed', function () {
        beforeEach(function () {
          loadAllAbsenceTypes(params);
        });

        it('calls equivalent API method with params argument', function () {
          expect(AbsenceTypeAPI.all).toHaveBeenCalledWith(params, undefined);
        });
      });

      describe('when additional params are passed', function () {
        var additionalParams = { other: 'param' };

        beforeEach(function () {
          loadAllAbsenceTypes(undefined, additionalParams);
        });

        it('calls equivalent API method with params argument', function () {
          expect(AbsenceTypeAPI.all).toHaveBeenCalledWith(undefined, additionalParams);
        });
      });

      /**
       * Gets absence types and stores them in a results variable
       *
       * @param {Object} params
       * @param {Object} additionalParams
       */
      function loadAllAbsenceTypes (params, additionalParams) {
        AbsenceType.all(params, additionalParams)
          .then(function (_results_) {
            results = _results_;
          });
        $rootScope.$digest();
      }
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

    describe('getUnusedColours()', function () {
      var usedColours;
      var unusedColours;

      beforeEach(function (done) {
        usedColours = _.map(absenceTypeData.all().values, 'color');

        AbsenceType.getUnusedColours()
          .then(function (_unusedColours_) {
            unusedColours = _unusedColours_;
          })
          .finally(done);
        $rootScope.$digest();
      });

      it('fetches only colours from absence types', function () {
        expect(AbsenceTypeAPI.all.calls.mostRecent().args[1].return).toEqual(['color']);
      });

      it('returns allowed colours', function () {
        expect(unusedColours.every(function (color) {
          return _.includes(absenceTypeColours, color);
        })).toBeTruthy();
      });

      it('does not return used colours', function () {
        expect(unusedColours.every(function (color) {
          return !_.includes(usedColours, color);
        })).toBeTruthy();
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
        expect(OptionGroup.valuesOf).toHaveBeenCalledWith(
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
