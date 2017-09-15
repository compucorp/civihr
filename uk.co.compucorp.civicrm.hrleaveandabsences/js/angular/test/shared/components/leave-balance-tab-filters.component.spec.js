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
    var $componentController, $provide, $rootScope, $scope, AbsencePeriod,
      absencePeriods, AbsenceType, absenceTypes, ctrl;
    var loggedInContactId = 101;
    var userRole = 'admin';

    beforeEach(module('leave-absences.components', 'leave-absences.mocks',
    'leave-absences.models', 'leave-absences.settings', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (AbsencePeriodAPIMock, AbsenceTypeAPIMock) {
      $provide.value('AbsencePeriodAPI', AbsencePeriodAPIMock);
      $provide.value('AbsenceTypeAPI', AbsenceTypeAPIMock);
    }));

    beforeEach(inject(function (_$componentController_, _$rootScope_,
    _AbsencePeriod_, _AbsenceType_) {
      $componentController = _$componentController_;
      $rootScope = _$rootScope_;
      AbsencePeriod = _AbsencePeriod_;
      AbsenceType = _AbsenceType_;
    }));

    beforeEach(function () {
      setupController();
    });

    describe('on init', function () {
      it('sets filters to null', function () {
        expect(ctrl.filters).toEqual({
          period_id: null,
          type_id: null,
          managed_by: null
        });
      });
    });

    describe('on changes', function () {
      describe('when absence periods change', function () {
        var period;

        beforeEach(function () {
          period = absencePeriods.find(function (p) { return p.current; });

          controllerOnChanges('absencePeriods', absencePeriods);
        });

        it('selects the current absence period', function () {
          expect(ctrl.filters.period_id).toBe(period.id);
        });

        describe('when there are no current periods', function () {
          beforeEach(function () {
            absencePeriods.forEach(function (period) {
              period.current = false;
            });

            period = ctrl.absencePeriods.reduce(function (periodA, periodB) {
              return moment(periodA.end_date).isAfter(periodB.end_date)
                ? periodA
                : periodB;
            });

            controllerOnChanges('absencePeriods', absencePeriods);
          });

          it('selects the most recent absence period', function () {
            expect(ctrl.filters.period_id).toBe(period.id);
          });
        });
      });

      describe('when absence types change', function () {
        var type;

        beforeEach(function () {
          type = absenceTypes.reduce(function (typeA, typeB) {
            return typeA.title.localeCompare(typeB.title) ? typeA : typeB;
          });

          controllerOnChanges('absenceTypes', absenceTypes);
        });

        it('selects the first absence type sorted by title', function () {
          expect(ctrl.filters.type_id).toBe(type.id);
        });
      });

      describe('when logged in contact\'s id or user role change', function () {
        describe('when user role is "admin"', function () {
          beforeEach(function () {
            controllerOnChanges('loggedInContactId', loggedInContactId);
            controllerOnChanges('userRole', 'admin');
          });

          it('does not set managed_by filter', function () {
            expect(ctrl.filters.managed_by).toBe(undefined);
          });
        });

        describe('when user role is "manager"', function () {
          beforeEach(function () {
            controllerOnChanges('loggedInContactId', loggedInContactId);
            controllerOnChanges('userRole', 'manager');
          });

          it('sets managed_by filter as the currently logged in user\'s ID', function () {
            expect(ctrl.filters.managed_by).toBe(loggedInContactId);
          });
        });
      });

      describe('when all filters are ready', function () {
        beforeEach(function () {
          controllerOnChanges('absencePeriods', absencePeriods);
          controllerOnChanges('absenceTypes', absenceTypes);
          controllerOnChanges('loggedInContactId', loggedInContactId);
          controllerOnChanges('userRole', userRole);
        });

        it('emits a "Filters Update" event with the selected filter values', function () {
          expect($scope.$emit).toHaveBeenCalledWith('LeaveBalanceFilters::update', ctrl.filters);
        });
      });

      describe('when absence periods and types are updated with empty values', function () {
        beforeEach(function () {
          controllerOnChanges('absencePeriods', []);
          controllerOnChanges('absenceTypes', []);
          controllerOnChanges('loggedInContactId', loggedInContactId);
        });

        it('does not emit a filters updated event', function () {
          expect($scope.$emit).not.toHaveBeenCalled();
        });
      });
    });

    describe('submitFilters()', function () {
      beforeEach(function () {
        controllerOnChanges('absencePeriods', absencePeriods);
        controllerOnChanges('absenceTypes', absenceTypes);
        controllerOnChanges('loggedInContactId', loggedInContactId);

        ctrl.submitFilters();
      });

      it('emits a "Filters Update" event with the selected filter values', function () {
        expect($scope.$emit).toHaveBeenCalledWith('LeaveBalanceFilters::update', ctrl.filters);
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

      $scope = $rootScope.$new();

      spyOn($scope, '$emit');

      ctrl = $componentController('leaveBalanceTabFilters', {
        $scope: $scope
      }, {
        absencePeriods: [],
        absenceTypes: [],
        loggedInContactId: null,
        userRole: userRole
      });
    }

    /**
     * Simulates the controller's $onChanges hook for a given field and value.
     *
     * @param {String} fieldName - The binding's name.
     * @param {Any} currentValue - The new value for the binding.
     * @param {Any} [previousValue=Array] - The previous value of the binding.
                                            Defaults to an empty array.
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
