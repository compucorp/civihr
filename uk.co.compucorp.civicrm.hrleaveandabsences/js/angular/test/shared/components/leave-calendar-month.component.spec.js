/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/lodash',
    'common/moment',
    'mocks/helpers/helper',
    'common/mocks/data/contact.data',
    'mocks/data/absence-period-data',
    'mocks/data/absence-type-data',
    'mocks/data/leave-request-data',
    'mocks/data/option-group-mock-data',
    'mocks/data/public-holiday-data',
    'mocks/data/work-pattern-data',
    'mocks/apis/leave-request-api-mock',
    'mocks/apis/option-group-api-mock',
    'mocks/apis/work-pattern-api-mock',
    'leave-absences/my-leave/app'
  ], function (_, moment, helper, ContactData, AbsencePeriodData, AbsenceTypeData, LeaveRequestData, OptionGroupData, PublicHolidayData, WorkPatternData) {
    'use strict';

    describe('leaveCalendarMonth', function () {
      var $componentController, $log, $provide, $q, $rootScope, Calendar,
        LeaveRequest, OptionGroup, controller, daysInFebruary, february, leaveRequestInFebruary,
        period2016, publicHolidays;
      var currentContactId = CRM.vars.leaveAndAbsences.contactId;
      var contactIdsToReduceTo = null;

      beforeEach(module('leave-absences.templates', 'leave-absences.mocks', 'my-leave', function (_$provide_) {
        $provide = _$provide_;
      }));

      beforeEach(inject(function (LeaveRequestAPIMock, WorkPatternAPIMock) {
        $provide.value('LeaveRequestAPI', LeaveRequestAPIMock);
        $provide.value('WorkPatternAPI', WorkPatternAPIMock);
      }));

      beforeEach(inject(function (_$componentController_, _$log_, _$q_, _$rootScope_,
        _Calendar_, _LeaveRequest_, _OptionGroup_, OptionGroupAPIMock) {
        $componentController = _$componentController_;
        $log = _$log_;
        $q = _$q_;
        $rootScope = _$rootScope_;
        Calendar = _Calendar_;
        LeaveRequest = _LeaveRequest_;
        OptionGroup = _OptionGroup_;

        february = { index: 1, year: 2016 };
        daysInFebruary = moment().month(february.index).year(february.year).daysInMonth();
        period2016 = _.clone(AbsencePeriodData.all().values[0]);
        publicHolidays = PublicHolidayData.all().values;
        leaveRequestInFebruary = LeaveRequestData.all().values[0];

        spyOn($log, 'debug');
        spyOn(Calendar, 'get').and.callThrough();
        spyOn(LeaveRequest, 'all').and.callFake(function () {
          return $q.resolve({ list: [leaveRequestInFebruary] }); // leave request from February
        });
        spyOn(OptionGroup, 'valuesOf').and.callFake(function (name) {
          return OptionGroupAPIMock.valuesOf(name);
        });

        compileComponent();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      it('not showing', function () {
        expect(controller.visible).toBe(false);
      });

      it('has the maximum size of the contacts list per page defined', function () {
        expect(controller.pageSize).toBeDefined();
        expect(controller.pageSize).toEqual(jasmine.any(Number));
      });

      it('has the current page set to zero', function () {
        expect(controller.currentPage).toBe(0);
      });

      it('has the show-contact-name binding set to false by default', function () {
        expect(controller.showContactName).toBe(false);
      });

      it('has the show-only-with-leave-request name binding set to false by default', function () {
        expect(controller.showOnlyWithLeaveRequests).toBe(false);
      });

      describe('on init', function () {
        it("does not load the contacts' work pattern calendars", function () {
          expect(Calendar.get).not.toHaveBeenCalled();
        });

        it("does not load the contacts' leave requests", function () {
          expect(LeaveRequest.all).not.toHaveBeenCalled();
        });
      });

      describe('on "show months" event', function () {
        describe('when it is included in the list of months to show', function () {
          beforeEach(function () {
            sendShowMonthsSignal();
          });

          it('shows itself', function () {
            expect(controller.visible).toBe(true);
          });

          describe("contacts' work pattern calendar", function () {
            it('loads the work pattern calendars', function () {
              expect(Calendar.get).toHaveBeenCalled();
            });

            it('loads only the work pattern calendars of the currently loaded contacts', function () {
              expect(Calendar.get.calls.mostRecent().args[0]).toEqual(controller.contacts.map(function (contact) {
                return contact.id;
              }));
            });

            it("uses the selected months' first and last day as date delimiters", function () {
              var month = controller.month;

              expect(Calendar.get.calls.mostRecent().args[1]).toBe(month.days[0].date);
              expect(Calendar.get.calls.mostRecent().args[2]).toBe(month.days[month.days.length - 1].date);
            });
          });

          describe('leave requests', function () {
            it('loads the leave requests', function () {
              expect(LeaveRequest.all).toHaveBeenCalled();
            });

            it('loads only the approved, admin approved, or awaiting approval leave requests', function () {
              expect(LeaveRequest.all.calls.mostRecent().args[0]).toEqual(jasmine.objectContaining({
                status_id: {'IN': [
                  OptionGroupData.specificObject('hrleaveandabsences_leave_request_status', 'name', 'approved').value,
                  OptionGroupData.specificObject('hrleaveandabsences_leave_request_status', 'name', 'admin_approved').value,
                  OptionGroupData.specificObject('hrleaveandabsences_leave_request_status', 'name', 'awaiting_approval').value
                ]}
              }));
            });

            it('loads only the leave requests belonging to the loaded contacts', function () {
              expect(LeaveRequest.all.calls.mostRecent().args[0]).toEqual(jasmine.objectContaining({
                contact_id: { 'IN': controller.contacts.map(function (contact) {
                  return contact.id;
                })}
              }));
            });

            it('loads all requests touching the specified month', function () {
              var month = controller.month;

              expect(LeaveRequest.all.calls.mostRecent().args[0]).toEqual(
                jasmine.objectContaining({
                  from_date: { to: month.days[month.days.length - 1].date },
                  to_date: { from: month.days[0].date }
                })
              );
            });
          });

          describe('contacts', function () {
            describe('when there are contacts to reduce to', function () {
              var randomContactIds = [_.sample(ContactData.all.values).contact_id];

              beforeEach(function () {
                contactIdsToReduceTo = randomContactIds;

                compileComponent();
                sendShowMonthsSignal();
                controller.contactsList();
                $rootScope.$digest();
              });

              it('reduces the list of contacts', function () {
                expect(controller.contacts.length).toEqual(randomContactIds.length);
                expect(controller.contacts[0].contact_id).toEqual(randomContactIds[0]);
              });
            });

            describe('when there are no contacts to reduce to', function () {
              beforeEach(function () {
                contactIdsToReduceTo = null;

                compileComponent();
                sendShowMonthsSignal();
                $rootScope.$digest();
              });

              it('does not reduce the list of contacts', function () {
                expect(controller.contacts).toEqual(ContactData.all.values);
              });
            });
          });

          describe('current page', function () {
            beforeEach(function () {
              controller.currentPage = 5;

              sendShowMonthsSignal();
            });

            it('resets it to 0', function () {
              expect(controller.currentPage).toBe(0);
            });
          });

          describe('when the reload is not forced', function () {
            beforeEach(function () {
              Calendar.get.calls.reset();
              LeaveRequest.all.calls.reset();

              sendShowMonthsSignal();
            });

            it('does not fetch the data again', function () {
              expect(Calendar.get).not.toHaveBeenCalled();
              expect(LeaveRequest.all).not.toHaveBeenCalled();
            });
          });

          describe('when the reload is forced', function () {
            beforeEach(function () {
              Calendar.get.calls.reset();
              LeaveRequest.all.calls.reset();

              sendShowMonthsSignal(true, true);
            });

            it('fetches the data again', function () {
              expect(Calendar.get).toHaveBeenCalled();
              expect(LeaveRequest.all).toHaveBeenCalled();
            });
          });
        });

        describe('when it is not included in the list of months to show', function () {
          beforeEach(function () {
            sendShowMonthsSignal(false);
          });

          it('hides itself', function () {
            expect(controller.visible).toBe(false);
          });

          it("does not load the contacts' work pattern calendars", function () {
            expect(Calendar.get).not.toHaveBeenCalled();
          });

          it("does not load the contacts' leave requests", function () {
            expect(LeaveRequest.all).not.toHaveBeenCalled();
          });
        });
      });

      describe('month structure', function () {
        it('contains a flag for the loading status', function () {
          expect(controller.month.loading).toBeDefined();
        });

        it('contains the month long name', function () {
          expect(controller.month.name).toBe('February');
        });

        it('contains the month index', function () {
          expect(controller.month.index).toBe(february.index);
        });

        it('contains the year', function () {
          expect(controller.month.year).toBe(february.year);
        });

        it('contains the list of days', function () {
          expect(controller.month.days.length).toEqual(daysInFebruary);
        });

        describe('when the given period does not start at the beginning of the month', function () {
          beforeEach(function () {
            period2016.start_date = '2016-02-20';

            compileComponent();
          });

          it('still contains all the days anyway', function () {
            expect(controller.month.days.length).toEqual(daysInFebruary);
          });
        });

        describe('when the given period does not finish at the end of the month', function () {
          beforeEach(function () {
            period2016.end_date = '2016-02-20';

            compileComponent();
          });

          it('still contains all the days anyway', function () {
            expect(controller.month.days.length).toEqual(daysInFebruary);
          });
        });
      });

      describe('day structure', function () {
        var twentiethOfFebruary;

        beforeEach(function () {
          twentiethOfFebruary = controller.month.days[19];
        });

        it('contains the date', function () {
          expect(twentiethOfFebruary.date).toBe('2016-02-20');
        });

        it('contains the day index', function () {
          expect(twentiethOfFebruary.index).toBe('20');
        });

        it('contains the name of day', function () {
          expect(twentiethOfFebruary.name).toBe('Sat');
        });

        it('contains the data specific for each contact in the calendar', function () {
          expect(twentiethOfFebruary.contactsData).toEqual(jasmine.any(Object));
        });

        describe('when the day is within the currently selected period', function () {
          it('is marked as enabled', function () {
            expect(twentiethOfFebruary.enabled).toBe(true);
          });
        });

        describe('when the day is outside the currently selected period', function () {
          beforeEach(function () {
            period2016.start_date = '2016-02-21';
            compileComponent();

            twentiethOfFebruary = controller.month.days[19];
          });

          it('is marked as disabled', function () {
            expect(twentiethOfFebruary.enabled).toBe(false);
          });
        });

        describe('current day flag', function () {
          describe('when the day is actually the current one', function () {
            beforeEach(function () {
              setCurrentDay(new Date(2016, 1, 20));
              twentiethOfFebruary = controller.month.days[19];
            });

            it('is set to true', function () {
              expect(twentiethOfFebruary.current).toBe(true);
            });
          });

          describe('when the day is not the current one', function () {
            beforeEach(function () {
              setCurrentDay(new Date(2016, 1, 1));
              twentiethOfFebruary = controller.month.days[19];
            });

            it('is set to false', function () {
              expect(twentiethOfFebruary.current).toBe(false);
            });
          });

          afterEach(function () {
            jasmine.clock().uninstall();
          });

          function setCurrentDay (date) {
            jasmine.clock().mockDate(date);
            compileComponent();
          }
        });
      });

      describe("day's data specific for each contact", function () {
        var contactData;

        beforeEach(function () {
          sendShowMonthsSignal();
        });

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
          beforeEach(inject(function (PublicHoliday) {
            publicHolidays.push({
              id: '123456789',
              title: 'Fake Holiday',
              date: getDayWithType('non_working_day').date,
              is_active: true
            });

            compileComponent(true);

            contactData = getDayWithType('non_working_day', true);
          }));

          it('marks it as such', function () {
            expect(contactData.isPublicHoliday).toBe(true);
          });
        });

        describe('when the contact has recorded a leave request on the day', function () {
          var leaveRequest, workPattern;

          beforeEach(function () {
            leaveRequest = _.clone(LeaveRequestData.singleDataSuccess().values[0]);
            workPattern = _.find(WorkPatternData.getCalendar.values, function (workPattern) {
              return workPattern.contact_id === currentContactId;
            });

            workPattern.calendar[0].date = leaveRequest.from_date;
          });

          describe('basic tests', function () {
            beforeEach(function () {
              leaveRequest.status_id = OptionGroupData.specificObject(
                'hrleaveandabsences_leave_request_status', 'name', 'approved'
              ).value;

              contactData = commonSetup();
            });

            it('contains a reference to the leave request itself', function () {
              expect(contactData.leaveRequest).toBe(leaveRequest);
            });

            it('assigns it the colors of its absence type', function () {
              var absenceTypeColor = _.find(controller.supportData.absenceTypes, function (absenceType) {
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
              leaveRequest.status_id = OptionGroupData.specificObject(
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
              leaveRequest.from_date_type = _.find(OptionGroupData.getCollection('hrleaveandabsences_leave_request_day_type'), function (absenceType) {
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
              leaveRequest.from_date_type = _.find(OptionGroupData.getCollection('hrleaveandabsences_leave_request_day_type'), function (absenceType) {
                return absenceType.name === 'half_day_pm';
              }).value;

              contactData = commonSetup();
            });

            it('marks it as such', function () {
              expect(contactData.isPM).toBe(true);
            });
          });

          describe('when the leave request is of toil type', function () {
            beforeEach(function () {
              leaveRequest.request_type = 'toil';

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

            compileComponent(true);

            controller.month.days.forEach(function (dayObj) {
              if (dayObj.date === leaveRequest.from_date) {
                day = dayObj;
              }
            });

            return day.contactsData[currentContactId];
          }
        });

        function getDayWithType (dayType, returnContactData) {
          var day;

          controller.month.days.forEach(function (dayObj) {
            if (dayObj.date === helper.getDate(dayType).date) {
              day = dayObj;
            }
          });

          return returnContactData ? day.contactsData[currentContactId] : day;
        }
      });

      describe('event listeners', function () {
        beforeEach(function () {
          sendShowMonthsSignal();
        });

        describe('when a leave request is deleted', function () {
          var leaveRequestToDelete;

          beforeEach(function () {
            leaveRequestToDelete = leaveRequestInFebruary;

            LeaveRequest.all.calls.reset();
            $rootScope.$emit('LeaveRequest::deleted', leaveRequestToDelete);
            $rootScope.$digest();
          });

          it('does not re-fetch the leave requests from the backend', function () {
            expect(LeaveRequest.all).not.toHaveBeenCalled();
          });

          it('resets the properties of each day that the leave request spans', function () {
            expect(getLeaveRequestDays(leaveRequestToDelete).every(isDayContactDataNull)).toBe(true);
          });
        });

        describe('when a leave request is added', function () {
          var leaveRequestToAdd;

          beforeEach(function () {
            leaveRequestToAdd = _.clone(leaveRequestInFebruary);
            leaveRequestToAdd = modifyLeaveRequestData(leaveRequestToAdd, true);

            LeaveRequest.all.calls.reset();
            $rootScope.$emit('LeaveRequest::new', leaveRequestToAdd);
            $rootScope.$digest();
          });

          it('does not re-fetch the leave requests from the backend', function () {
            expect(LeaveRequest.all).not.toHaveBeenCalled();
          });

          it('updates the properties of each day that the leave request spans', function () {
            expect(getLeaveRequestDays(leaveRequestToAdd).every(isDayContactDataNull)).toBe(false);
          });
        });

        describe('when a leave request is updated and its dates have changed', function () {
          var leaveRequestToUpdate, oldDays, newDays;

          beforeEach(function () {
            leaveRequestToUpdate = _.clone(leaveRequestInFebruary);
            oldDays = getLeaveRequestDays(leaveRequestToUpdate);
            leaveRequestToUpdate = modifyLeaveRequestData(leaveRequestToUpdate);

            LeaveRequest.all.calls.reset();

            $rootScope.$emit('LeaveRequest::edit', leaveRequestToUpdate);
            $rootScope.$digest();

            newDays = getLeaveRequestDays(leaveRequestToUpdate);
          });

          it('does not re-fetch the leave requests from the backend', function () {
            expect(LeaveRequest.all).not.toHaveBeenCalled();
          });

          it('resets the properties of the days that the leave request does not span anymore', function () {
            expect(oldDays.every(isDayContactDataNull)).toBe(true);
          });

          it('sets the properties of the days that the leave request now spans', function () {
            expect(newDays.every(isDayContactDataNull)).toBe(false);
          });
        });

        function modifyLeaveRequestData (leaveRequest, modifyId) {
          var modified = _.assign({}, leaveRequest, {
            from_date: '2016-02-20',
            to_date: '2016-02-21',
            dates: [
              { 'id': '1', 'date': '2016-02-20' },
              { 'id': '2', 'date': '2016-02-21' }
            ]
          });

          if (modifyId === true) {
            modified.id = '1';
          }

          return modified;
        }

        function getLeaveRequestDays (leaveRequest) {
          var days = [];
          var pointerDate = moment(leaveRequest.from_date).clone();
          var toDate = moment(leaveRequest.to_date);

          while (pointerDate.isSameOrBefore(toDate)) {
            days.push(_.find(controller.month.days, function (day) {
              return day.date === pointerDate.format('YYYY-MM-DD');
            }));

            pointerDate.add(1, 'day');
          }

          return days;
        }

        function isDayContactDataNull (day) {
          var contactData = day.contactsData[currentContactId];

          return contactData.leaveRequest === null &&
            contactData.styles === null &&
            contactData.isAccruedTOIL === null &&
            contactData.isRequested === null &&
            contactData.isAM === null &&
            contactData.isPM === null;
        }
      });

      describe('contactsList()', function () {
        beforeEach(function () {
          sendShowMonthsSignal();
        });

        describe('when show-only-with-leave-requests is set to false', function () {
          beforeEach(function () {
            controller.showOnlyWithLeaveRequests = false;
          });

          it('returns all contacts', function () {
            expect(controller.contactsList()).toEqual(controller.contacts);
          });
        });

        describe('when show-only-with-leave-requests is set to true', function () {
          beforeEach(function () {
            controller.showOnlyWithLeaveRequests = true;
          });

          it('returns only the contacts with at least a leave request in the month', function () {
            expect(controller.contactsList()).toEqual(controller.contacts.filter(function (contact) {
              return contact.id === leaveRequestInFebruary.contact_id;
            }));
          });
        });
      });

      describe('on destroy', function () {
        var $rootScope;

        beforeEach(inject(function (_$rootScope_) {
          $rootScope = _$rootScope_;
          spyOn($rootScope, '$emit');

          controller.$onDestroy();
        }));

        it('sends an event', function () {
          expect($rootScope.$emit).toHaveBeenCalledWith('LeaveCalendar::monthDestroyed');
        });
      });

      function compileComponent (sendSignal) {
        controller = $componentController('leaveCalendarMonth', null, {
          contacts: ContactData.all.values,
          month: february,
          period: period2016,
          supportData: {
            absenceTypes: AbsenceTypeData.all().values,
            dayTypes: OptionGroupData.getCollection('hrleaveandabsences_leave_request_day_type'),
            leaveRequestStatuses: OptionGroupData.getCollection('hrleaveandabsences_leave_request_status'),
            publicHolidays: publicHolidays
          },
          contactIdsToReduceTo: contactIdsToReduceTo
        });

        !!sendSignal && sendShowMonthsSignal();
      }

      /**
       * Sends the "show months" signal to the component
       *
       * @param  {Boolean} includeFebruary Whether to include Feb (the current month)
       * @param  {Boolean} forceReload Whether to force reloading the month's data
       */
      function sendShowMonthsSignal (includeFebruary, forceReload) {
        var selectedMonths = [{ index: 11, year: 2016 }];

        includeFebruary = typeof includeFebruary === 'undefined' ? true : !!includeFebruary;
        forceReload = typeof forceReload === 'undefined' ? false : !!forceReload;

        includeFebruary && selectedMonths.push(february);

        $rootScope.$emit('LeaveCalendar::showMonths', selectedMonths, forceReload);
        $rootScope.$digest();
      }
    });
  });
}(CRM));
