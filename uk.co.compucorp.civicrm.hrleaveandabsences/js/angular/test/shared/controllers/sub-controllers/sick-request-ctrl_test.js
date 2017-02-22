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
  ], function (_, optionGroupMock) {
    'use strict';

    describe('SickRequestCtrl', function () {
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
        _LeaveRequestAPIMock_, _OptionGroupAPIMock_, _PublicHolidayAPIMock_) {
        $provide.value('AbsencePeriodAPI', _AbsencePeriodAPIMock_);
        $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
        $provide.value('EntitlementAPI', _EntitlementAPIMock_);
        $provide.value('WorkPatternAPI', _WorkPatternAPIMock_);
        $provide.value('HR_settings', _HR_settingsMock_);
        $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
        $provide.value('api.optionGroup', _OptionGroupAPIMock_);
        $provide.value('PublicHolidayAPI', _PublicHolidayAPIMock_);
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
            contactId: CRM.vars.leaveAndAbsences.contactId,
            leaveType: 'sick'
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
          expect($ctrl.isLeaveType('sick')).toBeTruthy();
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
            var reason = optionGroupMock.specificObject('hrleaveandabsences_sickness_reason', 'name', 'appointment');

            setTestDates(date2016, date2016);
            $ctrl.request.reason = reason.value;
          });

          it('cannot submit request', function () {
            expect($ctrl.canSubmit()).toBe(true);
          });

          describe('when user changes number of days selected', function () {
            beforeEach(function () {
              $ctrl.changeInNoOfDays();
            });

            it('resets reason', function () {
              expect($ctrl.request.reason).toBeNull();
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

        $ctrl = $controller('SickRequestCtrl', {
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
    });
  });
})(CRM);
