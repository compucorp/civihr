/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/moment',
  'leave-absences/mocks/data/absence-period.data',
  'leave-absences/mocks/data/absence-type.data',
  'leave-absences/mocks/data/leave-request.data',
  'leave-absences/mocks/data/option-group.data',
  'leave-absences/mocks/helpers/helper',
  'leave-absences/mocks/helpers/request-modal-helper',
  'common/mocks/services/hr-settings-mock',
  'leave-absences/mocks/apis/absence-type-api-mock',
  'leave-absences/mocks/apis/leave-request-api-mock',
  'leave-absences/mocks/apis/option-group-api-mock',
  'leave-absences/mocks/apis/public-holiday-api-mock',
  'leave-absences/mocks/apis/work-pattern-api-mock',
  'leave-absences/manager-leave/app',
  'leave-absences/shared/modules/shared-settings'
], function (angular, _, moment, absencePeriodData, absenceTypeData, leaveRequestData, optionGroupMock, helper, requestModalHelper, crmAngService) {
  'use strict';

  describe('RequestModalDetailsSicknessController', function () {
    var $componentController, $provide, $log, $rootScope, controller, leaveRequest,
      SicknessRequestInstance, selectedAbsenceType;

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
      _$componentController_, _$log_, _$rootScope_, _crmAngService_, _SicknessRequestInstance_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $rootScope = _$rootScope_;
      crmAngService = _crmAngService_;
      SicknessRequestInstance = _SicknessRequestInstance_;

      spyOn($log, 'debug');
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
          requestModalHelper.setTestDates(controller, date2016, date2016To);
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

      describe('when users click on the sick reason wrench icon', function () {
        var url;
        var sicknessReasons;

        beforeEach(function () {
          url = '/civicrm/admin/options/hrleaveandabsences_sickness_reason?reset=1';
          sicknessReasons = optionGroupMock.getCollection('hrleaveandabsences_sickness_reason');

          spyOn(crmAngService, 'loadForm').and.callFake(function () {
            return {
              on: function () {}
            };
          });
          controller.openSicknessReasonOptionsEditor();
        });

        it('calls the crmAngService with the requested url', function () {
          expect(crmAngService.loadForm).toHaveBeenCalledWith(url);
        });

        it('loads the sickness reasons', function () {
          expect(controller.sicknessReasons).toEqual(_.keyBy(sicknessReasons, 'name'));
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
            requestModalHelper.setTestDates(controller, date2016, date2017);
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
            requestModalHelper.setTestDates(controller, date2016, date2016To);
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
              expect(controller.canSubmit()).toBeFalsy();
            });
          });

          describe('when sickness reason is chosen', function () {
            beforeEach(function () {
              controller.request.sickness_reason = '2';
            });

            it('does not allow to submit', function () {
              expect(controller.canSubmit()).toBeTruthy();
            });
          });
        });
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

      requestModalHelper.addDefaultComponentParams(params);

      controller = $componentController(
        'leaveRequestPopupDetailsTab',
        null,
        params
      );

      controller.$onInit();
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
  });
});
