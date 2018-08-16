/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/lodash',
    'common/moment',
    'leave-absences/mocks/helpers/helper',
    'common/mocks/data/contact.data',
    'leave-absences/mocks/data/absence-period.data',
    'leave-absences/mocks/data/absence-type.data',
    'leave-absences/mocks/data/leave-request.data',
    'leave-absences/mocks/data/option-group.data',
    'leave-absences/mocks/data/public-holiday.data',
    'leave-absences/mocks/data/work-pattern.data',
    'common/mocks/services/api/contract-mock',
    'common/models/contract',
    'leave-absences/mocks/apis/leave-request-api-mock',
    'leave-absences/mocks/apis/option-group-api-mock',
    'leave-absences/mocks/apis/work-pattern-api-mock',
    'common/services/pub-sub',
    'leave-absences/my-leave/app'
  ], function (_, moment, helper, ContactData, AbsencePeriodData, AbsenceTypeData, LeaveRequestData, OptionGroupData, PublicHolidayData, WorkPatternData) {
    'use strict';

    describe('leaveCalendarMonth', function () {
      var $componentController, $log, $provide, $q, $rootScope, allContracts, Calendar, Contract,
        LeaveRequest, OptionGroup, controller, daysInFebruary, february, leaveRequestInFebruary,
        period2016, publicHolidays, pubSub, contactData, leaveRequest, leaveRequestAttributes;
      var currentContactId = CRM.vars.leaveAndAbsences.contactId;
      var serverDateFormat = 'YYYY-MM-DD';

      beforeEach(module('common.services', 'leave-absences.templates',
        'leave-absences.mocks', 'my-leave', function (_$provide_) {
          $provide = _$provide_;
        }));

      beforeEach(inject(function (LeaveRequestAPIMock, WorkPatternAPIMock) {
        $provide.value('LeaveRequestAPI', LeaveRequestAPIMock);
        $provide.value('WorkPatternAPI', WorkPatternAPIMock);
      }));

      beforeEach(inject(['api.contract.mock', function (JobContractAPIMock) {
        $provide.value('api.contract', JobContractAPIMock);
      }]));

      beforeEach(inject(function (_$componentController_, _$log_, _$q_, _$rootScope_,
        _Calendar_, _Contract_, _LeaveRequest_, _OptionGroup_, OptionGroupAPIMock, _pubSub_) {
        $componentController = _$componentController_;
        $log = _$log_;
        $q = _$q_;
        $rootScope = _$rootScope_;
        Calendar = _Calendar_;
        Contract = _Contract_;
        LeaveRequest = _LeaveRequest_;
        OptionGroup = _OptionGroup_;

        february = { index: '2016-02', month: 1, year: 2016 };
        daysInFebruary = moment().month(february.month).year(february.year).daysInMonth();
        period2016 = _.clone(AbsencePeriodData.all().values[0]);
        publicHolidays = PublicHolidayData.all().values;
        leaveRequestInFebruary = LeaveRequestData.all().values[0];
        pubSub = _pubSub_;

        spyOn($log, 'debug');
        spyOn(Calendar, 'get').and.callThrough();
        spyOn(LeaveRequest, 'all').and.callFake(function () {
          return $q.resolve({ list: [leaveRequestInFebruary] }); // leave request from February
        });
        spyOn(OptionGroup, 'valuesOf').and.callFake(function (name) {
          return OptionGroupAPIMock.valuesOf(name);
        });
      }));

      beforeEach(function (done) {
        prepareJobContracts(done);
        compileComponent();
      });

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
            sendShowMonthSignal();
          });

          it('shows itself', function () {
            expect(controller.visible).toBe(true);
          });

          describe("contacts' work pattern calendar", function () {
            var calendarCallRecentsArgs;

            beforeEach(function () {
              calendarCallRecentsArgs = Calendar.get.calls.mostRecent().args;
            });

            it('loads the work pattern calendars', function () {
              expect(Calendar.get).toHaveBeenCalled();
            });

            it('loads only the work pattern calendars of the currently loaded contacts', function () {
              expect(calendarCallRecentsArgs[0]).toEqual(_.map(controller.contacts, 'id'));
            });

            it("uses the selected months' first and last day as date delimiters", function () {
              var month = controller.month;

              expect(calendarCallRecentsArgs[1]).toBe(month.days[0].date);
              expect(calendarCallRecentsArgs[2]).toBe(month.days[month.days.length - 1].date);
            });
          });

          describe('leave requests', function () {
            var requestRecentCallFirstArg;

            beforeEach(function () {
              requestRecentCallFirstArg = LeaveRequest.all.calls.mostRecent().args[0];
            });

            it('loads the leave requests', function () {
              expect(LeaveRequest.all).toHaveBeenCalled();
            });

            it('loads only the approved, admin approved, awaiting approval or more information required leave requests', function () {
              expect(requestRecentCallFirstArg).toEqual(jasmine.objectContaining({
                status_id: {'IN': [
                  OptionGroupData.specificObject('hrleaveandabsences_leave_request_status', 'name', 'approved').value,
                  OptionGroupData.specificObject('hrleaveandabsences_leave_request_status', 'name', 'admin_approved').value,
                  OptionGroupData.specificObject('hrleaveandabsences_leave_request_status', 'name', 'awaiting_approval').value,
                  OptionGroupData.specificObject('hrleaveandabsences_leave_request_status', 'name', 'more_information_required').value
                ]}
              }));
            });

            it('loads leave requests for *enabled* absence types only', function () {
              expect(requestRecentCallFirstArg).toEqual(jasmine.objectContaining({
                type_id: { 'IN': _.map(controller.supportData.absenceTypes, 'id') }
              }));
            });

            it('loads only the leave requests belonging to the loaded contacts', function () {
              expect(requestRecentCallFirstArg).toEqual(jasmine.objectContaining({
                contact_id: { 'IN': _.map(controller.contacts, 'id') }
              }));
            });

            it('loads all requests touching the specified month', function () {
              var month = controller.month;

              expect(requestRecentCallFirstArg).toEqual(
                jasmine.objectContaining({
                  from_date: { to: month.days[month.days.length - 1].date + ' 23:59:59' },
                  to_date: { from: month.days[0].date + ' 00:00:00' }
                })
              );
            });

            describe('when leave requests are reloaded', function () {
              beforeEach(function () {
                $rootScope.$emit('LeaveCalendar::showMonth', true);
              });

              it('flushes days data before populating it', function () {
                expect(_.every(controller.month.days, function (day) {
                  return !Object.keys(day.contactsData).length;
                })).toBe(true);
              });
            });
          });

          describe('contacts', function () {
            describe('when there are contacts with inactive contracts', function () {
              var allContractsButAllExipredExceptFirst;

              beforeEach(function () {
                allContractsButAllExipredExceptFirst =
                  allContracts.map(function (contract, index) {
                    contract = _.cloneDeep(contract);

                    if (index === 0) {
                      return contract;
                    }

                    contract.info.details.period_start_date =
                    moment().year(february.year).month(february.month - 3)
                      .date(1).format(serverDateFormat);
                    contract.info.details.period_end_date =
                    moment().year(february.year).month(february.month - 2)
                      .date(1).format(serverDateFormat);

                    return contract;
                  });

                compileComponent(true, {
                  jobContracts: allContractsButAllExipredExceptFirst });
              });

              it('removes contacts with inactive contracts', function () {
                expect(controller.contacts.length).toEqual(1);
                expect(_.first(controller.contacts).id).toEqual(
                  _.first(allContractsButAllExipredExceptFirst).contact_id);
              });
            });

            describe('when there are contacts without contracts', function () {
              var onlyFirstContract;

              beforeEach(function () {
                onlyFirstContract = _.cloneDeep(_.first(allContracts));

                compileComponent(true, {
                  jobContracts: [onlyFirstContract] });
              });

              it('removes contacts without contracts', function () {
                expect(controller.contacts.length).toEqual(1);
                expect(_.first(controller.contacts).id).toEqual(onlyFirstContract.contact_id);
              });
            });
          });

          describe('current page', function () {
            beforeEach(function () {
              controller.currentPage = 5;

              sendShowMonthSignal();
            });

            it('resets it to 0', function () {
              expect(controller.currentPage).toBe(0);
            });
          });

          describe('when the reload is not forced', function () {
            beforeEach(function () {
              Calendar.get.calls.reset();
              LeaveRequest.all.calls.reset();

              sendShowMonthSignal();
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

              sendShowMonthSignal(true, true);
            });

            it('fetches the data again', function () {
              expect(Calendar.get).toHaveBeenCalled();
              expect(LeaveRequest.all).toHaveBeenCalled();
            });
          });

          describe('filter by absence types', function () {
            var filterValue = ['777', '888'];

            beforeEach(function () {
              controller.supportData.absenceTypesToFilterBy = filterValue;

              $rootScope.$emit('LeaveCalendar::showMonth', true);
              $rootScope.$digest();
            });

            it('loads leave requests for only selected absence types', function () {
              expect(LeaveRequest.all).toHaveBeenCalledWith(jasmine.objectContaining({
                type_id: { 'IN': filterValue }
              }), null, null, null, false);
            });

            describe('displaying only public leave requests', function () {
              var privateLeaveRequests;
              var contactId = _.uniqueId();

              beforeEach(function () {
                var leaveRequests = _.cloneDeep(LeaveRequestData.all().values);

                leaveRequests.slice(0, 3).forEach(function (leaveRequest) {
                  leaveRequest.contact_id = contactId;
                  leaveRequest.type_id = '';

                  return leaveRequest;
                });

                LeaveRequest.all.and.returnValue($q.resolve({
                  count: leaveRequests.length,
                  list: leaveRequests
                }));

                $rootScope.$emit('LeaveCalendar::updateFiltersByAbsenceType', filterValue);
                $rootScope.$digest();

                // Gets private requests assigned to the contact and stored in
                // the calendar month controller:
                privateLeaveRequests = _.chain(controller.month.days)
                  .map('contactsData')
                  .map(contactId)
                  .map('leaveRequests')
                  .flatten()
                  .filter(function (leaveRequest) {
                    return leaveRequest.type_id === '';
                  }).value();
              });

              it('is does not store information about private requests', function () {
                expect(privateLeaveRequests.length).toBe(0);
              });
            });
          });
        });

        describe('when *to* time is less than *from* time for multiple leave request', function () {
          var originalLeaveRequestInFebruary;

          beforeEach(function () {
            originalLeaveRequestInFebruary = _.cloneDeep(leaveRequestInFebruary);

            leaveRequestInFebruary.from_date =
              moment(leaveRequestInFebruary.from_date).hour(11).minute(0)
                .format(serverDateFormat + ' HH:mm');
            leaveRequestInFebruary.to_date =
              moment(leaveRequestInFebruary.to_date).hour(9).minute(0)
                .format(serverDateFormat + ' HH:mm');
            sendShowMonthSignal();
          });

          afterEach(function () {
            leaveRequestInFebruary = originalLeaveRequestInFebruary;
          });

          it('includes the leave request in the celandar day cell for the *to* date', function () {
            expect(
              _.find(controller.month.days,
                { index: moment(leaveRequestInFebruary.to_date).day().toString() })
                .contactsData[leaveRequestInFebruary.contact_id]
                .leaveRequests.length).toBe(1);
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

          /**
           * Mocks current day
           *
           * @param {String} date
           */
          function setCurrentDay (date) {
            jasmine.clock().mockDate(date);
            compileComponent();
          }
        });
      });

      describe("day's data specific for each contact", function () {
        beforeEach(function () {
          sendShowMonthSignal();
        });

        it('is indexed by contact id', function () {
          var indexes = Object.keys(getDayWithType('working_day').contactsData);

          expect(indexes).toEqual(_.map(controller.contacts, 'id'));
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

        describe('when the contact has multiple leave requests on the same day', function () {
          var leaveRequest1, leaveRequest2;

          describe('basic tests', function () {
            beforeEach(function () {
              leaveRequest1 = _.clone(LeaveRequestData.singleDataSuccess().values[0]);
              leaveRequest2 = _.clone(LeaveRequestData.singleDataSuccess().values[0]);

              commonSetup([leaveRequest1, leaveRequest2]);
            });

            it('has leave requests references in the contact data', function () {
              expect(contactData.leaveRequests.length).toBe(2);
              expect(contactData.leaveRequests[0]).toBe(leaveRequest1);
              expect(contactData.leaveRequests[1]).toBe(leaveRequest2);
            });

            it('keeps references to leave requests to show in day cell', function () {
              expect(contactData.leaveRequestsToShowInCell.length).toBe(2);
              expect(contactData.leaveRequestsToShowInCell[0]).toBe(leaveRequest1);
              expect(contactData.leaveRequestsToShowInCell[1]).toBe(leaveRequest2);
            });
          });

          describe('when day contains unsorted requests in hours', function () {
            beforeEach(function () {
              leaveRequest1 = _.clone(LeaveRequestData.singleDataSuccess().values[0]);
              leaveRequest1.from_date = leaveRequest1.from_date.split(' ')[0] + ' 23:00';
              leaveRequest2 = _.clone(LeaveRequestData.singleDataSuccess().values[0]);
              leaveRequest2.from_date = leaveRequest2.from_date.split(' ')[0] + ' 11:00';

              commonSetup([leaveRequest1, leaveRequest2]);
            });

            it('sorts leave requests', function () {
              expect(contactData.leaveRequests[0]).toBe(leaveRequest2);
              expect(contactData.leaveRequests[1]).toBe(leaveRequest1);
            });
          });

          describe('when day contains unsorted requests in days', function () {
            beforeEach(function () {
              leaveRequest1 = _.clone(LeaveRequestData.singleDataSuccess().values[0]);
              leaveRequest1.from_date_type = getDayTypeId('half_day_pm');
              leaveRequest2 = _.clone(LeaveRequestData.singleDataSuccess().values[0]);
              leaveRequest2.from_date_type = getDayTypeId('half_day_am');

              commonSetup([leaveRequest1, leaveRequest2]);
            });

            it('sorts leave requests', function () {
              expect(contactData.leaveRequests[0]).toBe(leaveRequest2);
              expect(contactData.leaveRequests[1]).toBe(leaveRequest1);
            });
          });

          describe('when day contains a mix of TOIL and non-TOIL requests', function () {
            beforeEach(function () {
              leaveRequest1 = _.clone(LeaveRequestData.singleDataSuccess().values[0]);
              leaveRequest2 = _.clone(LeaveRequestData.singleDataSuccess().values[0]);
              leaveRequest2.request_type = 'toil';

              commonSetup([leaveRequest1, leaveRequest2]);
            });

            it('only shows toil requests in the day cell', function () {
              expect(contactData.leaveRequestsToShowInCell.length).toBe(1);
              expect(contactData.leaveRequestsToShowInCell[0]).toBe(leaveRequest2);
            });

            it('still has all leave request references', function () {
              expect(contactData.leaveRequests.length).toBe(2);
            });
          });
        });

        describe('when the contact has recorded a leave request on the day', function () {
          var workPattern;

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

              commonSetup();
            });

            it('contains a reference to the leave request itself', function () {
              expect(contactData.leaveRequests[0]).toBe(leaveRequest);
            });

            it('adds a reference to the leave request to show it in the day cell', function () {
              expect(contactData.leaveRequestsToShowInCell[0]).toBe(leaveRequest);
            });

            it('assigns it the colors of its absence type', function () {
              var absenceTypeColor = _.find(controller.supportData.absenceTypes, function (absenceType) {
                return absenceType.id === leaveRequest.type_id;
              }).color;

              expect(leaveRequestAttributes.styles).toEqual({
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

              commonSetup();
            });

            it('marks it as such', function () {
              expect(leaveRequestAttributes.isRequested).toBe(true);
            });
          });

          describe('when the leave request is for half day am', function () {
            beforeEach(function () {
              leaveRequest.from_date_type = getDayTypeId('half_day_am');

              commonSetup();
            });

            it('marks it as such', function () {
              expect(leaveRequestAttributes.isAM).toBe(true);
            });
          });

          describe('when leave request is for half day pm', function () {
            beforeEach(function () {
              leaveRequest.from_date_type = getDayTypeId('half_day_pm');

              commonSetup();
            });

            it('marks it as such', function () {
              expect(leaveRequestAttributes.isPM).toBe(true);
            });
          });

          describe('when the leave request is of toil type', function () {
            beforeEach(function () {
              leaveRequest.request_type = 'toil';

              commonSetup();
            });

            it('marks it as such', function () {
              expect(leaveRequestAttributes.isAccruedTOIL).toBe(true);
            });
          });

          describe('when the leave request is a single day request', function () {
            beforeEach(function () {
              leaveRequest.from_date = leaveRequest.to_date;

              commonSetup();
            });

            it('marks it as such', function () {
              expect(leaveRequestAttributes.isSingleDay).toBe(true);
            });
          });
        });

        /**
         * Returns a day by a given day type
         *
         * @param  {String} dayType
         * @param  {Boolean} returnContactData
         * @return {Object}
         */
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
          sendShowMonthSignal();
        });

        describe('when a leave request is deleted', function () {
          var leaveRequestToDelete;

          beforeEach(function () {
            leaveRequestToDelete = leaveRequestInFebruary;

            LeaveRequest.all.calls.reset();
          });

          describe('leave request delete event', function () {
            beforeEach(function () {
              pubSub.publish('LeaveRequest::delete', leaveRequestToDelete);
              $rootScope.$digest();
            });

            itHandlesLeaveRequestDeleteEvent();
          });

          describe('leave request status update event', function () {
            beforeEach(function () {
              var shrinkedLeaveRequest = _.cloneDeep(leaveRequestToDelete);
              // Srinking the leave in terms of dates to ensure later that all dates are flushed
              shrinkedLeaveRequest.to_date = shrinkedLeaveRequest.from_date;

              pubSub.publish('LeaveRequest::statusUpdate', {
                status: 'delete',
                leaveRequest: shrinkedLeaveRequest
              });
              $rootScope.$digest();
            });

            itHandlesLeaveRequestDeleteEvent();
          });

          /**
           * Checks that the leave request is deleted
           */
          function itHandlesLeaveRequestDeleteEvent () {
            it('does not re-fetch the leave requests from the backend', function () {
              expect(LeaveRequest.all).not.toHaveBeenCalled();
            });

            it('resets the properties of each day that the original leave request spans', function () {
              expect(getLeaveRequestDays(leaveRequestToDelete).every(isDayContactDataNull)).toBe(true);
            });
          }
        });

        describe('when a leave request is added', function () {
          var leaveRequestToAdd;

          beforeEach(function () {
            leaveRequestToAdd = _.clone(leaveRequestInFebruary);
            leaveRequestToAdd = modifyLeaveRequestData(leaveRequestToAdd, true);

            LeaveRequest.all.calls.reset();
            pubSub.publish('LeaveRequest::new', leaveRequestToAdd);
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
          var leaveRequestToUpdate;

          beforeEach(function () {
            leaveRequestToUpdate = _.clone(leaveRequestInFebruary);
            leaveRequestToUpdate = modifyLeaveRequestData(leaveRequestToUpdate);

            LeaveRequest.all.calls.reset();
          });

          describe('leave request edit event', function () {
            var updatedRequest;

            beforeEach(function () {
              pubSub.publish('LeaveRequest::edit', leaveRequestToUpdate);
              $rootScope.$digest();

              updatedRequest = _.find(controller.month.days, function (day) {
                return _.find(day.contactsData[leaveRequestToUpdate.contact_id].leaveRequests, function (leaveRequest) {
                  return leaveRequest === leaveRequestToUpdate;
                });
              });
            });

            it('updates the leaveRequest', function () {
              expect(updatedRequest).toBeDefined();
            });
          });

          describe('leave request status update event', function () {
            var updatedRequest;

            beforeEach(function () {
              pubSub.publish('LeaveRequest::statusUpdate', {
                status: 'cancel',
                leaveRequest: leaveRequestToUpdate
              });
              $rootScope.$digest();

              updatedRequest = _.find(controller.month.days, function (day) {
                return _.find(day.contactsData[leaveRequestToUpdate.contact_id].leaveRequests, function (leaveRequest) {
                  return leaveRequest === leaveRequestToUpdate;
                });
              });
            });

            it('updates the leaveRequest', function () {
              expect(updatedRequest).toBeDefined();
            });
          });
        });

        /**
         * Modifies leave request by settings different dates
         *
         * @param  {LeaveRequestInstance} leaveRequest
         * @param  {Boolean} modifyId if to modify id or not
         * @return {LeaveRequestInstance}
         */
        function modifyLeaveRequestData (leaveRequest, modifyId) {
          var modifiedLeaveRequest = _.assign({}, leaveRequest, {
            from_date: '2016-02-20',
            to_date: '2016-02-21',
            dates: [
              { 'id': '1', 'date': '2016-02-20' },
              { 'id': '2', 'date': '2016-02-21' }
            ]
          });

          if (modifyId) {
            modifiedLeaveRequest.id = '1';
          }

          return modifiedLeaveRequest;
        }

        /**
         * Returns the array of days for the leave request
         *
         * @param  {LeaveRequestInstance} leaveRequest
         * @return {Array}
         */
        function getLeaveRequestDays (leaveRequest) {
          var days = [];
          var pointerDate = moment(leaveRequest.from_date).clone();
          var toDate = moment(leaveRequest.to_date);

          while (pointerDate.isSameOrBefore(toDate)) {
            days.push(_.find(controller.month.days, function (day) {
              return day.date === pointerDate.format(serverDateFormat);
            }));

            pointerDate.add(1, 'day');
          }

          return days;
        }

        /**
         * Checks if day contact data is empty
         *
         * @param  {Object} day
         * @return {Boolean}
         */
        function isDayContactDataNull (day) {
          var leaveRequests = day.contactsData[currentContactId].leaveRequests;
          var leaveRequestsAttributes = day.contactsData[currentContactId].leaveRequestsAttributes;

          return !leaveRequests.length && !Object.keys(leaveRequestsAttributes).length;
        }
      });

      describe('contactsList()', function () {
        beforeEach(function () {
          sendShowMonthSignal();
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

        describe('when show-only-with-leave-requests is set to true, but specific contacts must be shown even if they have no leave requests', function () {
          var expectedContact;

          beforeEach(function () {
            expectedContact = { id: _.uniqueId() };
            controller.showOnlyWithLeaveRequests = true;
            controller.showTheseContacts = [expectedContact.id];

            controller.contacts.push(expectedContact);
          });

          it('returns a list including the contacts that have no leave requests', function () {
            expect(controller.contactsList()).toContain(expectedContact);
          });
        });

        describe('when show-only-with-leave-requests is set to true and there is no leave request for contacts', function () {
          beforeEach(function () {
            LeaveRequest.all.and.callFake(function () {
              return $q.resolve({ list: [] });
            });

            compileComponent();

            controller.showOnlyWithLeaveRequests = true;
          });

          it('shows "There are no staff members matching selected filters" message on UI', function () {
            expect(controller.contactsList().length).toEqual(0);
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

      describe('getContactUrl()', function () {
        var contactID = 1;
        var returnedURL;

        beforeEach(function () {
          returnedURL = controller.getContactUrl(contactID);
        });

        it('returns URL for the contacts profile page', function () {
          expect(returnedURL).toBe('/index.php?q=civicrm/contact/view&cid=' + contactID);
        });
      });

      /**
       * Initializes controller with default or given leave requests
       * and caches day, contact data and leave request attributes
       * for the first leave request in the list for testing convenience
       *
       * @param {Array} leaveRequests - optional array of {LeaveRequestInstance}
       */
      function commonSetup (leaveRequests) {
        var day;
        var leaveRequestsToUse = leaveRequests || [leaveRequest];

        LeaveRequest.all.and.callFake(function () {
          return $q.resolve({ list: leaveRequestsToUse });
        });

        compileComponent(true);

        controller.month.days.forEach(function (dayObj) {
          if (moment(dayObj.date).isSame(leaveRequestsToUse[0].from_date, 'day')) {
            day = dayObj;
          }
        });

        contactData = day.contactsData[currentContactId];
        leaveRequestAttributes = contactData.leaveRequestsAttributes[leaveRequestsToUse[0].id];
      }

      /**
       * Compiles component
       *
       * @param {Boolean} sendSignal if to send a month signal or not
       * @param {Object} [params] bindings to override
       */
      function compileComponent (sendSignal, params) {
        var absenceTypes = _.clone(AbsenceTypeData.all().values);

        // append generic absence type:
        absenceTypes.push({
          id: '',
          label: 'Leave'
        });

        controller = $componentController('leaveCalendarMonth', null, _.assign({
          contacts: _.clone(ContactData.all.values),
          jobContracts: _.clone(allContracts),
          month: february,
          period: period2016,
          supportData: {
            absenceTypes: absenceTypes,
            absenceTypesToFilterBy: [],
            dayTypes: OptionGroupData.getCollection('hrleaveandabsences_leave_request_day_type'),
            leaveRequestStatuses: OptionGroupData.getCollection('hrleaveandabsences_leave_request_status'),
            publicHolidays: publicHolidays
          }
        }, params));
        controller.$onInit();

        !!sendSignal && sendShowMonthSignal();
      }

      /**
       * Gets day type ID by its name
       *
       * @param  {String} dayTypeName
       * @return {String}
       */
      function getDayTypeId (dayTypeName) {
        return _.find(OptionGroupData.getCollection(
          'hrleaveandabsences_leave_request_day_type'), function (dayType) {
          return dayType.name === dayTypeName;
        }).value;
      }

      /**
       * Creates active job contracts for each contact
       * by ensuring that contracts' dates will cover any reasonable use-case.
       *
       * @param {Function} done callback
       */
      function prepareJobContracts (done) {
        Contract.all()
          .then(function (contracts) {
            allContracts = ContactData.all.values.map(function (contact) {
              var contract = _.clone(_.sample(contracts));

              contract.id = _.uniqueId();
              contract.info.details.period_start_date =
                moment().year(february.year).month(february.month)
                  .date(1).format(serverDateFormat);
              contract.info.details.period_end_date =
                moment().year(february.year).month(february.month + 1)
                  .date(1).format(serverDateFormat);
              contract.contact_id = contact.id;

              return contract;
            });
          })
          .finally(done);

        $rootScope.$digest();
      }

      /**
       * Sends the "show months" signal to the component
       *
       * @param {Boolean} forceReload Whether to force reloading the month's data
       */
      function sendShowMonthSignal (forceReload) {
        forceReload = typeof forceReload === 'undefined' ? false : !!forceReload;

        $rootScope.$emit('LeaveCalendar::showMonth', forceReload);
        $rootScope.$digest();
      }
    });
  });
}(CRM));
