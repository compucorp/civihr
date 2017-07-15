/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angular',
    'common/moment',
    'common/lodash',
    'mocks/helpers/helper',
    'mocks/data/absence-period-data',
    'mocks/data/absence-type-data',
    'mocks/data/option-group-mock-data',
    'mocks/data/public-holiday-data',
    'mocks/data/work-pattern-data',
    'mocks/data/leave-request-data',
    'common/mocks/services/api/contact-mock',
    'mocks/apis/absence-period-api-mock',
    'mocks/apis/absence-type-api-mock',
    'mocks/apis/leave-request-api-mock',
    'mocks/apis/public-holiday-api-mock',
    'mocks/apis/option-group-api-mock',
    'mocks/apis/work-pattern-api-mock',
    'leave-absences/shared/config',
    'leave-absences/my-leave/app'
  ], function (angular, moment, _, helper, absencePeriodData, absenceTypeData, optionGroupMock, publicHolidayData, workPatternMocked, leaveRequestData) {
    'use strict';

    describe('sharedLeaveCalendar', function () {
      var $componentController, $log, $q, $rootScope, controller, $provide,
        AbsencePeriod, LeaveRequest, OptionGroup;

      beforeEach(module('leave-absences.templates', 'leave-absences.mocks', 'my-leave', 'common.mocks', function (_$provide_) {
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

      beforeEach(inject(['api.contact.mock', function (ContactAPIMock) {
        $provide.value('api.contact', ContactAPIMock);
      }]));

      beforeEach(inject(function (_OptionGroup_, OptionGroupAPIMock) {
        OptionGroup = _OptionGroup_;

        spyOn(OptionGroup, 'valuesOf').and.callFake(function (name) {
          return OptionGroupAPIMock.valuesOf(name);
        });
      }));

      beforeEach(inject(function (_$componentController_, _$log_, _$q_, _$rootScope_,
        _AbsencePeriod_, _LeaveRequest_) {
        $componentController = _$componentController_;
        $log = _$log_;
        $q = _$q_;
        $rootScope = _$rootScope_;
        AbsencePeriod = _AbsencePeriod_;
        LeaveRequest = _LeaveRequest_;

        spyOn($log, 'debug');
        spyOn(LeaveRequest, 'all').and.callThrough();
        spyOn(AbsencePeriod, 'all').and.callFake(function () {
          var data = absencePeriodData.all().values;
          // Set 2016 as current period, because Calendar loads data only for the current period initially,
          // and MockedData has 2016 dates
          data[0].current = true;

          return $q.resolve(data);
        });

        compileComponent();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      it('has the legend expanded', function () {
        expect(controller.legendCollapsed).toBe(false);
      });

      describe('on init', function () {
        var AbsenceType, Calendar, PublicHoliday;

        beforeEach(inject(function (_AbsenceType_, _Calendar_, _PublicHoliday_) {
          AbsenceType = _AbsenceType_;
          Calendar = _Calendar_;
          PublicHoliday = _PublicHoliday_;

          spyOn(AbsenceType, 'all').and.callThrough();
          spyOn(Calendar, 'get').and.callThrough();
          spyOn(PublicHoliday, 'all').and.callThrough();

          compileComponent();
        }));

        it('hides the loader for the whole page', function () {
          expect(controller.loading.page).toBe(false);
        });

        it('selects the current month', function () {
          expect(controller.selectedMonths).toEqual([moment().format('MMM')]);
        });

        it('loads the public holidays', function () {
          expect(PublicHoliday.all).toHaveBeenCalled();
        });

        it('loads the OptionValues of the leave request statuses and day types', function () {
          expect(OptionGroup.valuesOf).toHaveBeenCalledWith([
            'hrleaveandabsences_leave_request_status',
            'hrleaveandabsences_leave_request_day_type'
          ]);
        });

        it('loads the contacts to display on the calendar', function () {
          expect(controller.contacts.length).not.toBe(0);
        });

        describe('absence periods', function () {
          it('loads the absence periods', function () {
            expect(controller.absencePeriods.length).not.toBe(0);
          });

          it('sorts absence periods by start_date', function () {
            expect(controller.absencePeriods).toEqual(_.sortBy(absencePeriodData.all().values, 'start_date'));
          });

          it('selects the current period', function () {
            expect(controller.selectedPeriod.current).toBe(true);
          });

          it('creates the list of months of the selected period', function () {
            var months = controller.months;
            var periodStartDate = moment(controller.selectedPeriod.start_date);
            var periodEndDate = moment(controller.selectedPeriod.end_date);

            expect(months[0].month).toEqual(periodStartDate.month());
            expect(months[0].year).toEqual(periodStartDate.year());
            expect(months[months.length - 1].month).toEqual(periodEndDate.month());
            expect(months[months.length - 1].year).toEqual(periodEndDate.year());
          });
        });

        describe('absence types', function () {
          it('loads the absence types', function () {
            expect(controller.absenceTypes.length).not.toBe(0);
          });

          it('excludes the inactive absence types', function () {
            expect(AbsenceType.all).toHaveBeenCalledWith({
              is_active: true
            });
          });
        });

        describe('contacts\' work pattern calendar', function () {
          var callParams;

          beforeEach(function () {
            callParams = Calendar.get.calls.mostRecent().args;
          });

          it('loads the work pattern calendars', function () {
            expect(Calendar.get).toHaveBeenCalled();
          });

          it('loads only the work pattern calendars of the currently loaded contacts', function () {
            expect(callParams[0]).toEqual(controller.contacts.map(function (contact) {
              return contact.id;
            }));
          });

          it('loads only the work pattern calendars of the currently selected period', function () {
            expect(callParams[1]).toEqual(controller.selectedPeriod.id);
          });
        });

        describe('leave requests', function () {
          var filters;

          beforeEach(function () {
            filters = LeaveRequest.all.calls.mostRecent().args[0];
          });

          it('loads the leave requests', function () {
            expect(LeaveRequest.all).toHaveBeenCalled();
          });

          it('loads only the leave requests of the currently selected period', function () {
            expect(filters).toEqual(jasmine.objectContaining({
              from_date: {from: controller.selectedPeriod.start_date},
              to_date: {to: controller.selectedPeriod.end_date}
            }));
          });

          it('loads only the approved, admin approved, or awaiting approval leave requests', function () {
            expect(filters).toEqual(jasmine.objectContaining({
              status_id: {'IN': [
                optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'name', 'approved').value,
                optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'name', 'admin_approved').value,
                optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'name', 'awaiting_approval').value
              ]}
            }));
          });

          it('loads only the leave requests belonging to the loaded contacts', function () {
            expect(filters).toEqual(jasmine.objectContaining({
              contact_id: { 'IN': [CRM.vars.leaveAndAbsences.contactId] }
            }));
          });

          describe('indexing', function () {
            var leaveRequests;

            beforeEach(function () {
              leaveRequests = controller.leaveRequests;
            });

            it('indexes the overall list of leave requests by contact id', function () {
              expect(Object.keys(leaveRequests)).toEqual(controller.contacts.map(function (contact) {
                return contact.id;
              }));
            });

            it('indexes the leave requests of a specific contact by date', function () {
              expect(Object.keys(leaveRequests[controller.contacts[0].id]).every(function (key) {
                return moment(key).isValid();
              })).toBe(true);
            });
          });
        });
      });

      describe('date properties set by the calendar', function () {
        var dateObj;

        describe('when the day is a weekend', function () {
          beforeEach(function () {
            dateObj = getDateFromCalendar('weekend');
          });

          it('marks it as such', function () {
            expect(dateObj.UI.isWeekend).toBe(true);
          });
        });

        describe('when the day is a non-working day', function () {
          beforeEach(function () {
            dateObj = getDateFromCalendar('non_working_day');
          });

          it('marks it as such', function () {
            expect(dateObj.UI.isNonWorkingDay).toBe(true);
          });
        });

        describe('when the day is a public holiday', function () {
          beforeEach(function () {
            // set this so that every date is marked as public holiday
            spyOn(controller, 'isPublicHoliday').and.returnValue(true);
            controller.refresh();
            $rootScope.$digest();
            // pick any date
            dateObj = getDateFromCalendar('non_working_day');
          });

          it('marks it as such', function () {
            expect(dateObj.UI.isPublicHoliday).toBe(true);
          });
        });

        describe('when the day has a leave request on it', function () {
          var leaveRequest, workPattern;

          beforeEach(function () {
            leaveRequest = _.clone(leaveRequestData.singleDataSuccess().values[0]);
            workPattern = _.find(workPatternMocked.getCalendar.values, function (workPattern) {
              return workPattern.contact_id === CRM.vars.leaveAndAbsences.contactId;
            });

            workPattern.calendar[0].date = leaveRequest.from_date;
          });

          describe('basic tests', function () {
            beforeEach(function () {
              leaveRequest.status_id = optionGroupMock.specificObject(
                'hrleaveandabsences_leave_request_status', 'name', 'approved'
              ).value;

              dateObj = commonSetup();
            });

            it('assigns it the colors of its absence type', function () {
              var absenceTypeColor = _.find(controller.absenceTypes, function (absenceType) {
                return absenceType.id === leaveRequest.type_id;
              }).color;

              expect(dateObj.UI.styles).toEqual({
                backgroundColor: absenceTypeColor,
                borderColor: absenceTypeColor
              });
            });
          });

          describe('when the leave request is still awaiting approval', function () {
            beforeEach(function () {
              leaveRequest.status_id = optionGroupMock.specificObject(
                'hrleaveandabsences_leave_request_status', 'name', 'awaiting_approval'
              ).value;

              dateObj = commonSetup();
            });

            it('marks it as such', function () {
              expect(dateObj.UI.isRequested).toBe(true);
            });
          });

          describe('when the leave request is for half day am', function () {
            beforeEach(function () {
              leaveRequest.from_date_type = _.find(optionGroupMock.getCollection('hrleaveandabsences_leave_request_day_type'), function (absenceType) {
                return absenceType.name === 'half_day_am';
              }).value;

              dateObj = commonSetup();
            });

            it('marks it as such', function () {
              expect(dateObj.UI.isAM).toBe(true);
            });
          });

          describe('when leave request is for half day pm', function () {
            beforeEach(function () {
              leaveRequest.from_date_type = _.find(optionGroupMock.getCollection('hrleaveandabsences_leave_request_day_type'), function (absenceType) {
                return absenceType.name === 'half_day_pm';
              }).value;

              dateObj = commonSetup();
            });

            it('marks it as such', function () {
              expect(dateObj.UI.isPM).toBe(true);
            });
          });

          describe('when the balance change of the leave request is positive', function () {
            beforeEach(function () {
              leaveRequest.balance_change = 2;

              dateObj = commonSetup();
            });

            it('marks it as such', function () {
              expect(dateObj.UI.isAccruedTOIL).toBe(true);
            });
          });

          function commonSetup () {
            LeaveRequest.all.and.callFake(function () {
              return $q.resolve({ list: [leaveRequest] });
            });

            controller.refresh();
            $rootScope.$digest();

            return getDate(workPattern, leaveRequest.from_date);
          }
        });
      });

      describe('isPublicHoliday()', function () {
        var date;

        beforeEach(function () {
          date = publicHolidayData.all().values[0].date;
        });

        it('checks whether date is a public holiday', function () {
          expect(controller.isPublicHoliday(date)).toBe(true);
        });
      });

      describe('labelPeriod()', function () {
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

      describe('getDayName()', function () {
        var anySunday;

        beforeEach(function () {
          anySunday = '2017/02/05';
        });

        it('returns day name of a date', function () {
          expect(controller.getDayName(anySunday)).toBe('Sun');
        });
      });

      describe('getMonthData()', function () {
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

      describe('refresh()', function () {
        it('marks the calendar as loading', function () {
          controller.refresh();
          expect(controller.loading.calendar).toBe(true);
        });

        describe('after data load is complete', function () {
          beforeEach(function () {
            $rootScope.$digest();
          });

          it('takes the calendar out of the loading phase', function () {
            expect(controller.loading.calendar).toBe(false);
          });
        });
      });

      function compileComponent () {
        controller = $componentController('staffLeaveCalendar', null, { contactId: CRM.vars.leaveAndAbsences.contactId });
        $rootScope.$digest();
      }

      function getDate (workPattern, dateStr) {
        return workPattern.calendar.find(function (data) {
          return data.date === dateStr;
        });
      }

      function getDateFromCalendar (dayType) {
        var date;

        controller.contacts[0].calendarData.forEach(function (month) {
          month.data.forEach(function (dateObj) {
            if (dateObj.date === helper.getDate(dayType).date) {
              date = dateObj;
            }
          });
        });

        return date;
      }
    });
  });
})(CRM);
