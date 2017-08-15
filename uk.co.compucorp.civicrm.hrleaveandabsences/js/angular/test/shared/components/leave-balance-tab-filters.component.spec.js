/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/moment',
  'mocks/data/absence-period-data',
  'mocks/data/absence-type-data',
  'leave-absences/shared/components/leave-balance-tab-filters.component',
  'leave-absences/shared/modules/shared-settings'
], function (angular, moment) {
  'use strict';

  describe('leaveBalanceTabFilters', function () {
    var $componentController, $provide, $rootScope, AbsencePeriod, absencePeriods, AbsenceType, absenceTypes, ctrl;

    beforeEach(module('leave-absences.components', 'leave-absences.mocks', 'leave-absences.models', 'leave-absences.settings', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (AbsencePeriodAPIMock, AbsenceTypeAPIMock) {
      $provide.value('AbsencePeriodAPI', AbsencePeriodAPIMock);
      $provide.value('AbsenceTypeAPI', AbsenceTypeAPIMock);
    }));

    beforeEach(inject(function (_$componentController_, _$rootScope_, _AbsencePeriod_, _AbsenceType_) {
      $componentController = _$componentController_;
      $rootScope = _$rootScope_;
      AbsencePeriod = _AbsencePeriod_;
      AbsenceType = _AbsenceType_;
    }));

    beforeEach(function () {
      setupController();
    });

    describe('on init', function () {
      it('sets filters object with absencePeriod and absenceType set to null', function () {
        expect(ctrl.filters).toEqual({
          absencePeriod: null,
          absenceType: null
        });
      });
    });

    describe('on changes', function () {
      var expected;

      describe('when absence periods are updated', function () {
        beforeEach(function () {
          expected = absencePeriods.find(function (p) { return p.current; });

          controllerOnChanges('absencePeriods', absencePeriods);
        });

        it('selects the current absence period', function () {
          expect(ctrl.filters.absencePeriod).toBe(expected);
        });

        describe('when there are no current periods', function () {
          beforeEach(function () {
            absencePeriods.forEach(function (period) {
              period.current = false;
            });

            expected = ctrl.absencePeriods.reduce(function (a, b) {
              return moment(a.end_date).isAfter(b.end_date) ? a : b;
            });

            controllerOnChanges('absencePeriods', absencePeriods);
          });

          it('selects the most recent absence period', function () {
            expect(ctrl.filters.absencePeriod).toBe(expected);
          });
        });
      });

      describe('when absence types are updated', function () {
        beforeEach(function () {
          expected = absenceTypes.reduce(function (a, b) {
            return a.title.localeCompare(b.title) ? a : b;
          });

          controllerOnChanges('absenceTypes', absenceTypes);
        });

        it('selects the first absence type sorted by title', function () {
          expect(ctrl.filters.absenceType).toBe(expected);
        });
      });

      describe('when absence periods and types are updated', function () {
        beforeEach(function () {
          controllerOnChanges('absencePeriods', absencePeriods);
          controllerOnChanges('absenceTypes', absenceTypes);
        });

        it('emits a on filter change event with the selected absence period and type', function () {
          expect(ctrl.onFiltersChange).toHaveBeenCalledWith({
            $filters: ctrl.filters
          });
        });
      });

      describe('when absence periods and types are updated with empty values', function () {
        beforeEach(function () {
          controllerOnChanges('absencePeriods', []);
          controllerOnChanges('absenceTypes', []);
        });

        it('emits an on filter change event with the selected absence period and type', function () {
          expect(ctrl.onFiltersChange).not.toHaveBeenCalled();
        });
      });
    });

    describe('filter()', function () {
      beforeEach(function () {
        controllerOnChanges('absencePeriods', absencePeriods);
        controllerOnChanges('absenceTypes', absenceTypes);
        ctrl.onFiltersChange.calls.reset();

        ctrl.filter();
      });

      it('emits an on filters change event with the selected filters', function () {
        expect(ctrl.onFiltersChange).toHaveBeenCalledWith({ $filters: ctrl.filters });
      });
    });

    /**
     * Setups the leaveBalanceTabFilters controller for testing purposes.
     * It assign absence periods and types with any transformations performed by
     * their model.
     */
    function setupController () {
      AbsencePeriod.all().then(function (values) {
        absencePeriods = values;
      });

      AbsenceType.all().then(function (values) {
        absenceTypes = values;
      });

      $rootScope.$digest();

      ctrl = $componentController('leaveBalanceTabFilters', null, {
        absencePeriods: [],
        absenceTypes: [],
        onFiltersChange: jasmine.createSpy('onFiltersChange')
      });
    }

    /**
     * Simulates the controller's $onChanges hook for a given field and value.
     *
     * @param {String} fieldName - the binding's name.
     * @param {Any} currentValue - the new value for the binding.
     * @param {Any} [previousValue=Array] - the previous value of the binding. Defaults to an empty array.
     */
    function controllerOnChanges (fieldName, currentValue, previousValue) {
      var changes = {};
      previousValue = previousValue || [];

      changes[fieldName] = {
        currentValue: currentValue,
        previousValue: previousValue
      };

      ctrl[fieldName] = currentValue;
      ctrl.$onChanges(changes);
    }
  });
});
