(function (CRM) {
  define([
    'common/angular',
    'mocks/data/option-group-mock-data',
    'mocks/data/leave-request-data',
    'leave-absences/shared/config',
    'leave-absences/manager-leave/app',
    'mocks/apis/absence-period-api-mock',
    'mocks/apis/absence-type-api-mock',
    'mocks/apis/leave-request-api-mock',
    'common/mocks/services/api/contact-mock'
  ], function (angular, optionGroupMock, leaveRequestData) {
    'use strict';

    describe('managerLeaveReport', function () {
      var $compile,
        $log,
        $q,
        $provide,
        $rootScope,
        component,
        controller,
        OptionGroup,
        AbsenceType,
        AbsencePeriod,
        LeaveRequest,
        Contact,
        ContactAPIMock;

      beforeEach(module('leave-absences.templates', 'manager-leave', 'leave-absences.mocks', function (_$provide_) {
        $provide = _$provide_;
      }));

      beforeEach(inject(function (AbsencePeriodAPIMock, AbsenceTypeAPIMock, LeaveRequestAPIMock) {
        $provide.value('AbsencePeriodAPI', AbsencePeriodAPIMock);
        $provide.value('AbsenceTypeAPI', AbsenceTypeAPIMock);
        $provide.value('LeaveRequestAPI', LeaveRequestAPIMock);
      }));

      beforeEach(inject(['api.contact.mock', function (_ContactAPIMock_) {
        ContactAPIMock = _ContactAPIMock_;
      }]));

      beforeEach(inject(function (_$compile_, _$log_, _$rootScope_, _$q_, _OptionGroup_, _AbsencePeriod_, _AbsenceType_, _LeaveRequest_, _Contact_) {
        $compile = _$compile_;
        $log = _$log_;
        $q = _$q_;
        $rootScope = _$rootScope_;
        OptionGroup = _OptionGroup_;
        AbsenceType = _AbsenceType_;
        AbsencePeriod = _AbsencePeriod_;
        LeaveRequest = _LeaveRequest_;
        Contact = _Contact_
      }));

      beforeEach(function () {
        spyOn($log, 'debug');

        spyOn(AbsencePeriod, 'all').and.callThrough();
        spyOn(AbsenceType, 'all').and.callThrough();

        spyOn(Contact, 'all').and.callFake(function () {
          return $q.resolve(ContactAPIMock.mockedContacts());
        });

        spyOn(OptionGroup, 'valuesOf').and.callFake(function (name) {
          return $q.resolve(optionGroupMock.getCollection(name));
        });
      });

      beforeEach(function () {
        compileComponent();
      });



      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      describe('initial data', function () {

        it('the filter section is closed', function () {
          expect(controller.isFilterExpanded).toBe(false);
        });

        it('the filters are reset', function () {
          expect(controller.filters).toEqual({
            contactFilters: {
              region: '',
              department: '',
              level_type: '',
              location: ''
            },
            leaveRequestFilters: {
              pending_requests: false
            }
          })
        });

        describe('pagination', function () {

          it('page is set to 1', function () {
            expect(controller.pagination.page).toBe(1);
          });

          it('page size is 7', function () {
            expect(controller.pagination.size).toBe(7);
          });
        });
      });

      describe('data loading', function () {

        //TODO need to figure out how to test variables which are changed when controller gets initialized
        xdescribe('before loading starts', function () {

          it('loading should be hidden', function () {

          });

          it('leave requests are empty', function () {

          });

          it('absencePeriods are empty', function () {

          });

          it('absenceTypes are empty', function () {

          });

          it('statusCount is reset', function () {

          });
        });

        describe('when data is loaded', function () {

          describe('loading', function () {

            it('page loader should be hidden', function () {
              expect(controller.loading.page).toBe(false);
            });

            it('controller loader should be hidden', function () {
              expect(controller.loading.content).toBe(false);
            });
          });

          it('leave requests status is loaded', function () {
            var expectedResult = optionGroupMock.getCollection('hrleaveandabsences_leave_request_status').concat({
              name: 'all',
              label: 'All'
            });
            expect(controller.leaveRequestStatuses).toEqual(expectedResult);
          });

          it('regions are loaded', function () {
            expect(controller.regions).toEqual(optionGroupMock.getCollection('hrjc_region'));
          });

          it('departments are loaded', function () {
            expect(controller.departments).toEqual(optionGroupMock.getCollection('hrjc_department'));
          });

          it('locations are loaded', function () {
            expect(controller.locations).toEqual(optionGroupMock.getCollection('hrjc_location'));
          });

          it('levelTypes are loaded', function () {
            expect(controller.levelTypes).toEqual(optionGroupMock.getCollection('hrjc_level_type'));
          });

          it('absencePeriods are loaded', function () {
            expect(controller.absencePeriods.length).not.toBe(0);
          });

          it('absenceTypes have data', function () {
            expect(controller.absenceTypes.length).not.toBe(0);
          });

          it('users have loaded', function () {
            expect(controller.filteredUsers).toEqual(ContactAPIMock.mockedContacts().list);
          });

          it('leaveRequests have loaded', function () {
            expect(controller.leaveRequests.list).toEqual(leaveRequestData.all().values);
          });
        });
      });

      describe('pagination', function () {
        //TODO create directive for this

        it('next button increases the page no', function () {

        });

        it('last button sets the page no the last', function () {

        });

        it('last button sets the page no the last', function () {

        });
      });

      describe('status type', function () {

        it('sets active status type', function () {

        });
      });

      describe('filters', function () {

        it('staff member filter is set', function () {

        });

        it('region filter is set', function () {

        });

        it('department filter is set', function () {

        });

        it('level type filter is set', function () {

        });

        it('location filter is set', function () {

        });

        it('pending requests filter is set', function () {

        });
      });

      function compileComponent() {
        var $scope = $rootScope.$new();
        var contactId = CRM.vars.leaveAndAbsences.contactId;

        component = angular.element('<manager-leave-requests contact-id="' + contactId + '"></manager-leave-requests>');
        $compile(component)($scope);
        $scope.$digest();

        controller = component.controller('managerLeaveRequests');
      }
    });
  })
})(CRM);
