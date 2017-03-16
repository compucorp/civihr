(function (CRM) {
  define([
    'common/angular',
    'common/moment',
    'common/lodash',
    'mocks/data/option-group-mock-data',
    'mocks/data/public-holiday-data',
    'mocks/data/work-pattern-data',
    'mocks/data/leave-request-data',
    'mocks/apis/absence-period-api-mock',
    'mocks/apis/absence-type-api-mock',
    'mocks/apis/leave-request-api-mock',
    'mocks/apis/public-holiday-api-mock',
    'mocks/apis/option-group-api-mock',
    'mocks/apis/work-pattern-api-mock',
    'leave-absences/shared/config',
    'leave-absences/my-leave/app',
  ], function (angular, moment, _, optionGroupMock, publicHolidayData, workPatternData, leaveRequestData) {
    'use strict';

    describe('myLeaveCalendar', function () {
      var $compile, $log, $q, $rootScope, component, controller, sharedSettings,
        $provide, OptionGroup, OptionGroupAPIMock, Calendar, CalendarInstance, LeaveRequest;

      beforeEach(module('leave-absences.templates', 'leave-absences.mocks',
      'my-leave', 'common.mocks', function (_$provide_) {
        $provide = _$provide_;
      }));

      beforeEach(inject(function (AbsencePeriodAPIMock, AbsenceTypeAPIMock, LeaveRequestAPIMock,
                                  PublicHolidayAPIMock, WorkPatternAPIMock) {
        $provide.value('AbsencePeriodAPI', AbsencePeriodAPIMock);
        $provide.value('AbsenceTypeAPI', AbsenceTypeAPIMock);
        $provide.value('LeaveRequestAPI', LeaveRequestAPIMock);
        $provide.value('PublicHolidayAPI', PublicHolidayAPIMock);
        $provide.value('WorkPatternAPI', WorkPatternAPIMock);
      }));

      beforeEach(inject(['$compile', '$log', '$q', '$rootScope', 'OptionGroup', 'OptionGroupAPIMock',
        'Calendar', 'CalendarInstance', 'LeaveRequest', 'shared-settings',
        function (_$compile_, _$log_, _$q_, _$rootScope_, _OptionGroup_, _OptionGroupAPIMock_,
                  _Calendar_, _CalendarInstance_, _LeaveRequest_, _sharedSettings_) {
        $compile = _$compile_;
        $log = _$log_;
        $q = _$q_;
        $rootScope = _$rootScope_;
        LeaveRequest = _LeaveRequest_;
        Calendar = _Calendar_;
        CalendarInstance = _CalendarInstance_;
        OptionGroup = _OptionGroup_;
        OptionGroupAPIMock = _OptionGroupAPIMock_;
        sharedSettings = _sharedSettings_;

        spyOn($log, 'debug');

        spyOn(OptionGroup, 'valuesOf').and.callFake(function (name) {
          return OptionGroupAPIMock.valuesOf(name);
        });

        compileComponent();
      }]));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      it('has the legend expanded', function () {
        expect(controller.legendCollapsed).toBe(false);
      });

      describe('on init', function () {
        it('loader is hidden', function () {
          expect(controller.loading.page).toBe(false);
        });

        it('absence periods have loaded', function () {
          expect(controller.absencePeriods.length).not.toBe(0);
        });

        it('absence types have loaded', function () {
          expect(controller.absenceTypes.length).not.toBe(0);
        });

        it('calendar have loaded', function () {
          expect(Object.keys(controller.calendar.days).length).not.toBe(0);
        });
      });

      describe('isPublicHoliday', function () {
        var date;

        beforeEach(function () {
          date = publicHolidayData.all().values[0].date;
        });

        it('checks whether date is a public holiday', function () {
          expect(controller.isPublicHoliday(date)).toBe(true);
        });
      });

      describe('period label', function () {
        var label, period;

        describe('when the period is current', function () {
          beforeEach(function () {
            period = _(controller.absencePeriods).find(function (period) {
              return period.current;
            });
            label = controller.labelPeriod(period);
          });

          it('adds Current Period to the label', function () {
            expect(label).toBe('Current Period (' + period.title + ')');
          });
        });

        describe('when the period is not current', function () {
          beforeEach(function () {
            period = _(controller.absencePeriods).filter(function (period) {
              return !period.current;
            }).sample();
            label = controller.labelPeriod(period);
          });

          it('returns the title as it is', function () {
            expect(label).toBe(period.title);
          });
        });
      });

      describe('getDayName', function () {
        var anySunday;

        beforeEach(function () {
          anySunday = '2017/02/05';
        });

        it('returns day name of a date', function () {
          expect(controller.getDayName(anySunday)).toBe('Sun');
        });
      });

      describe('getMonthData', function () {
        var januaryMonth = 0,
          returnValue;

        beforeEach(function () {
          returnValue = controller.getMonthData(januaryMonth);
        });

        it('returns the date which are from the month of january', function () {
          _.each(returnValue, function (dateObject) {
            expect(moment(dateObject.date).month()).toBe(januaryMonth);
          });
        });
      });

      describe('refresh', function () {
        it('page loading is true initially', function () {
          controller.refresh();
          expect(controller.loading.calendar).toBe(true);
        });

        describe('after data load is complete', function () {
          beforeEach(function () {
            controller.refresh();
            $rootScope.$digest();
          });

          it('page loading is false', function () {
            expect(controller.loading.calendar).toBe(false);
          });
        });

        describe('isWeekend', function () {
          var dateObj;

          beforeEach(function () {
            controller.refresh();
            $rootScope.$digest();
            dateObj = controller.calendar.days[getDateObjectWithFormat(getDate('weekend').date).valueOf()];
          });

          it('is set', function () {
            expect(dateObj.UI.isWeekend).toBe(true);
          });
        });

        describe('isNonWorkingDay', function () {
          var dateObj;

          beforeEach(function () {
            controller.refresh();
            $rootScope.$digest();
            dateObj = controller.calendar.days[getDateObjectWithFormat(getDate('non_working_day').date).valueOf()];
          });

          it('is set', function () {
            expect(dateObj.UI.isNonWorkingDay).toBe(true);
          });
        });

        describe('isPublicHoliday', function () {
          var dateObj;

          beforeEach(function () {
            spyOn(controller, 'isPublicHoliday').and.returnValue(true);
            controller.refresh();
            $rootScope.$digest();
            dateObj = controller.calendar.days[Object.keys(controller.calendar.days)[0]];
          });

          it('is set', function () {
            expect(dateObj.UI.isPublicHoliday).toBe(true);
          });
        });

        describe('refresh', function () {
          var dateObj,
            workPattern,
            leaveRequest;

          beforeEach(function () {
            workPattern = workPatternData.daysData();
            leaveRequest = leaveRequestData.singleDataSuccess().values[0];
            workPattern.values[0].date = leaveRequest.from_date;
          });

          describe('when leave request is not approved', function () {
            beforeEach(function () {
              var status = _.find(optionGroupMock.getCollection('hrleaveandabsences_leave_request_status'), function (status) {
                return status.name === 'waiting_approval';
              });
              leaveRequest.status_id = status.value;
              leaveRequest.balance_change = -1;
              commonSetup();
              dateObj = controller.calendar.days[getDateObjectWithFormat(leaveRequest.from_date).valueOf()];
            });

            it('isRequested flag is true', function () {
              expect(dateObj.UI.isRequested).toBe(true);
            });

            it('styles are fetched', function () {
              var color = _.find(controller.absenceTypes, function (absenceType) {
                return absenceType.id == leaveRequest.type_id;
              }).color;

              expect(dateObj.UI.styles).toEqual({
                backgroundColor: color,
                borderColor: color
              });
            });
          });

          describe('when leave request is for half day am', function() {
            beforeEach(function() {
              var halfDayAMValue = _.find(optionGroupMock.getCollection('hrleaveandabsences_leave_request_day_type'), function (absenceType) {
                return absenceType.name === 'half_day_am';
              }).value;

              leaveRequest.from_date_type = halfDayAMValue;
              commonSetup();
            });

            it('AM flag is set', function() {
              expect(dateObj.UI.isAM).toBe(true);
            });
          });

          describe('when leave request is for half day pm', function() {
            beforeEach(function() {
              var halfDayPMValue = _.find(optionGroupMock.getCollection('hrleaveandabsences_leave_request_day_type'), function (absenceType) {
                return absenceType.name === 'half_day_pm';
              }).value;

              leaveRequest.from_date_type = halfDayPMValue;
              commonSetup();
            });

            it('PM flag is set', function() {
              expect(dateObj.UI.isPM).toBe(true);
            });
          });

          describe('when balance change is positive', function() {
            beforeEach(function() {
              leaveRequest.balance_change = 2;
              commonSetup();
            });

            it('AccruedTOIL flag is set', function() {
              expect(dateObj.UI.isAccruedTOIL).toBe(true);
            });
          });

          function commonSetup() {
            spyOn(Calendar, 'get').and.callFake(function () {
              return $q.resolve(CalendarInstance.init(workPattern.values));
            });

            spyOn(LeaveRequest, 'all').and.callFake(function () {
              return $q.resolve({
                list: [leaveRequest]
              });
            });

            controller.refresh();
            $rootScope.$digest();
          }
        });
      });

      function compileComponent() {
        var $scope = $rootScope.$new();
        var contactId = CRM.vars.leaveAndAbsences.contactId;

        component = angular.element('<my-leave-calendar contact-id="' + contactId + '"></my-leave-calendar>');
        $compile(component)($scope);
        $scope.$digest();

        controller = component.controller('myLeaveCalendar');
      }

      function getDateObjectWithFormat(date) {
        return moment(date, sharedSettings.serverDateFormat).clone();
      }

      function getDate(dayType) {
        return workPatternData.daysData().values.find(function (data) {
          return data.type.name === dayType;
        });
      }
    });
  });
})(CRM);
