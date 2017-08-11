/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angular',
    'common/moment',
    'common/lodash',
    'mocks/data/absence-period-data',
    'mocks/data/absence-type-data',
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
    'leave-absences/my-leave/app'
  ], function (angular, moment, _, absencePeriodData, absenceTypeData, optionGroupMock, publicHolidayData, workPatternMocked, leaveRequestData) {
    'use strict';

    describe('sharedLeaveCalendar', function () {
      var $componentController, $log, $q, $rootScope, controller, $provide,
        AbsencePeriod, AbsenceType, OptionGroup, OptionGroupAPIMock, Calendar, CalendarInstance, LeaveRequest, LeavePopup;

      beforeEach(module('leave-absences.templates', 'leave-absences.mocks', 'my-leave', function (_$provide_) {
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

      beforeEach(inject(['$componentController', '$log', '$q', '$rootScope', 'AbsencePeriod', 'AbsenceType', 'OptionGroup', 'OptionGroupAPIMock',
        'Calendar', 'CalendarInstance', 'LeaveRequest', 'LeavePopup',
        function (_$componentController_, _$log_, _$q_, _$rootScope_, _AbsencePeriod_, _AbsenceType_, _OptionGroup_, _OptionGroupAPIMock_,
          _Calendar_, _CalendarInstance_, _LeaveRequest_, _LeavePopup_) {
          $componentController = _$componentController_;
          $log = _$log_;
          $q = _$q_;
          $rootScope = _$rootScope_;
          AbsencePeriod = _AbsencePeriod_;
          AbsenceType = _AbsenceType_;
          LeaveRequest = _LeaveRequest_;
          Calendar = _Calendar_;
          CalendarInstance = _CalendarInstance_;
          OptionGroup = _OptionGroup_;
          OptionGroupAPIMock = _OptionGroupAPIMock_;
          LeavePopup = _LeavePopup_;

          spyOn($log, 'debug');
          spyOn(OptionGroup, 'valuesOf').and.callFake(function (name) {
            return OptionGroupAPIMock.valuesOf(name);
          });
          spyOn(AbsencePeriod, 'all').and.callFake(function () {
            var data = absencePeriodData.all().values;
            // Set 2016 as current period, because Calendar loads data only for the current period initially,
            // and MockedData has 2016 dates
            data[0].current = true;

            return $q.resolve(data);
          });
          spyOn(AbsenceType, 'all').and.callThrough();
          spyOn(LeaveRequest, 'all').and.callThrough();

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

        describe('absence periods', function () {
          it('absence periods have loaded', function () {
            expect(controller.absencePeriods.length).not.toBe(0);
          });

          it('sorts absence periods by start_date', function () {
            expect(controller.absencePeriods).toEqual(_.sortBy(absencePeriodData.all().values, 'start_date'));
          });
        });

        it('absence types have loaded', function () {
          expect(controller.absenceTypes.length).not.toBe(0);
        });

        it('each month data has loaded', function () {
          _.each(controller.months, function (month) {
            expect(Object.keys(month.data.length)).not.toBe(0);
          });
        });

        it('Leave request API is called with proper parameters', function () {
          expect(LeaveRequest.all).toHaveBeenCalledWith({
            from_date: {from: controller.selectedPeriod.start_date},
            to_date: {to: controller.selectedPeriod.end_date},
            status_id: {'IN': [
              optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'name', 'approved').value,
              optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'name', 'admin_approved').value,
              optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'name', 'awaiting_approval').value
            ]},
            contact_id: CRM.vars.leaveAndAbsences.contactId
          }, {}, null, null, false);
        });
      });

      it('disabled absence types are filtered', function () {
        expect(AbsenceType.all).toHaveBeenCalledWith({
          is_active: true
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
        var returnValue;
        var januaryMonth = 0;

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
            dateObj = getDateFromCalendar('weekend');
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
            dateObj = getDateFromCalendar('non_working_day');
          });

          it('is set', function () {
            expect(dateObj.UI.isNonWorkingDay).toBe(true);
          });
        });

        describe('isPublicHoliday', function () {
          var dateObj;

          beforeEach(function () {
            // set this so that every date is marked as public holiday
            spyOn(controller, 'isPublicHoliday').and.returnValue(true);
            controller.refresh();
            $rootScope.$digest();
            // pick any date
            dateObj = getDateFromCalendar('non_working_day');
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
            workPattern = workPatternMocked.getCalendar;
            leaveRequest = leaveRequestData.singleDataSuccess().values[0];
            workPattern.values[0].calendar[0].date = leaveRequest.from_date;
          });

          describe('when leave request is not approved', function () {
            beforeEach(function () {
              var status = optionGroupMock.specificObject(
                'hrleaveandabsences_leave_request_status', 'name', 'awaiting_approval');

              leaveRequest.status_id = status.value;
              leaveRequest.balance_change = -1;
              commonSetup();
              dateObj = getDate(leaveRequest.from_date);
            });

            it('isRequested flag is true', function () {
              expect(dateObj.UI.isRequested).toBe(true);
            });

            it('styles are fetched', function () {
              var color = _.find(controller.absenceTypes, function (absenceType) {
                return absenceType.id === leaveRequest.type_id;
              }).color;

              expect(dateObj.UI.styles).toEqual({
                backgroundColor: color,
                borderColor: color
              });
            });
          });

          describe('when leave request is for half day am', function () {
            beforeEach(function () {
              var halfDayAMValue = _.find(optionGroupMock.getCollection('hrleaveandabsences_leave_request_day_type'), function (absenceType) {
                return absenceType.name === 'half_day_am';
              }).value;

              leaveRequest.from_date_type = halfDayAMValue;
              commonSetup();
            });

            it('AM flag is set', function () {
              expect(dateObj.UI.isAM).toBe(true);
            });
          });

          describe('when leave request is for half day pm', function () {
            beforeEach(function () {
              var halfDayPMValue = _.find(optionGroupMock.getCollection('hrleaveandabsences_leave_request_day_type'), function (absenceType) {
                return absenceType.name === 'half_day_pm';
              }).value;

              leaveRequest.from_date_type = halfDayPMValue;
              commonSetup();
            });

            it('PM flag is set', function () {
              expect(dateObj.UI.isPM).toBe(true);
            });
          });

          describe('when balance change is positive', function () {
            beforeEach(function () {
              leaveRequest.balance_change = 2;
              commonSetup();
            });

            it('AccruedTOIL flag is set', function () {
              expect(dateObj.UI.isAccruedTOIL).toBe(true);
            });
          });

          function commonSetup () {
            spyOn(Calendar, 'get').and.callFake(function () {
              return $q.resolve(CalendarInstance.init(workPattern.values[0]));
            });

            LeaveRequest.all.and.callFake(function () {
              return $q.resolve({
                list: [leaveRequest]
              });
            });

            controller.refresh();
            $rootScope.$digest();
          }
        });
      });

      describe('openLeavePopup()', function () {
        var leaveRequest = { key: 'value' };
        var leaveType = 'some_leave_type';
        var selectedContactId = '101';
        var isSelfRecord = true;

        beforeEach(function () {
          spyOn(LeavePopup, 'openModal');
          controller.openLeavePopup(leaveRequest, leaveType, selectedContactId, isSelfRecord);
        });

        it('opens the leave request popup', function () {
          expect(LeavePopup.openModal).toHaveBeenCalledWith(leaveRequest, leaveType, selectedContactId, isSelfRecord);
        });
      });

      function compileComponent () {
        controller = $componentController('staffLeaveCalendar', null, { contactId: CRM.vars.leaveAndAbsences.contactId });
        $rootScope.$digest();
      }

      function getDate (dateStr) {
        return workPatternMocked.getCalendar.values[0].calendar.find(function (data) {
          return data.date === dateStr;
        });
      }

      function getDateByType (dayType) {
        return workPatternMocked.getCalendar.values[0].calendar.find(function (data) {
          return data.type.name === dayType;
        });
      }

      function getDateFromCalendar (dayType) {
        var date;
        _.each(controller.months, function (month) {
          _.each(month.data, function (dateObj) {
            if (dateObj.date === getDateByType(dayType).date) {
              date = dateObj;
            }
          });
        });

        return date;
      }
    });
  });
})(CRM);
