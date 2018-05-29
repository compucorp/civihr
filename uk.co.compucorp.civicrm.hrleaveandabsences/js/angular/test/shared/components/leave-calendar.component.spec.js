/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angular',
    'common/moment',
    'common/lodash',
    'leave-absences/mocks/helpers/helper',
    'leave-absences/mocks/data/absence-period.data',
    'leave-absences/mocks/data/absence-type.data',
    'leave-absences/mocks/data/option-group.data',
    'leave-absences/mocks/data/public-holiday.data',
    'common/mocks/services/api/contact-mock',
    'common/mocks/services/api/contract-mock',
    'leave-absences/mocks/apis/absence-period-api-mock',
    'leave-absences/mocks/apis/absence-type-api-mock',
    'leave-absences/mocks/apis/public-holiday-api-mock',
    'leave-absences/mocks/apis/option-group-api-mock',
    'leave-absences/shared/config',
    'leave-absences/my-leave/app'
  ], function (angular, moment, _, helper, absencePeriodData, absenceTypeData, optionGroupMock, publicHolidayData) {
    'use strict';

    describe('leaveCalendar', function () {
      var $componentController, $controller, $controllerProvider, $log, $q,
        $rootScope, controller, $provide, AbsencePeriod, Contact, ContactAPIMock, Contract, OptionGroup,
        PublicHoliday, sharedSettings, notification;
      var mockedCheckPermissions = mockCheckPermissionService();
      var currentContact = {
        id: CRM.vars.leaveAndAbsences.contactId,
        role: 'staff'
      };
      var currentYear = 2016;
      var currentMonth = 1;

      beforeEach(module('common.mocks', 'leave-absences.templates', 'leave-absences.mocks', 'my-leave', function (_$provide_, _$controllerProvider_) {
        $provide = _$provide_;
        $controllerProvider = _$controllerProvider_;
      }));

      beforeEach(inject([
        'AbsencePeriodAPIMock', 'AbsenceTypeAPIMock', 'PublicHolidayAPIMock', 'api.contact.mock', 'api.contract.mock',
        function (_AbsencePeriodAPIMock_, _AbsenceTypeAPIMock_, _PublicHolidayAPIMock_, _ContactAPIMock_, _ContractAPIMock_) {
          ContactAPIMock = _ContactAPIMock_;

          $provide.value('AbsencePeriodAPI', _AbsencePeriodAPIMock_);
          $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
          $provide.value('PublicHolidayAPI', _PublicHolidayAPIMock_);
          $provide.value('api.contact', _ContactAPIMock_);
          $provide.value('api.contract', _ContractAPIMock_);
          $provide.value('checkPermissions', mockedCheckPermissions);
        }
      ]));

      beforeEach(inject([
        '$componentController', '$controller', '$log', '$q', '$rootScope',
        'AbsencePeriod', 'Contact', 'Contract', 'OptionGroup', 'PublicHoliday',
        'shared-settings', 'notificationService', 'OptionGroupAPIMock',
        function (_$componentController_, _$controller_, _$log_, _$q_, _$rootScope_,
          _AbsencePeriod_, _Contact_, _Contract_, _OptionGroup_, _PublicHoliday_,
          _sharedSettings_, _notificationService_, OptionGroupAPIMock) {
          $componentController = _$componentController_;
          $controller = _$controller_;
          $log = _$log_;
          $q = _$q_;
          $rootScope = _$rootScope_;
          AbsencePeriod = _AbsencePeriod_;
          Contact = _Contact_;
          Contract = _Contract_;
          PublicHoliday = _PublicHoliday_;
          OptionGroup = _OptionGroup_;
          sharedSettings = _sharedSettings_;
          notification = _notificationService_;

          spyOn(window, 'alert');
          spyOn($log, 'debug');
          spyOn($rootScope, '$emit').and.callThrough();
          spyOn(AbsencePeriod, 'all');
          spyOn(Contact, 'all').and.callThrough();
          spyOn(Contact, 'leaveManagees').and.callFake(function () {
            return ContactAPIMock.leaveManagees();
          });
          spyOn(Contract, 'all').and.callThrough();
          spyOn(PublicHoliday, 'all').and.callThrough();
          spyOn(OptionGroup, 'valuesOf').and.callFake(function (name) {
            return OptionGroupAPIMock.valuesOf(name);
          });

          // Set 2016 as current period, because Calendar loads data only for the current period initially,
          // and MockedData has 2016 dates
          amend2016Period({ current: true });

          compileComponent();
        }
      ]));

      // The mocked "work pattern calendar" and "leave request" data is made for
      // the month of February, so we pretend we are in February
      beforeAll(function () {
        jasmine.clock().mockDate(new Date(currentYear, currentMonth, 1));
      });

      afterAll(function () {
        jasmine.clock().uninstall();
      });

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      describe('on init', function () {
        it('hides the loader for the whole page', function () {
          expect(controller.loading.page).toBe(false);
        });

        it('loads the public holidays', function () {
          expect(PublicHoliday.all).toHaveBeenCalled();
        });

        it('loads the OptionValues of the leave request statuses, day types, and calculation units', function () {
          expect(OptionGroup.valuesOf).toHaveBeenCalledWith([
            'hrleaveandabsences_absence_type_calculation_unit',
            'hrleaveandabsences_leave_request_day_type',
            'hrleaveandabsences_leave_request_status',
            'hrleaveandabsences_toil_amounts'
          ]);
        });

        describe('after loading support data', function () {
          it('stores absence types', function () {
            expect(controller.supportData.absenceTypes.length).not.toBe(0);
          });

          it('stores calculation units', function () {
            expect(controller.supportData.calculationUnits.length).not.toBe(0);
          });

          it('stores day types', function () {
            expect(controller.supportData.dayTypes.length).not.toBe(0);
          });

          it('stores public holidays', function () {
            expect(controller.supportData.publicHolidays.length).not.toBe(0);
          });

          it('stores leave request statuses', function () {
            expect(controller.supportData.leaveRequestStatuses.length).not.toBe(0);
          });

          it('stores toil amounts', function () {
            expect(controller.supportData.toilAmounts).toBeDefined();
          });
        });

        describe('taking leave filter', function () {
          beforeEach(function () {
            // skip digest to see the default filter settings
            // before child controller is invoked
            compileComponent(true);
          });

          it('sets the filter to *on* by default', function () {
            expect(controller.filters.userSettings.contacts_with_leaves).toBe(true);
          });

          describe('when role is staff', function () {
            beforeEach(function () {
              compileComponent();
            });

            it('sets the filter to *on* by default', function () {
              expect(controller.filters.userSettings.contacts_with_leaves).toBe(true);
            });
          });
        });

        describe('permissions', function () {
          it('checks if the user has permissions to manage L&A', function () {
            expect(mockedCheckPermissions).toHaveBeenCalledWith(sharedSettings.permissions.ssp.manage);
          });

          it('checks if the user has permissions to administer L&A', function () {
            expect(mockedCheckPermissions).toHaveBeenCalledWith(sharedSettings.permissions.admin.administer);
          });
        });

        describe('sub-controller', function () {
          describe('when the user is a staff', function () {
            it('injects the staff calendar sub-controller', function () {
              expect($log.debug).toHaveBeenCalledWith('LeaveCalendarStaffController');
            });
          });

          describe('when the user is a manager', function () {
            beforeEach(function () {
              currentContact.role = 'manager';
              compileComponent();
            });

            it('injects the manager calendar sub-controller', function () {
              expect($log.debug).toHaveBeenCalledWith('LeaveCalendarManagerController');
            });
          });

          describe('when the user is an admin', function () {
            beforeEach(function () {
              currentContact.role = 'admin';
              compileComponent();
            });

            it('injects the admin calendar sub-controller', function () {
              expect($log.debug).toHaveBeenCalledWith('LeaveCalendarAdminController');
            });
          });

          describe('when the role-override binding is used', function () {
            beforeEach(function () {
              currentContact.role = 'admin';
              compileComponent(false, { roleOverride: 'staff' });
            });

            it('ignores the real user\'s role and uses the one given in the binding instead', function () {
              expect($log.debug).not.toHaveBeenCalledWith('LeaveCalendarAdminController');
              expect($log.debug).toHaveBeenCalledWith('LeaveCalendarStaffController');
            });
          });

          afterEach(function () {
            currentContact.role = 'staff';
          });
        });

        describe('contacts', function () {
          var spyLoadContacts;

          beforeEach(function () {
            spyLoadContacts = spyOnSubCtrlLoadContacts();
            compileComponent();
          });

          it('gets the list of contacts from the currently injected sub-controller', function () {
            expect(spyLoadContacts).toHaveBeenCalled();
          });

          it('loads the contacts to display on the calendar', function () {
            expect(controller.contacts.length).not.toBe(0);
          });
        });

        describe('month paginators', function () {
          describe('when current month is the first month of the current absence period', function () {
            beforeEach(function () {
              controller.selectedMonthIndex = _.first(controller.months).index;

              $rootScope.$digest();
            });

            it('does not allow to paginate to the previous month', function () {
              expect(controller.monthPaginatorsAvailability.previous).toBe(false);
            });
          });

          describe('when current month is the last month of the current absence period', function () {
            beforeEach(function () {
              controller.selectedMonthIndex = _.last(controller.months).index;

              $rootScope.$digest();
            });

            it('does not allow to paginate to the previous month', function () {
              expect(controller.monthPaginatorsAvailability.next).toBe(false);
            });
          });

          describe('when current month is neither the first nor the last month of the current absence period', function () {
            beforeEach(function () {
              controller.selectedMonthIndex = controller.months[1].index;

              $rootScope.$digest();
            });

            it('allows to paginate the month in both directions', function () {
              expect(controller.monthPaginatorsAvailability.previous).toBe(true);
              expect(controller.monthPaginatorsAvailability.next).toBe(true);
            });
          });
        });

        describe('additional contacts filter', function () {
          describe('when the user is an admin', function () {
            beforeEach(function () {
              currentContact.role = 'admin';

              compileComponent();
            });

            describe('when filter by assignee is set to "Me"', function () {
              beforeEach(function () {
                selectFilterByAssignee('me');
              });

              it('does *not* load additional contacts IDs to filter', function () {
                expect(controller.contactIdsToReduceTo).toEqual(null);
              });
            });

            describe('when filter by assignee is *not* set to "Me"', function () {
              beforeEach(function () {
                selectFilterByAssignee('all');
              });

              it('loads additional contacts IDs to filter', function () {
                expect(controller.contactIdsToReduceTo).toEqual(jasmine.any(Array));
              });
            });
          });

          describe('when the user is a manager', function () {
            beforeEach(function () {
              currentContact.role = 'manager';

              compileComponent();
            });

            it('does not load additional contacts IDs to filter', function () {
              expect(controller.contactIdsToReduceTo).toBe(null);
            });
          });

          afterEach(function () {
            currentContact.role = 'staff';
          });
        });

        describe('filter by assignee', function () {
          it('does *not* have such a filter for staff', function () {
            expect(controller.filtersByAssignee).not.toBeDefined();
          });

          describe('when user is Admin', function () {
            beforeEach(function () {
              currentContact.role = 'admin';

              compileComponent();
            });

            it('has the filter available', function () {
              expect(controller.filtersByAssignee).toBeDefined();
            });

            describe('when filter by assignee is set to "Me"', function () {
              beforeEach(function () {
                Contract.all.calls.reset();
                Contact.all.calls.reset();
                selectFilterByAssignee('me');
              });

              it('does *not* load contracts', function () {
                expect(Contract.all).not.toHaveBeenCalledWith();
              });

              it('loads only managees', function () {
                expect(Contact.all).not.toHaveBeenCalledWith();
                expect(Contact.leaveManagees).toHaveBeenCalledWith(currentContact.id);
              });
            });

            describe('when filter by assignee is set to "Unassigned"', function () {
              beforeEach(function () {
                selectFilterByAssignee('unassigned');
              });

              it('loads all contracts', function () {
                expect(Contract.all).toHaveBeenCalledWith();
              });

              it('loads all contacts', function () {
                expect(Contact.leaveManagees).toHaveBeenCalledWith(undefined, {
                  unassigned: true
                });
              });
            });

            describe('when filter by assignee is set to "All"', function () {
              beforeEach(function () {
                selectFilterByAssignee('all');
              });

              it('loads all contracts', function () {
                expect(Contract.all).toHaveBeenCalledWith();
              });

              it('loads all contacts', function () {
                expect(Contact.all).toHaveBeenCalledWith();
              });
            });
          });

          afterEach(function () {
            currentContact.role = 'staff';
          });
        });

        describe('hint for admin filtering logic', function () {
          it('does *not* have such a hint for staff', function () {
            expect(controller.showAdminFilteringHint).not.toBeDefined();
          });

          describe('when user is Admin', function () {
            beforeEach(function () {
              currentContact.role = 'admin';

              compileComponent();
            });

            it('shows the hint', function () {
              expect(controller.showAdminFilteringHint).toBeDefined();
            });

            describe('when user clicks the hint', function () {
              beforeEach(function () {
                spyOn(notification, 'info').and.callThrough();
                controller.showAdminFilteringHint();
              });

              it('shows the notification with a hint message', function () {
                expect(notification.info).toHaveBeenCalledWith(jasmine.any(String), jasmine.any(String));
              });
            });
          });

          afterEach(function () {
            currentContact.role = 'staff';
          });
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

          describe('when absence period has been changed', function () {
            beforeEach(function () {
              controller.injectMonth = false;
              controller.selectedPeriod = controller.absencePeriods[1];

              controller.refresh('period');
              $rootScope.$digest();
            });

            it('sets the first month from the period as the selected month', function () {
              expect(controller.selectedMonth).toEqual(controller.months[0]);
            });
          });
        });

        describe('months', function () {
          it('creates a list of the months of the selected period', function () {
            var months = controller.months;
            var periodStartDate = moment(controller.selectedPeriod.start_date);
            var periodEndDate = moment(controller.selectedPeriod.end_date);

            expect(months[0].month).toEqual(periodStartDate.month());
            expect(months[0].year).toEqual(periodStartDate.year());
            expect(months[months.length - 1].month).toEqual(periodEndDate.month());
            expect(months[months.length - 1].year).toEqual(periodEndDate.year());
          });

          it('sorts the list of the months', function () {
            var months = controller.months;
            var monthsSorted = _.sortBy(months, function (month) {
              return new Date(month.moment);
            });

            expect(months).toEqual(monthsSorted);
          });

          it('selects the current month', function () {
            expect(controller.selectedMonth).toEqual(_.find(controller.months,
              { index: moment().format('YYYY-MM') }));
          });
        });

        describe('absence types', function () {
          var AbsenceType, absenceTypeRecord;

          beforeEach(inject(function (_AbsenceType_) {
            AbsenceType = _AbsenceType_;
            absenceTypeRecord = controller.supportData.absenceTypes[0];

            spyOn(AbsenceType, 'all').and.callThrough();
            compileComponent();
          }));

          it('loads the absence types', function () {
            expect(controller.supportData.absenceTypes.length).not.toBe(0);
            expect(AbsenceType.all).toHaveBeenCalledWith();
          });

          it('loads the absence types calculation units', function () {
            expect(absenceTypeRecord.calculation_unit_name).toEqual(jasmine.any(String));
            expect(absenceTypeRecord.calculation_unit_label).toEqual(jasmine.any(String));
          });
        });

        describe('filter option values', function () {
          var optionGroups = ['hrjc_region', 'hrjc_location', 'hrjc_level_type', 'hrjc_department'];

          describe('when the subcontroller does not show filters', function () {
            beforeEach(function () {
              var staffController = $controller('LeaveCalendarStaffController').init(controller);

              // mocks the staff sub controller to hide the filters:
              $controllerProvider.register('LeaveCalendarStaffController', function () {
                return {
                  init: function (vm) {
                    vm.showFilters = false;

                    return staffController;
                  }
                };
              });

              OptionGroup.valuesOf.calls.reset();
              compileComponent();
            });

            it('does not fetch the filters option values', function () {
              expect(OptionGroup.valuesOf).not.toHaveBeenCalledWith(optionGroups);
            });
          });

          describe('when the filters should be shown', function () {
            beforeEach(function () {
              compileComponent(true);
              controller.showFilters = true;
              $rootScope.$digest();
            });

            it('fetches the filters option values', function () {
              expect(OptionGroup.valuesOf).toHaveBeenCalledWith(optionGroups);
            });
          });
        });

        describe('"show months" event', function () {
          beforeEach(function () {
            compileComponent();
            controller.injectMonths = true;
          });

          describe('when it has received the "month injected" event from the month', function () {
            beforeEach(function () {
              simulateMonthWithSignal('injected');
            });

            it('sends the event', function () {
              expect($rootScope.$emit.calls.mostRecent().args[0]).toBe('LeaveCalendar::showMonth');
            });
          });
        });

        /**
         * Selects a filter by assignee
         *
         * @param {String} type (me|unassigned|all)
         */
        function selectFilterByAssignee (type) {
          controller.filters.userSettings.assignedTo =
            _.find(controller.filtersByAssignee, { type: type });

          controller.refresh('contacts');
          $rootScope.$digest();

          simulateMonthWithSignal('destroyed', controller.months.length);
        }
      });

      describe('navigateToCurrentMonth()', function () {
        var currentAbsencePeriod;

        beforeEach(function () {
          currentAbsencePeriod = _.find(controller.absencePeriods,
            { current: true });
          controller.injectMonth = false;
          controller.selectedPeriod = controller.absencePeriods[1];
          $rootScope.$digest();
          controller.selectedMonthIndex =
            moment().year(currentYear).month(currentMonth).format('YYYY-MM');
          $rootScope.$digest();
          controller.navigateToCurrentMonth();
          $rootScope.$digest();
        });

        it('sets the selected month as the current month', function () {
          expect(controller.selectedMonth.year).toBe(currentYear);
          expect(controller.selectedMonth.month).toBe(currentMonth);
        });

        it('sets the current absence period', function () {
          expect(controller.selectedPeriod).toEqual(currentAbsencePeriod);
        });
      });

      describe('paginateMonth()', function () {
        var currentlySelectedMonth;
        var tests = [
          { direction: 'previous', monthDifference: -1 },
          { direction: 'next', monthDifference: 1 }
        ];

        beforeEach(function () {
          currentlySelectedMonth = controller.selectedMonth.month;
          // This is needed to test the paginators availability
          controller.months = _.slice(controller.months, 0, 3);
        });

        tests.forEach(function (test) {
          describe('when user paginates to the ' + test.direction + ' month', function () {
            beforeEach(function () {
              controller.injectMonth = false;
              controller.paginateMonth(test.direction);
              $rootScope.$digest();
              simulateMonthWithSignal('injected', controller.months.length);
              $rootScope.$digest();
            });

            it('sets the selected month as a ' + test.direction + ' month', function () {
              expect(controller.selectedMonth.moment
                .diff(currentlySelectedMonth.moment, 'month')).toBe(test.monthDifference);
            });

            it('does not allow to paginate further because the are no more months in that direction', function () {
              expect(controller.monthPaginatorsAvailability[test.direction]).toBe(false);
            });

            it('refreshes the month component without force data reload', function () {
              expect($rootScope.$emit).toHaveBeenCalledWith('LeaveCalendar::showMonth', false);
            });
          });
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
        describe('loading flag', function () {
          beforeEach(function () {
            controller.refresh();
          });

          it('does not mark the entire page as loading', function () {
            expect(controller.loading.page).not.toBe(true);
          });

          it('marks the calendar content as loading', function () {
            expect(controller.loading.calendar).toBe(true);
          });
        });

        describe('source of refresh', function () {
          var oldMonths, spyLoadContacts;

          beforeEach(function () {
            spyLoadContacts = spyOnSubCtrlLoadContacts();

            compileComponent();
            oldMonths = controller.months;

            spyLoadContacts.calls.reset();
            $rootScope.$emit.calls.reset();
          });

          describe('when the source of the refresh is a period change', function () {
            beforeEach(function () {
              controller.refresh('period');
              $rootScope.$digest();

              simulateMonthWithSignal('destroyed', controller.months.length);
              simulateMonthWithSignal('injected', controller.months.length);
            });

            it('rebuilds the months structure', function () {
              expect(controller.months).not.toBe(oldMonths);
            });

            it('does not reloads the contacts', function () {
              expect(spyLoadContacts).not.toHaveBeenCalled();
            });

            it('sends the "show months" signal without forcing data reload', function () {
              expect($rootScope.$emit).toHaveBeenCalledWith(
                'LeaveCalendar::showMonth', false);
            });
          });

          describe('when the source of the refresh is a contact filters change', function () {
            beforeEach(function () {
              controller.refresh('contacts');
              $rootScope.$digest();

              simulateMonthWithSignal('destroyed', controller.months.length);
              simulateMonthWithSignal('injected', controller.months.length);
            });

            it('does not rebuild the months structure', function () {
              expect(controller.months).toBe(oldMonths);
            });

            it('reloads the contacts', function () {
              expect(spyLoadContacts).toHaveBeenCalled();
            });

            it('sends the "show months" signal with forcing data reload', function () {
              expect($rootScope.$emit).toHaveBeenCalledWith(
                'LeaveCalendar::showMonth', true);
            });
          });
        });
      });

      /**
       * Amends the property of the 2016 period (the current one)
       *
       * @param  {Object} params
       */
      function amend2016Period (params) {
        AbsencePeriod.all.and.callFake(function () {
          var absencePeriods = _.clone(absencePeriodData.all().values);
          _.assign(absencePeriods[0], params);

          return $q.resolve(absencePeriods);
        });
      }

      function compileComponent (skipDigest, bindings) {
        controller = $componentController('leaveCalendar', null, _.assign({ contactId: currentContact.id }, bindings));
        !skipDigest && $rootScope.$digest();
      }

      /**
       * Spies on the `loadContacts()` method of the sub-controller that will
       * be injected in the component (it will change depending on the current role)
       *
       * @return {Function}
       */
      function spyOnSubCtrlLoadContacts () {
        var ctrlName = 'LeaveCalendar' + _.capitalize(currentContact.role) + 'Controller';
        var realSubCtrl = $controller(ctrlName).init(controller);
        var spy = jasmine.createSpy('loadContacts').and.callFake(function () {
          return realSubCtrl.loadContacts();
        });

        $controllerProvider.register(ctrlName, function () {
          return {
            init: function () {
              return { loadContacts: spy };
            }
          };
        });

        return spy;
      }

      /**
       * Simulates that the given number of months sends the given
       * signal to the component
       *
       * @param {String} signal
       */
      function simulateMonthWithSignal (signal) {
        $rootScope.$emit('LeaveCalendar::month' + _.capitalize(signal));

        $rootScope.$emit.calls.reset();
        $rootScope.$digest();
      }

      /**
       * Mocks the `checkPermission` service, returning a different response
       * based on the current role set in the tests
       *
       * @return {Promise} resolves to {Boolean}
       */
      function mockCheckPermissionService () {
        return jasmine.createSpy().and.callFake(function (permissionToCheck) {
          if (permissionToCheck === sharedSettings.permissions.ssp.manage) {
            return $q.resolve(currentContact.role === 'manager');
          }

          if (permissionToCheck === sharedSettings.permissions.admin.administer) {
            return $q.resolve(currentContact.role === 'admin');
          }
        });
      }
    });
  });
}(CRM));
