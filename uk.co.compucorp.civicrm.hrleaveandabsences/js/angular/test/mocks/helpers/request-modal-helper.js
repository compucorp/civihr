/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'leave-absences/mocks/data/absence-period.data',
  'leave-absences/mocks/data/absence-type.data',
  'leave-absences/mocks/helpers/helper'
], function (_, absencePeriodData, absenceTypeData, helper) {
  return {
    /**
     * Appends default values to the controller initialiation.
     *
     * @param {Object} params - the object to wich defaults will be appented to.
     * properties and defaults:
     * - {Array} absencePeriods - a list of absence periods. Defaults to all absence periods.
     * - {Array} absenceTypes - a list of absence types. Defaults to all absence types.
     * - {Object} balance - the request balance. Defaults to the globally defined balance.
     * - {JasmineSpy} checkSubmitConditions - a spy to execute the checkSubmitConditions callback.
     * - {JasmineSpy} isLeaveStatus - a spy to execute the isLeaveStatus callback.
     * - {String} leaveType - the leave absence type. Options are "leave", "sick", "toil". Defaults to "leave".
     * - {Object} period - the currently selected period. Defaults to first period.
     * - {Object} selectedAbsenceType - the selected absence type. Defaults to the first absence type, and sets remainder value to 0.
     * - {Object} request - The leave request data.
     * - {JasmineSpy} isMode - a isMode spy function.
     * - {JasmineSpy} isRole - a isRole spy function.
     */
    addDefaultComponentParams: function (params) {
      var AbsencePeriodInstance = getDependency('AbsencePeriodInstance');

      this.addSpyParams(params);

      var defaultParams = {
        absencePeriods: absencePeriodData.all().values.map(function (period) {
          return AbsencePeriodInstance.init(period);
        }),
        absenceTypes: absenceTypeData.all().values,
        balance: {
          closing: 0,
          opening: 0,
          change: {
            amount: 0,
            breakdown: []
          }
        },
        checkSubmitConditions: params.checkSubmitConditions,
        isLeaveStatus: params.isLeaveStatus,
        leaveType: 'leave',
        period: absencePeriodData.all().values[0],
        selectedAbsenceType: _.assign(absenceTypeData.all().values[0], {
          remainder: 0
        }),
        isMode: params.isMode,
        isRole: params.isRole
      };

      _.defaults(params, defaultParams);
    },

    /**
     * Appends default spy functions to the params object.
     *
     * @param {Object} params - the object which spy functions will be appened to.
     */
    addSpyParams: function (params) {
      var defaultParams = {
        mode: 'create',
        role: 'staff'
      };

      _.defaults(params, defaultParams);

      params.isMode = jasmine.createSpy('isMode')
        .and.callFake(function (mode) {
          return mode === params.mode;
        });

      params.isRole = jasmine.createSpy('isRole')
        .and.callFake(function (role) {
          return role === params.role;
        });

      params.checkSubmitConditions = jasmine.createSpy('checkSubmitConditions');
      params.isLeaveStatus = jasmine.createSpy('isLeaveStatus')
        .and.callFake(function (statusName) {
          return helper.getStatusValueFromName(statusName) === params.request.status_id;
        });
    },

    /**
     * Sets "from" and/or "to" dates
     *
     * @param {object} controller controller object
     * @param {String} from date set if passed
     * @param {String} to date set if passed
     */
    setTestDates: function (controller, from, to) {
      var $rootScope = getDependency('$rootScope');

      if (from) {
        controller.uiOptions.fromDate = helper.getUTCDate(from);
        controller.dateChangeHandler('from');
        $rootScope.$digest();
      }

      if (to) {
        controller.uiOptions.toDate = helper.getUTCDate(to);
        controller.dateChangeHandler('to');
        $rootScope.$digest();
      }
    },

    /**
     * Sets "from" and/or "to" times
     *
     * @param {Object} controller - controller object
     * @param {String} from - time in HH:mm format, optional
     * @param {String} to - time in HH:mm format, optional
     */
    setTestTimes: function (controller, from, to) {
      var $rootScope = getDependency('$rootScope');

      if (from) {
        controller.uiOptions.times.from.time = from;
        $rootScope.$digest();
      }

      if (to) {
        controller.uiOptions.times.to.time = to;
        $rootScope.$digest();
      }
    }
  };

  /**
   * Get an angular dependency
   *
   * @param {String} dependencyName name of the dependency
   * @return {Object} requested dependency
   */
  function getDependency (dependencyName) {
    var dependency;

    inject([dependencyName, function (_dependency_) {
      dependency = _dependency_;
    }]);

    return dependency;
  }
});
