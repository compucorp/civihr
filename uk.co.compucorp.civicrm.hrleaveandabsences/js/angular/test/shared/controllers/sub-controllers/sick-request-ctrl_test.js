(function (CRM) {
  define([
    'common/lodash',
    'mocks/data/option-group-mock-data',
    'common/angularMocks',
    'leave-absences/shared/config',
    'common/mocks/services/hr-settings-mock',
    'mocks/apis/absence-period-api-mock',
    'mocks/apis/absence-type-api-mock',
    'mocks/apis/entitlement-api-mock',
    'mocks/apis/work-pattern-api-mock',
    'mocks/apis/leave-request-api-mock',
    'mocks/apis/option-group-api-mock',
    'mocks/apis/public-holiday-api-mock',
    'common/mocks/services/api/contact-mock',
    'leave-absences/shared/controllers/sub-controllers/sick-request-ctrl',
    'leave-absences/shared/modules/shared-settings',
    'common/mocks/services/file-uploader-mock',
  ], function (_, optionGroupMock) {
    'use strict';

    describe('SicknessRequestCtrl', function () {
      var $log, $rootScope, $ctrl, modalInstanceSpy, $scope, $controller,
        $provide, sharedSettings, AbsenceTypeAPI, SicknessRequestInstance,
        date2016 = '01/12/2016';

      beforeEach(module('leave-absences.templates', 'leave-absences.controllers',
        'leave-absences.mocks', 'common.mocks', 'leave-absences.settings',
        function (_$provide_) {
          $provide = _$provide_;
        }));

      beforeEach(inject(function (_AbsencePeriodAPIMock_, _HR_settingsMock_,
        _AbsenceTypeAPIMock_, _EntitlementAPIMock_, _WorkPatternAPIMock_,
        _LeaveRequestAPIMock_, _OptionGroupAPIMock_, _PublicHolidayAPIMock_,
        _FileUploaderMock_) {
        $provide.value('AbsencePeriodAPI', _AbsencePeriodAPIMock_);
        $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
        $provide.value('EntitlementAPI', _EntitlementAPIMock_);
        $provide.value('WorkPatternAPI', _WorkPatternAPIMock_);
        $provide.value('HR_settings', _HR_settingsMock_);
        $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
        $provide.value('api.optionGroup', _OptionGroupAPIMock_);
        $provide.value('PublicHolidayAPI', _PublicHolidayAPIMock_);
        $provide.value('FileUploader', _FileUploaderMock_);
      }));

      beforeEach(inject(function (_$log_, _$controller_, _$rootScope_,
        _AbsenceTypeAPI_, _SicknessRequestInstance_) {

        $log = _$log_;
        $rootScope = _$rootScope_;
        $controller = _$controller_;
        AbsenceTypeAPI = _AbsenceTypeAPI_;
        SicknessRequestInstance = _SicknessRequestInstance_;
        modalInstanceSpy = jasmine.createSpyObj('modalInstanceSpy', ['dismiss', 'close']);

        spyOn($log, 'debug');
        spyOn(AbsenceTypeAPI, 'all').and.callThrough();
        spyOn(SicknessRequestInstance, 'init').and.callThrough();
      }));

      describe('sick request', function () {
        var parentRequestCtrl;

        beforeEach(function () {
          var directiveOptions = {
            contactId: CRM.vars.leaveAndAbsences.contactId
          };

          initTestController(directiveOptions);
          parentRequestCtrl = $controller('RequestCtrl')
        });

        it('is called', function () {
          expect($log.debug).toHaveBeenCalled();
        });

        it('inherited from request controller', function(){
          expect($ctrl instanceof parentRequestCtrl.constructor).toBe(true);
        });

        it('has leave type set to sick', function () {
          expect($ctrl.isLeaveType('sickness')).toBeTruthy();
        });

        it('calls init on sickness instance', function () {
          expect(SicknessRequestInstance.init).toHaveBeenCalledWith({
            contact_id: CRM.vars.leaveAndAbsences.contactId
          });
        });

        it('loads reasons option types', function () {
          expect(Object.keys($ctrl.sicknessReasons).length).toBeGreaterThan(0);
        });

        it('loads documents option types', function () {
          expect($ctrl.sicknessDocumentTypes.length).toBeGreaterThan(0);
        });

        it('gets absence types with true sick param', function () {
          expect(AbsenceTypeAPI.all).toHaveBeenCalledWith({
            is_sick: true
          })
        });

        it('cannot submit request', function () {
          expect($ctrl.canSubmit()).toBe(false);
        });

        describe('with selected reason', function () {
          beforeEach(function () {
            setTestDates(date2016, date2016);
            setReason();
          });

          it('cannot submit request', function () {
            expect($ctrl.canSubmit()).toBe(true);
          });

          describe('when user changes number of days selected', function () {
            beforeEach(function () {
              $ctrl.changeInNoOfDays();
            });

            it('resets reason', function () {
              expect($ctrl.request.sickness_reason).toBeNull();
            });
          });
        });

        describe('when submit with valid fields', function () {
          beforeEach(function () {
            spyOn($rootScope, '$emit');
            setTestDates(date2016, date2016);
            //entitlements are randomly generated so resetting them to positive here
            $ctrl.balance.closing = 1;
            setReason();
            $ctrl.submit();
            $scope.$digest();
          });

          it('has all required fields', function () {
            expect($ctrl.request.from_date).toBeDefined();
            expect($ctrl.request.to_date).toBeDefined();
            expect($ctrl.request.from_date_type).toBeDefined();
            expect($ctrl.request.to_date_type).toBeDefined();
            expect($ctrl.request.contact_id).toBeDefined();
            expect($ctrl.request.status_id).toBeDefined();
            expect($ctrl.request.type_id).toBeDefined();
            expect($ctrl.request.sickness_reason).toBeDefined();
            expect($ctrl.request.sickness_required_documents).toBeDefined();
          });

          it('is successful', function () {
            expect($ctrl.error).toBeNull();
            expect($ctrl.request.id).toBeDefined();
          });

          it('allows user to submit', function () {
            expect($ctrl.canSubmit()).toBeTruthy();
          });

          it('sends event', function () {
            expect($rootScope.$emit).toHaveBeenCalledWith('LeaveRequest::new', $ctrl.request);
          });

          describe('when balance change is negative', function () {
            beforeEach(function () {
              setTestDates(date2016, date2016);
              //entitlements are randomly generated so resetting them to negative here
              $ctrl.balance.closing = -1;
              $ctrl.submit();
              $scope.$digest();
            });

            describe('and absence type does not allow overuse', function () {
              it('does not save and sets error', function () {
                expect($ctrl.error).toBeDefined();
              });
            });
          });
        });
      });

      /**
       * Initialize the controller
       *
       * @param leave request
       */
      function initTestController(directiveOptions) {
        $scope = $rootScope.$new();

        $ctrl = $controller('SicknessRequestCtrl', {
          $scope: $scope,
          $uibModalInstance: modalInstanceSpy,
          directiveOptions: directiveOptions
        });

        $scope.$digest();
      }

      /**
       * sets from and/or to dates
       * @param {String} from date set if passed
       * @param {String} to date set if passed
       */
      function setTestDates(from, to) {
        if (from) {
          $ctrl.uiOptions.fromDate = new Date(from);
          $ctrl.updateAbsencePeriodDatesTypes($ctrl.uiOptions.fromDate, 'from');
          $scope.$digest();
        }

        if (to) {
          $ctrl.uiOptions.toDate = new Date(to);
          $ctrl.updateAbsencePeriodDatesTypes($ctrl.uiOptions.toDate, 'to');
          $scope.$digest();
        }
      }

      /**
      * Sets reason on request
      **/
      function setReason() {
        var reason = optionGroupMock.specificObject('hrleaveandabsences_sickness_reason', 'name', 'appointment');
        $ctrl.request.sickness_reason = reason.value;
      }
    });
  });
})(CRM);
