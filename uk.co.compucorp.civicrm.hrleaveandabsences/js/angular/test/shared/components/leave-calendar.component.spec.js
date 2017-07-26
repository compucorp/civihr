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
    'common/mocks/services/api/contact-mock',
    'mocks/apis/absence-period-api-mock',
    'mocks/apis/absence-type-api-mock',
    'mocks/apis/public-holiday-api-mock',
    'mocks/apis/option-group-api-mock',
    'leave-absences/shared/config',
    'leave-absences/my-leave/app'
  ], function (angular, moment, _, helper, absencePeriodData, absenceTypeData, optionGroupMock, publicHolidayData) {
    'use strict';

    describe('leaveCalendar', function () {
      var $componentController, $controller, $controllerProvider, $log, $q,
        $rootScope, $timeout, controller, $provide, AbsencePeriod, OptionGroup,
        PublicHoliday, sharedSettings;
      var mockedCheckPermissions = mockCheckPermissionService();
      var currentContact = {
        id: CRM.vars.leaveAndAbsences.contactId,
        role: 'staff'
      };

      beforeEach(module('common.mocks', 'leave-absences.templates', 'leave-absences.mocks', 'my-leave', function (_$provide_, _$controllerProvider_) {
        $provide = _$provide_;
        $controllerProvider = _$controllerProvider_;
      }));

      beforeEach(inject([
        'AbsencePeriodAPIMock', 'AbsenceTypeAPIMock', 'PublicHolidayAPIMock', 'api.contact.mock',
        function (AbsencePeriodAPIMock, AbsenceTypeAPIMock, PublicHolidayAPIMock, ContactAPIMock) {
          $provide.value('AbsencePeriodAPI', AbsencePeriodAPIMock);
          $provide.value('AbsenceTypeAPI', AbsenceTypeAPIMock);
          $provide.value('PublicHolidayAPI', PublicHolidayAPIMock);
          $provide.value('checkPermissions', mockedCheckPermissions);
          $provide.value('api.contact', ContactAPIMock);
        }
      ]));

      beforeEach(inject([
        '$componentController', '$controller', '$log', '$q', '$rootScope', '$timeout',
        'AbsencePeriod', 'OptionGroup', 'PublicHoliday', 'shared-settings', 'OptionGroupAPIMock',
        function (_$componentController_, _$controller_, _$log_, _$q_, _$rootScope_,
          _$timeout_, _AbsencePeriod_, _OptionGroup_, _PublicHoliday_, _sharedSettings_,
          OptionGroupAPIMock) {
          $componentController = _$componentController_;
          $controller = _$controller_;
          $log = _$log_;
          $q = _$q_;
          $rootScope = _$rootScope_;
          $timeout = _$timeout_;
          AbsencePeriod = _AbsencePeriod_;
          PublicHoliday = _PublicHoliday_;
          OptionGroup = _OptionGroup_;
          sharedSettings = _sharedSettings_;

          spyOn($log, 'debug');
          spyOn($rootScope, '$emit').and.callThrough();
          spyOn(AbsencePeriod, 'all');
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
        jasmine.clock().mockDate(new Date(2016, 1, 1));
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

        it('loads the OptionValues of the leave request statuses and day types', function () {
          expect(OptionGroup.valuesOf).toHaveBeenCalledWith([
            'hrleaveandabsences_leave_request_status',
            'hrleaveandabsences_leave_request_day_type'
          ]);
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
        });

        describe('months', function () {
          it('creates a list of the months of the selected period', function () {
            var months = controller.months;
            var periodStartDate = moment(controller.selectedPeriod.start_date);
            var periodEndDate = moment(controller.selectedPeriod.end_date);

            expect(months[0].index).toEqual(periodStartDate.month());
            expect(months[0].year).toEqual(periodStartDate.year());
            expect(months[months.length - 1].index).toEqual(periodEndDate.month());
            expect(months[months.length - 1].year).toEqual(periodEndDate.year());
          });

          it('selects the current month', function () {
            expect(controller.selectedMonths).toEqual([moment().month()]);
          });
        });

        describe('absence types', function () {
          var AbsenceType;

          beforeEach(inject(function (_AbsenceType_) {
            AbsenceType = _AbsenceType_;
            spyOn(AbsenceType, 'all').and.callThrough();

            compileComponent();
          }));

          it('loads the absence types', function () {
            expect(controller.supportData.absenceTypes.length).not.toBe(0);
          });

          it('excludes the inactive absence types', function () {
            expect(AbsenceType.all).toHaveBeenCalledWith({
              is_active: true
            });
          });
        });

        describe('filter option values', function () {
          var optionGroups = ['hrjc_region', 'hrjc_location', 'hrjc_level_type', 'hrjc_department'];

          describe('when the filters should not be shown', function () {
            it('does not fetch the filters option values', function () {
              expect(OptionGroup.valuesOf).not.toHaveBeenCalledWith(optionGroups);
            });
          });

          describe('when the filters should be shown', function () {
            beforeEach(function () {
              compileComponent(true);
              controller.showFilters = true;
              digest();
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

          describe('when it has not yet received the "month injected" event from all the months', function () {
            beforeEach(function () {
              simulateMonthsInjected(2);
            });

            it('does not send the event', function () {
              expect($rootScope.$emit).not.toHaveBeenCalled();
            });
          });

          describe('when it has received the "month injected" event from all the months', function () {
            beforeEach(function () {
              simulateMonthsInjected(controller.months.length);
            });

            it('sends the event', function () {
              expect($rootScope.$emit).toHaveBeenCalled();
              expect($rootScope.$emit.calls.mostRecent().args[0]).toBe('LeaveCalendar::showMonths');
            });

            it('attaches to the event only the currently selected months', function () {
              expect($rootScope.$emit.calls.mostRecent().args[1]).toEqual(controller.months.filter(function (month) {
                return _.includes(controller.selectedMonths, month.index);
              }));
            });
          });
        });
      });

      describe('selected months watcher', function () {
        describe('when some other months are selected', function () {
          beforeEach(function () {
            controller.selectedMonths = [1, 2, 3];
            digest(true);
          });

          it('sends the "show months" event with the newly selected months', function () {
            expect($rootScope.$emit).toHaveBeenCalledWith(
              'LeaveCalendar::showMonths',
              controller.months.filter(function (month) {
                return _.includes([1, 2, 3], month.index);
              }),
              jasmine.any(Boolean)
            );
          });
        });

        describe('when none of the months are selected', function () {
          beforeEach(function () {
            controller.selectedMonths = [];
            digest(true);
          });

          it('sends the "show months" event with the all the months', function () {
            expect($rootScope.$emit).toHaveBeenCalledWith(
              'LeaveCalendar::showMonths',
              controller.months,
              jasmine.any(Boolean));
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
              digest(true);

              simulateMonthsInjected(controller.months.length);
            });

            it('rebuilds the months structure', function () {
              expect(controller.months).not.toBe(oldMonths);
            });

            it('does not reloads the contacts', function () {
              expect(spyLoadContacts).not.toHaveBeenCalled();
            });

            it('sends the "show months" signal without forcing data reload', function () {
              expect($rootScope.$emit).toHaveBeenCalledWith(
                'LeaveCalendar::showMonths',
                jasmine.any(Array),
                false
              );
            });
          });

          describe('when the source of the refresh is a contact filters change', function () {
            beforeEach(function () {
              controller.refresh('contacts');
              digest(true);

              simulateMonthsInjected(controller.months.length);
            });

            it('does not rebuild the months structure', function () {
              expect(controller.months).toBe(oldMonths);
            });

            it('reloads the contacts', function () {
              expect(spyLoadContacts).toHaveBeenCalled();
            });

            it('sends the "show months" signal with forcing data reload', function () {
              expect($rootScope.$emit).toHaveBeenCalledWith(
                'LeaveCalendar::showMonths',
                jasmine.any(Array),
                true
              );
            });
          });
        });
      });

      function amend2016Period (params) {
        AbsencePeriod.all.and.callFake(function () {
          var absencePeriods = _.clone(absencePeriodData.all().values);
          _.assign(absencePeriods[0], params);

          return $q.resolve(absencePeriods);
        });
      }

      function compileComponent (skipDigest, bindings) {
        controller = $componentController('leaveCalendar', null, _.assign({ contactId: currentContact.id }, bindings));
        !skipDigest && digest();
      }

      function digest (skipFlush) {
        $rootScope.$digest();
        !skipFlush && $timeout.flush();
      }

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

      function simulateMonthsInjected (numberOfMonths) {
        _.times(numberOfMonths, function () {
          $rootScope.$emit('LeaveCalendar::monthInjected');
        });

        $rootScope.$emit.calls.reset();
        digest(true);
      }

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
