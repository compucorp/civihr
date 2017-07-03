/* eslint-env amd, jasmine */
/* global CRM, inject */

(function (CRM) {
  define([
    'common/angular',
    'common/lodash',
    'common/moment',
    'mocks/data/absence-period-data',
    'mocks/data/option-group-mock-data',
    'mocks/data/leave-request-data',
    'mocks/data/public-holiday-data',
    'mocks/data/work-pattern-data',
    'mocks/apis/absence-type-api-mock',
    'mocks/apis/leave-request-api-mock',
    'mocks/apis/public-holiday-api-mock',
    'mocks/apis/option-group-api-mock',
    'mocks/apis/work-pattern-api-mock',
    'leave-absences/shared/config',
    'leave-absences/manager-leave/app'
  ], function (angular, _, moment, absencePeriodData, optionGroupMock, leaveRequestData, publicHolidayData, workPatternMocked) {
    'use strict';

    describe('managerLeaveCalendar', function () {
      var $componentController, $q, $log, $rootScope, controller, $provide,
        OptionGroup, OptionGroupAPIMock, ContactAPIMock, AbsencePeriod,
        Contact, LeaveRequest, WorkPatternAPI;

      beforeEach(module('leave-absences.templates', 'leave-absences.mocks', 'manager-leave', function (_$provide_) {
        $provide = _$provide_;
      }));

      beforeEach(inject(function (AbsenceTypeAPIMock, LeaveRequestAPIMock,
        PublicHolidayAPIMock, WorkPatternAPIMock) {
        $provide.value('AbsenceTypeAPI', AbsenceTypeAPIMock);
        $provide.value('LeaveRequestAPI', LeaveRequestAPIMock);
        $provide.value('PublicHolidayAPI', PublicHolidayAPIMock);
        $provide.value('WorkPatternAPI', WorkPatternAPIMock);
      }));

      beforeEach(inject(['api.contact.mock', function (_ContactAPIMock_) {
        ContactAPIMock = _ContactAPIMock_;
      }]));

      beforeEach(inject(function (
        _$componentController_, _$q_, _$log_, _$rootScope_,
        _OptionGroup_, _OptionGroupAPIMock_, _AbsencePeriod_, _Contact_,
        _LeaveRequest_, _WorkPatternAPI_) {
        $componentController = _$componentController_;
        $q = _$q_;
        $log = _$log_;
        $rootScope = _$rootScope_;
        AbsencePeriod = _AbsencePeriod_;
        Contact = _Contact_;
        LeaveRequest = _LeaveRequest_;
        OptionGroup = _OptionGroup_;
        OptionGroupAPIMock = _OptionGroupAPIMock_;
        WorkPatternAPI = _WorkPatternAPI_;

        spyOn($log, 'debug');
        spyOn(OptionGroup, 'valuesOf').and.callFake(function (name) {
          return OptionGroupAPIMock.valuesOf(name);
        });
        spyOn(Contact, 'all').and.callFake(function () {
          return $q.resolve(ContactAPIMock.mockedContacts());
        });
        spyOn(Contact, 'find').and.callFake(function () {
          var contactInstance = ContactAPIMock.mockedContacts().list[0];
          contactInstance.leaveManagees = jasmine.createSpy('leaveManagees');
          contactInstance.leaveManagees.and.returnValue($q.resolve(ContactAPIMock.leaveManagees()));

          return $q.resolve(contactInstance);
        });
        spyOn(AbsencePeriod, 'all').and.callFake(function () {
          var data = absencePeriodData.all().values;
          // Set 2016 as current period, because Calendar loads data only for the current period initially,
          // and MockedData has 2016 dates
          data[0].current = true;

          return $q.resolve(data);
        });

        /**
         * This is unfortunately a hack to make sure that
         * all mocked contacts have a mocked calendar
         *
         * It takes the mocked WorkPattern.getcalendar response and expands it
         * by making copies of the first mocked calendar and assigning it to
         * each mocked contacts.
         *
         * The ideal solution would be to decouple the mocked data from the
         * `api.contact.mock` (common/mocks/services/api/contact-mock) and put it
         * in a separate file, so that mocks/data/work-pattern-data can inject it
         * and dynamically generate the data by looping through all mocked contacts
         */
        spyOn(WorkPatternAPI, 'getCalendar').and.callFake(function () {
          var mockedGetCalendarResponse = _.clone(workPatternMocked.getCalendar);
          var singleMockedCalendarTemplate = mockedGetCalendarResponse.values[0];

          return $q.resolve(_.assign(mockedGetCalendarResponse, {
            values: ContactAPIMock.mockedContacts().list.map(function (contact) {
              return _.assign(_.clone(singleMockedCalendarTemplate), {
                contact_id: contact.id
              });
            })
          }));
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
        it('loader is hidden', function () {
          expect(controller.loading.page).toBe(false);
        });

        it('absence periods have loaded', function () {
          expect(controller.absencePeriods.length).not.toBe(0);
        });

        it('absence types have loaded', function () {
          expect(controller.absenceTypes.length).not.toBe(0);
        });

        it('regions have loaded', function () {
          expect(controller.regions).toEqual(optionGroupMock.getCollection('hrjc_region'));
        });

        it('departments have loaded', function () {
          expect(controller.departments).toEqual(optionGroupMock.getCollection('hrjc_department'));
        });

        it('locations have loaded', function () {
          expect(controller.locations).toEqual(optionGroupMock.getCollection('hrjc_location'));
        });

        it('level types have loaded', function () {
          expect(controller.levelTypes).toEqual(optionGroupMock.getCollection('hrjc_level_type'));
        });

        describe('contacts', function () {
          it('contacts managed by logged in user have loaded', function () {
            expect(controller.managedContacts.length).not.toBe(0);
          });

          it('contacts after filteraion have loaded', function () {
            expect(controller.filteredContacts).not.toBe(0);
          });
        });

        it('calendar have loaded for each contact', function () {
          _.each(controller.managedContacts, function (contact) {
            expect(Object.keys(contact.calendarData[0]).length).not.toBe(0);
          });
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

      describe('filterContacts', function () {
        beforeEach(function () {
          controller.filteredContacts = ContactAPIMock.mockedContacts().list;
        });

        describe('when contacts with leaves filter is false', function () {
          var returnValue;

          beforeEach(function () {
            controller.filters.contacts_with_leaves = false;
            returnValue = controller.filterContacts();
          });

          it('does not filter the contacts', function () {
            expect(returnValue).toEqual(controller.filteredContacts);
          });
        });

        describe('when contacts with leaves filter is true', function () {
          var returnValue,
            anyLeaveRequest;

          beforeEach(function () {
            controller.filters.contacts_with_leaves = true;
            anyLeaveRequest = leaveRequestData.all().values[0];
            returnValue = controller.filterContacts();
          });

          it('filters the contacts which have a leave request', function () {
            expect(!!_.find(returnValue, function (contact) {
              return contact.id === anyLeaveRequest.contact_id;
            })).toBe(true);
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
          beforeEach(function () {
            controller.refresh();
            $rootScope.$digest();
          });

          it('is set', function () {
            _.each(controller.managedContacts, function (contact) {
              var date = getDateFromCalendar(contact, 'weekend');
              expect(date.UI.isWeekend).toBe(true);
            });
          });
        });

        describe('isNonWorkingDay', function () {
          beforeEach(function () {
            controller.refresh();
            $rootScope.$digest();
          });

          it('is set', function () {
            _.each(controller.managedContacts, function (contact) {
              var date = getDateFromCalendar(contact, 'non_working_day');
              expect(date.UI.isNonWorkingDay).toBe(true);
            });
          });
        });

        describe('isPublicHoliday', function () {
          var dateObj;

          beforeEach(function () {
            spyOn(controller, 'isPublicHoliday').and.returnValue(true);
            controller.refresh();
            $rootScope.$digest();
          });

          it('is set', function () {
            _.each(controller.managedContacts, function (contact) {
              // any date
              dateObj = contact.calendarData[0].data[0];
              expect(dateObj.UI.isPublicHoliday).toBe(true);
            });
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

              leaveRequest.contact_id = '203';
              leaveRequest.status_id = status.value;
              leaveRequest.balance_change = -1;
              commonSetup();
            });

            it('isRequested flag is true', function () {
              _.each(controller.managedContacts, function (contact) {
                dateObj = getDate(contact, leaveRequest.from_date);
                expect(dateObj.UI.isRequested).toBe(true);
              });
            });

            it('styles are fetched', function () {
              var color = _.find(controller.absenceTypes, function (absenceType) {
                return absenceType.id === leaveRequest.type_id;
              }).color;

              _.each(controller.managedContacts, function (contact) {
                dateObj = getDate(contact, leaveRequest.from_date);
                expect(dateObj.UI.styles).toEqual({
                  backgroundColor: color,
                  borderColor: color
                });
              });
            });
          });

          describe('when leave request is for half day am', function () {
            beforeEach(function () {
              var halfDayAMValue = optionGroupMock.specificObject(
                'hrleaveandabsences_leave_request_day_type', 'name', 'half_day_am').value;

              leaveRequest.from_date_type = halfDayAMValue;
              commonSetup();
            });

            it('AM flag is set', function () {
              expect(dateObj.UI.isAM).toBe(true);
            });
          });

          describe('when leave request is for half day pm', function () {
            beforeEach(function () {
              var halfDayPMValue = optionGroupMock.specificObject(
                'hrleaveandabsences_leave_request_day_type', 'name', 'half_day_pm').value;

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

      function compileComponent () {
        controller = $componentController('managerLeaveCalendar', null, { contactId: CRM.vars.leaveAndAbsences.contactId });
        $rootScope.$digest();
      }

      function getDate (contact, dateStr) {
        var date;

        _.each(contact.calendarData, function (month) {
          _.each(month.data, function (dateObj) {
            if (dateObj.date === dateStr) {
              date = dateObj;
            }
          });
        });

        return date;
      }

      function getDateByType (dayType) {
        return workPatternMocked.getCalendar.values[0].calendar.find(function (data) {
          return data.type.name === dayType;
        });
      }

      function getDateFromCalendar (contact, dayType) {
        var date;
        _.each(contact.calendarData, function (month) {
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
