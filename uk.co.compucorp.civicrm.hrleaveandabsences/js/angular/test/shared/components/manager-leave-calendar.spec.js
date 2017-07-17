/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angular',
    'common/lodash',
    'common/moment',
    'mocks/helpers/helper',
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
  ], function (angular, _, moment, helper, absencePeriodData, optionGroupMock, leaveRequestData, publicHolidayData, workPatternMocked) {
    'use strict';

    describe('managerLeaveCalendar', function () {
      var $componentController, $q, $log, $rootScope, controller, $provide,
        OptionGroup, OptionGroupAPIMock, ContactAPIMock, AbsencePeriod, Contact;

      beforeEach(module('leave-absences.templates', 'leave-absences.mocks', 'manager-leave', function (_$provide_) {
        $provide = _$provide_;
      }));

      beforeEach(inject(function (AbsenceTypeAPIMock, LeaveRequestAPIMock,
        PublicHolidayAPIMock, WorkPatternAPIMock) {
        $provide.value('AbsenceTypeAPI', AbsenceTypeAPIMock);
        $provide.value('LeaveRequestAPI', LeaveRequestAPIMock);
        $provide.value('PublicHolidayAPI', PublicHolidayAPIMock);
        $provide.value('WorkPatternAPI', WorkPatternAPIMock);
        $provide.value('checkPermissions', function () { return $q.resolve(false); });
      }));

      beforeEach(inject(['api.contact.mock', function (_ContactAPIMock_) {
        ContactAPIMock = _ContactAPIMock_;
      }]));

      beforeEach(inject(function (
        _$componentController_, _$q_, _$log_, _$rootScope_,
        _OptionGroup_, _OptionGroupAPIMock_, _AbsencePeriod_, _Contact_,
        ContactInstance) {
        $componentController = _$componentController_;
        $q = _$q_;
        $log = _$log_;
        $rootScope = _$rootScope_;
        AbsencePeriod = _AbsencePeriod_;
        Contact = _Contact_;
        OptionGroup = _OptionGroup_;
        OptionGroupAPIMock = _OptionGroupAPIMock_;

        spyOn($log, 'debug');
        spyOn(Contact, 'all').and.returnValue($q.resolve(ContactAPIMock.mockedContacts()));
        spyOn(ContactInstance, 'leaveManagees').and.returnValue($q.resolve(ContactAPIMock.leaveManagees()));
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

        compileComponent();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      describe('on init', function () {
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

      function compileComponent () {
        controller = $componentController('managerLeaveCalendar', null, { contactId: CRM.vars.leaveAndAbsences.contactId });
        $rootScope.$digest();
      }
    });
  });
})(CRM);
