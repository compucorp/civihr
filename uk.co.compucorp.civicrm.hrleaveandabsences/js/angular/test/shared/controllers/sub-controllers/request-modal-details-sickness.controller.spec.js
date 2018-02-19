/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/moment',
  'mocks/data/absence-period.data',
  'mocks/data/absence-type.data',
  'mocks/data/leave-request.data',
  'mocks/data/option-group.data',
  'mocks/helpers/helper',
  'mocks/apis/option-group-api-mock',
  'leave-absences/manager-leave/app'
], function (angular, _, moment, absencePeriodData, absenceTypeData, leaveRequestData, optionGroupMock, helper) {
  'use strict';

  describe('RequestModalDetailsSicknessController', function () {
    var $componentController, $provide, $log, $rootScope, controller, leaveRequest, LeaveRequestAPI,
      AbsenceType, AbsenceTypeAPI, AbsencePeriodInstance, LeaveRequestInstance, SicknessRequestInstance,
      OptionGroup, OptionGroupAPIMock, balance, selectedAbsenceType, WorkPatternAPI, EntitlementAPI;

    var date2016 = '01/12/2016';
    var date2016To = '02/12/2016'; // Must be greater than `date2016`
    var date2017 = '01/02/2017';

    beforeEach(module('common.mocks', 'leave-absences.templates', 'leave-absences.mocks', 'manager-leave', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_AbsenceTypeAPIMock_, _WorkPatternAPIMock_, _PublicHolidayAPIMock_, _LeaveRequestAPIMock_, _OptionGroupAPIMock_) {
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
      $provide.value('WorkPatternAPI', _WorkPatternAPIMock_);
      $provide.value('PublicHolidayAPI', _PublicHolidayAPIMock_);
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
      $provide.value('api.optionGroup', _OptionGroupAPIMock_);
    }));

    beforeEach(inject(['HR_settingsMock', function (_HRSettingsMock_) {
      $provide.value('HR_settings', _HRSettingsMock_);
    }]));

    beforeEach(inject(function (
      _$componentController_, _$log_, _$rootScope_, _AbsencePeriodInstance_,
      _LeaveRequestInstance_, _SicknessRequestInstance_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $rootScope = _$rootScope_;
      AbsencePeriodInstance = _AbsencePeriodInstance_;
      LeaveRequestInstance = _LeaveRequestInstance_;
      SicknessRequestInstance = _SicknessRequestInstance_;

      spyOn($log, 'debug');

      balance = {
        closing: 0,
        opening: 0,
        change: {
          amount: 0,
          breakdown: []
        }
      };
    }));

    describe('on initialize', function () {
      beforeEach(function () {
        selectedAbsenceType = _.assign(absenceTypeData.all().values[0], {
          remainder: 0
        });
        leaveRequest = SicknessRequestInstance.init();

        compileComponent({
          leaveType: 'sick',
          request: leaveRequest,
          selectedAbsenceType: selectedAbsenceType
        });

        $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
        $rootScope.$digest();
      });

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      it('has leave type as "sickness"', function () {
        expect(controller.isLeaveType('sickness')).toBeTruthy();
      });

      describe('initChildController()', function () {
        it('loads reasons option types', function () {
          expect(Object.keys(controller.sicknessReasons).length).toBeGreaterThan(0);
        });

        it('loads documents option types', function () {
          expect(controller.sicknessDocumentTypes.length).toBeGreaterThan(0);
        });
      });

      describe('isDocumentInRequest()', function () {
        var documents = optionGroupMock.getCollection('hrleaveandabsences_leave_request_required_document');

        it('checks if the document is in the request', function () {
          expect(controller.isDocumentInRequest(documents[0].value)).toBeTruthy();
          expect(controller.isDocumentInRequest('non-existing-document')).toBeFalsy();
        });
      });

      describe('with selected reason', function () {
        beforeEach(function () {
          setTestDates(date2016, date2016To);
          setReason();
        });

        describe('when user changes number of days selected', function () {
          beforeEach(function () {
            controller.daysSelectionModeChangeHandler();
          });

          it('does not reset sickness reason', function () {
            expect(controller.request.sickness_reason).not.toBeNull();
          });
        });
      });

      describe('open sickness request in edit mode', function () {
        var sicknessRequest;

        beforeEach(function () {
          sicknessRequest = SicknessRequestInstance.init(leaveRequestData.findBy('request_type', 'sickness'));
          sicknessRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
          sicknessRequest.sickness_required_documents = '1,2';
          sicknessRequest.status_id = optionGroupMock.specificValue(
            'hrleaveandabsences_leave_request_status', 'value', '3');

          compileComponent({
            leaveType: 'sick',
            mode: 'edit',
            request: sicknessRequest,
            selectedAbsenceType: selectedAbsenceType
          });

          $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
          $rootScope.$digest();
        });

        it('sets edit mode', function () {
          expect(controller.isMode('edit')).toBeTruthy();
        });

        it('does show balance', function () {
          expect(controller.uiOptions.showBalance).toBeTruthy();
        });

        describe('when request states multiple days', function () {
          beforeEach(function () {
            compileComponent({
              mode: 'edit',
              leaveType: 'sick',
              request: sicknessRequest,
              selectedAbsenceType: selectedAbsenceType
            });
            setTestDates(date2016, date2017);
            $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
            $rootScope.$digest();
          });

          it('shows multiple days', function () {
            expect(controller.uiOptions.multipleDays).toBeTruthy();
          });
        });

        describe('when request states a single day', function () {
          beforeEach(function () {
            compileComponent({
              request: sicknessRequest,
              leaveType: 'sick'
            });
            setTestDates(date2016, date2016To);
            $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
            $rootScope.$digest();
          });

          it('shows single day', function () {
            expect(controller.uiOptions.multipleDays).not.toBeTruthy();
          });
        });

        describe('initializes required documents', function () {
          var testDocumentId = '1';
          var failDocumentId = '3';

          it('checks checkbox', function () {
            expect(controller.isChecked(testDocumentId)).toBeTruthy();
          });

          it('does not check checkbox', function () {
            expect(controller.isChecked(failDocumentId)).toBeFalsy();
          });
        });

        describe('when checking if can submit', function () {
          describe('when sickness reason is not chosen', function () {
            beforeEach(function () {
              controller.request.sickness_reason = null;
            });

            it('does not allow to submit', function () {
              expect(controller.checkSubmitConditions()).toBeFalsy();
            });
          });

          describe('when sickness reason is chosen', function () {
            beforeEach(function () {
              controller.request.sickness_reason = '2';
            });

            it('does not allow to submit', function () {
              expect(controller.checkSubmitConditions()).toBeTruthy();
            });
          });
        });
      });
    });

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
     * - {Object} request - The leave request data. Defaults to an empty leave request.
     * - {JasmineSpy} isMode - a isMode spy function.
     * - {JasmineSpy} isRole - a isRole spy function.
     */
    function addDefaultComponentParams (params) {
      addSpyParams(params);

      var defaultParams = {
        absencePeriods: absencePeriodData.all().values.map(function (period) {
          return AbsencePeriodInstance.init(period);
        }),
        absenceTypes: absenceTypeData.all().values,
        balance: balance, // balance is set globally
        checkSubmitConditions: params.checkSubmitConditions,
        isLeaveStatus: params.isLeaveStatus,
        leaveType: 'leave',
        period: absencePeriodData.all().values[0],
        selectedAbsenceType: _.assign(absenceTypeData.all().values[0], {
          remainder: 0
        }),
        request: LeaveRequestInstance.init(),
        isMode: params.isMode,
        isRole: params.isRole
      };

      _.defaults(params, defaultParams);
    }

    /**
     * Appends default spy functions to the params object.
     *
     * @param {Object} params - the object which spy functions will be appened to.
     */
    function addSpyParams (params) {
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
          return getStatusValueFromName(statusName) === params.request.status_id;
        });
    }

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

      addDefaultComponentParams(params);

      controller = $componentController(
        'leaveRequestPopupDetailsTab',
        null,
        params
      );

      $rootScope.$digest();

      return params;
    }

    /**
     * Sets reason on request
     **/
    function setReason () {
      var reason = optionGroupMock.specificObject('hrleaveandabsences_sickness_reason', 'name', 'appointment');
      controller.request.sickness_reason = reason.value;
    }

    /**
     * sets from and/or to dates
     * @param {String} from date set if passed
     * @param {String} to date set if passed
     */
    function setTestDates (from, to) {
      if (from) {
        controller.uiOptions.fromDate = getUTCDate(from);
        controller.dateChangeHandler('from');
        $rootScope.$digest();
      }

      if (to) {
        controller.uiOptions.toDate = getUTCDate(to);
        controller.dateChangeHandler('to');
        $rootScope.$digest();
      }
    }

    /**
     * Returns a UTC Date object from a string.
     *
     * @param {String} date - the date to convert to UTC Date object.
     * @return {Date}
     */
    function getUTCDate (date) {
      var now = new Date(date);
      return new Date(now.getTime() + now.getTimezoneOffset() * 60000);
    }

    /**
     * Returns the id for a specific status by filtering using the status name.
     *
     * @param {String} statusName - The name of the status to filter by.
     * @return {Number}
     */
    function getStatusValueFromName (statusName) {
      var status = optionGroupMock.specificObject(
        'hrleaveandabsences_leave_request_status',
        'name',
        statusName
      );

      return status.value;
    }
  });
});
