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
        spyOn(AbsencePeriod, 'all');

        // Set 2016 as current period, because Calendar loads data only for the current period initially,
        // and MockedData has 2016 dates
        amend2016Period({ current: true });

        compileComponent();
      }));

      // The mocked "work pattern calendar" and "leave request" data is made for
      // the month of February, so we pretend we are in February
      beforeAll(function () {
        jasmine.clock().mockDate(new Date(2016, 1, 1));
      });

      afterAll(function () {
        jasmine.clock().uninstall();
      });

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
          expect(controller.selectedMonths).toEqual([moment().month()]);
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

            expect(months[0].index).toEqual(periodStartDate.month());
            expect(months[0].year).toEqual(periodStartDate.year());
            expect(months[months.length - 1].index).toEqual(periodEndDate.month());
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
          beforeEach(function () {
            LeaveRequest.all.calls.reset();
            compileComponent();
          });

          it('loads the leave requests', function () {
            expect(LeaveRequest.all.calls.any()).toBe(true);
          });

          it('loads only the approved, admin approved, or awaiting approval leave requests', function () {
            expect(LeaveRequest.all.calls.mostRecent().args[0]).toEqual(jasmine.objectContaining({
              status_id: {'IN': [
                optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'name', 'approved').value,
                optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'name', 'admin_approved').value,
                optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'name', 'awaiting_approval').value
              ]}
            }));
          });

          it('loads only the leave requests belonging to the loaded contacts', function () {
            expect(LeaveRequest.all.calls.mostRecent().args[0]).toEqual(jasmine.objectContaining({
              contact_id: { 'IN': [CRM.vars.leaveAndAbsences.contactId] }
            }));
          });

          describe('splitting the loading by selected months', function () {
            it('loads the leave requests individually for each selected month', function () {
              expect(LeaveRequest.all.calls.count()).toBe(controller.selectedMonths.length);
            });

            it('uses the selected months\' first and last day as date delimiters', function () {
              LeaveRequest.all.calls.all().forEach(function (call, index) {
                var callMonth = controller.months[controller.selectedMonths[index]];

                expect(call.args[0]).toEqual(jasmine.objectContaining({
                  from_date: { from: callMonth.days[0].date },
                  to_date: { to: callMonth.days[callMonth.days.length - 1].date }
                }));
              });
            });
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

      describe('calendar data structure', function () {
        describe('month', function () {
          var july, daysInJuly;

          beforeEach(function () {
            july = controller.months[6];
            daysInJuly = moment().month(july.index).daysInMonth();
          });

          it('contains a flag for the loading status', function () {
            expect(july.loading).toBeDefined();
          });

          it('contains the month long name', function () {
            expect(july.name.long).toBe('July');
          });

          it('contains the month short name', function () {
            expect(july.name.short).toBe('Jul');
          });

          it('contains the month index', function () {
            expect(july.index).toBe(6);
          });

          it('contains the year', function () {
            expect(july.year).toBe(moment(controller.selectedPeriod.start_date).year());
          });

          it('contains the list of days', function () {
            expect(july.days.length).toEqual(daysInJuly);
          });

          describe('when the currently selected period does not start at the beginning of the month', function () {
            var january, daysInJanuary;

            beforeEach(function () {
              amend2016Period({ start_date: '2016-01-20' });
              compileComponent();
            });

            beforeEach(function () {
              january = controller.months[0];
              daysInJanuary = moment().month(january.index).daysInMonth();
            });

            it('still contains all the days anyway', function () {
              expect(january.days.length).toEqual(daysInJanuary);
            });
          });

          describe('when the currently selected period does not finish at the end of the month', function () {
            var december, daysInDecember;

            beforeEach(function () {
              amend2016Period({ end_date: '2016-12-26' });
              compileComponent();
            });

            beforeEach(function () {
              december = controller.months[11];
              daysInDecember = moment().month(december.index).daysInMonth();
            });

            it('still contains all the days anyway', function () {
              expect(december.days.length).toEqual(daysInDecember);
            });
          });
        });

        describe('day', function () {
          var twentiethOfJanuary;

          beforeEach(function () {
            twentiethOfJanuary = controller.months[0].days[19];
          });

          it('contains the date', function () {
            expect(twentiethOfJanuary.date).toBe('2016-01-20');
          });

          it('contains the day index', function () {
            expect(twentiethOfJanuary.index).toBe('20');
          });

          it('contains the name of day', function () {
            expect(twentiethOfJanuary.name).toBe('Wed');
          });

          it('contains the data specific for each contact in the calendar', function () {
            expect(twentiethOfJanuary.contactsData).toEqual(jasmine.any(Object));
          });

          describe('when the day is within the currently selected period', function () {
            it('is marked as enabled', function () {
              expect(twentiethOfJanuary.enabled).toBe(true);
            });
          });

          describe('when the day is outside the currently selected period', function () {
            beforeEach(function () {
              amend2016Period({ start_date: '2016-01-22' });
              compileComponent();

              twentiethOfJanuary = controller.months[0].days[19];
            });

            it('is marked as disabled', function () {
              expect(twentiethOfJanuary.enabled).toBe(false);
            });
          });
        });

        describe('day\'s data specific for each contact', function () {
          var contactData;

          it('is indexed by contact id', function () {
            var indexes = Object.keys(getDayWithType('working_day').contactsData);

            expect(indexes).toEqual(controller.contacts.map(function (contact) {
              return contact.id;
            }));
          });

          describe('when the day is a weekend for a contact', function () {
            beforeEach(function () {
              contactData = getDayWithType('weekend', true);
            });

            it('marks it as such', function () {
              expect(contactData.isWeekend).toBe(true);
            });
          });

          describe('when the day is a non-working day for a contact', function () {
            beforeEach(function () {
              contactData = getDayWithType('non_working_day', true);
            });

            it('marks it as such', function () {
              expect(contactData.isNonWorkingDay).toBe(true);
            });
          });

          describe('when the day is a public holiday for a contact', function () {
            beforeEach(function () {
              // set this so that every date is marked as public holiday
              spyOn(controller, 'isPublicHoliday').and.returnValue(true);
              controller.refresh();
              $rootScope.$digest();
              // pick any date
              contactData = getDayWithType('non_working_day', true);
            });

            it('marks it as such', function () {
              expect(contactData.isPublicHoliday).toBe(true);
            });
          });

          describe('when the contact has recorded a leave request on the day', function () {
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

                contactData = commonSetup();
              });

              it('contains a reference to the leave request itself', function () {
                expect(contactData.leaveRequest).toBe(leaveRequest);
              });

              it('assigns it the colors of its absence type', function () {
                var absenceTypeColor = _.find(controller.absenceTypes, function (absenceType) {
                  return absenceType.id === leaveRequest.type_id;
                }).color;

                expect(contactData.styles).toEqual({
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

                contactData = commonSetup();
              });

              it('marks it as such', function () {
                expect(contactData.isRequested).toBe(true);
              });
            });

            describe('when the leave request is for half day am', function () {
              beforeEach(function () {
                leaveRequest.from_date_type = _.find(optionGroupMock.getCollection('hrleaveandabsences_leave_request_day_type'), function (absenceType) {
                  return absenceType.name === 'half_day_am';
                }).value;

                contactData = commonSetup();
              });

              it('marks it as such', function () {
                expect(contactData.isAM).toBe(true);
              });
            });

            describe('when leave request is for half day pm', function () {
              beforeEach(function () {
                leaveRequest.from_date_type = _.find(optionGroupMock.getCollection('hrleaveandabsences_leave_request_day_type'), function (absenceType) {
                  return absenceType.name === 'half_day_pm';
                }).value;

                contactData = commonSetup();
              });

              it('marks it as such', function () {
                expect(contactData.isPM).toBe(true);
              });
            });

            describe('when the balance change of the leave request is positive', function () {
              beforeEach(function () {
                leaveRequest.balance_change = 2;

                contactData = commonSetup();
              });

              it('marks it as such', function () {
                expect(contactData.isAccruedTOIL).toBe(true);
              });
            });

            function commonSetup () {
              var day;

              LeaveRequest.all.and.callFake(function () {
                return $q.resolve({ list: [leaveRequest] });
              });

              controller.refresh();
              $rootScope.$digest();

              controller.months.forEach(function (month) {
                month.days.forEach(function (dayObj) {
                  if (dayObj.date === leaveRequest.from_date) {
                    day = dayObj;
                  }
                });
              });

              return day.contactsData[CRM.vars.leaveAndAbsences.contactId];
            }
          });

          function getDayWithType (dayType, returnContactData) {
            var day;

            controller.months.forEach(function (month) {
              month.days.forEach(function (dayObj) {
                if (dayObj.date === helper.getDate(dayType).date) {
                  day = dayObj;
                }
              });
            });

            return returnContactData ? day.contactsData[CRM.vars.leaveAndAbsences.contactId] : day;
          }
        });
      });

      describe('selected months watcher', function () {
        beforeEach(function () {
          LeaveRequest.all.calls.reset();
        });

        describe('when some other months are selected', function () {
          beforeEach(function () {
            controller.selectedMonths = [1, 2, 3];
            $rootScope.$digest();
          });

          it('loads the leave requests of the selected months', function () {
            expect(LeaveRequest.all.calls.count()).toBe(3);
          });
        });

        describe('when none of the months are selected', function () {
          beforeEach(function () {
            controller.selectedMonths = [];
            $rootScope.$digest();
          });

          it('loads the leave requests for all the months', function () {
            var startDate = moment(controller.selectedPeriod.start_date);
            var endDate = moment(controller.selectedPeriod.end_date);

            expect(LeaveRequest.all.calls.count()).toBe(endDate.diff(startDate, 'months') + 1);
          });
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

      describe('refresh()', function () {
        beforeEach(function () {
          controller.refresh();
          $rootScope.$digest();
        });

        // TODO
      });

      function amend2016Period (params) {
        AbsencePeriod.all.and.callFake(function () {
          var absencePeriods = _.clone(absencePeriodData.all().values);
          _.assign(absencePeriods[0], params);

          return $q.resolve(absencePeriods);
        });
      }

      function compileComponent () {
        controller = $componentController('staffLeaveCalendar', null, { contactId: CRM.vars.leaveAndAbsences.contactId });
        $rootScope.$digest();
      }
    });
  });
})(CRM);
