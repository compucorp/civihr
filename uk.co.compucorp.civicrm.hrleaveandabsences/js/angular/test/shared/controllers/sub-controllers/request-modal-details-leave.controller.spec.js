/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/moment',
  'leave-absences/mocks/data/absence-period.data',
  'leave-absences/mocks/data/absence-type.data',
  'leave-absences/mocks/data/leave-request.data',
  'leave-absences/mocks/helpers/request-modal-helper',
  'leave-absences/manager-leave/app'
], function (angular, _, moment, absencePeriodData, absenceTypeData, leaveRequestData, requestModalHelper) {
  'use strict';

  describe('RequestModalDetailsLeaveController', function () {
    var $componentController, $provide, $log, $rootScope, controller, leaveRequest,
      AbsencePeriodInstance, LeaveRequestInstance, selectedAbsenceType;

    beforeEach(module('common.mocks', 'leave-absences.templates', 'leave-absences.mocks', 'manager-leave', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_PublicHolidayAPIMock_, _WorkPatternAPIMock_, _OptionGroupAPIMock_) {
      $provide.value('PublicHolidayAPI', _PublicHolidayAPIMock_);
      $provide.value('WorkPatternAPI', _WorkPatternAPIMock_);
      $provide.value('api.optionGroup', _OptionGroupAPIMock_);
    }));

    beforeEach(inject(['HR_settingsMock', function (_HRSettingsMock_) {
      $provide.value('HR_settings', _HRSettingsMock_);
    }]));

    beforeEach(inject(function (
      _$componentController_, _$log_, _$rootScope_, _AbsencePeriodInstance_,
      _LeaveRequestInstance_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $rootScope = _$rootScope_;
      AbsencePeriodInstance = _AbsencePeriodInstance_;
      LeaveRequestInstance = _LeaveRequestInstance_;

      spyOn($log, 'debug');
    }));

    describe('on initialize', function () {
      beforeEach(function () {
        selectedAbsenceType = _.assign(absenceTypeData.all().values[0], {
          remainder: 0
        });
        leaveRequest = LeaveRequestInstance.init();

        compileComponent({
          request: leaveRequest,
          selectedAbsenceType: selectedAbsenceType
        });

        $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
        $rootScope.$digest();
      });

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      it('has leave type as "leave"', function () {
        expect(controller.isLeaveType('leave')).toBeTruthy();
      });
    });

    /**
     * Compiles and initializes the component's controller. It returns the
     * parameters used to initialize the controller plus default parameter
     * values.
     *
     * @param {Object} params - the values to initialize the component. Defaults
     * to an empty object.
     *
     * @return {Object}
     */
    function compileComponent (params) {
      params = params || {};

      requestModalHelper.addDefaultComponentParams(params, AbsencePeriodInstance);

      controller = $componentController(
        'leaveRequestPopupDetailsTab',
        null,
        params
      );

      $rootScope.$digest();

      return params;
    }
  });
});
