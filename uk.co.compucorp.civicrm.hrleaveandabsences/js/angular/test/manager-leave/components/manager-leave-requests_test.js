(function (CRM) {
  define([
    'common/angular',
    'mocks/data/option-group-mock-data',
    'mocks/data/absence-type-data',
    'mocks/data/leave-request-data',
    'leave-absences/shared/config',
    'leave-absences/manager-leave/app',
    'mocks/apis/absence-period-api-mock',
    'mocks/apis/absence-type-api-mock',
    'mocks/apis/leave-request-api-mock',
    'common/mocks/services/api/contact-mock'
  ], function (angular, optionGroupMock, absenceTypeData, leaveRequestData) {
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
            expect(controller.leaveRequests.table.list).toEqual(leaveRequestData.all().values);
            expect(controller.leaveRequests.filter.list).toEqual(leaveRequestData.all().values);
          });
        });
      });

      describe('pagination', function () {

        describe('totalNoOfPages', function () {

          var returnValue;

          beforeEach(function () {
            controller.leaveRequests.table.total = 20;
            controller.pagination.size = 5;
            returnValue = controller.totalNoOfPages();
          });

          it('returns correct number of pages', function () {
            expect(returnValue).toBe(4);
          });
        });

        describe('nextPage', function () {

          beforeEach(function () {
            spyOn(controller, 'totalNoOfPages').and.returnValue(2);
            spyOn(controller, 'refresh');
          });

          describe('current page is less than total page number', function () {

            beforeEach(function () {
              controller.pagination.page = 1;
              controller.totalNoOfPages.and.returnValue(2);
              controller.nextPage();
            });

            it('page no gets increased', function () {
              expect(controller.pagination.page).toBe(2);
            });

            it('calls refresh', function () {
              expect(controller.refresh).toHaveBeenCalledWith(2);
            });
          });

          describe('current page is not less than total page number', function () {

            beforeEach(function () {
              controller.pagination.page = 3;
              controller.totalNoOfPages.and.returnValue(3);
              controller.nextPage();
            });

            it('does not increase page no', function () {
              expect(controller.pagination.page).toBe(3);
            });

            it('calls refresh', function () {
              expect(controller.refresh).not.toHaveBeenCalled();
            });
          });
        });
      });

      xdescribe('filters', function () {

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

      describe('period label', function () {
        var label, period;

        describe('when the period is current', function () {
          beforeEach(function () {
            period = _(controller.absencePeriods).find(function (period) {
              return period.current;
            });
            label = controller.labelPeriod(period);
          });

          it('labels it as such', function () {
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

      describe('getLeaveStatusByValue', function () {

        var status,
          returnValue;

        beforeEach(function () {
          status = optionGroupMock.getCollection('hrleaveandabsences_leave_request_status')[0];
          returnValue = controller.getLeaveStatusByValue(status.value)
        });

        it('returns label of the status', function () {
          expect(returnValue).toBe(status.label);
        })
      });

      describe('getAbsenceTypesByID', function () {

        var absence,
          returnValue;

        describe('when id is passed', function () {

          beforeEach(function () {
            absence = absenceTypeData.all().values[0];
            returnValue = controller.getAbsenceTypesByID(absence.id)
          });

          it('returns title of the absence', function () {
            expect(returnValue).toBe(absence.title);
          })
        });

        describe('when id is not passed', function () {

          beforeEach(function () {
            returnValue = controller.getAbsenceTypesByID()
          });

          it('returns title of the absence', function () {
            expect(returnValue).toBeUndefined();
          })
        });

      });

      describe('filterLeaveRequestByStatus', function () {

        var returnValue;

        describe('when status is blank', function () {

          beforeEach(function () {
            returnValue = controller.filterLeaveRequestByStatus('');
          });

          it('returns all data', function () {
            expect(returnValue).toEqual(controller.leaveRequests.filter.list);
          });
        });

        describe('when status is all', function () {

          beforeEach(function () {
            returnValue = controller.filterLeaveRequestByStatus({
              name: 'all'
            });
          });

          it('returns all data', function () {
            expect(returnValue).toEqual(controller.leaveRequests.filter.list);
          });
        });

        describe('for any other status', function () {

          var status,
            expectedValue;

          beforeEach(function () {
            status = optionGroupMock.getCollection('hrleaveandabsences_leave_request_status')[0];
            returnValue = controller.filterLeaveRequestByStatus(status);
            expectedValue = controller.leaveRequests.filter.list.filter(function (request) {
              return request.status_id == status.value;
            });
          });

          it('returns all data', function () {
            expect(returnValue).toEqual(expectedValue);
          });
        });
      });

      describe('getNavBadge', function () {

        var returnValue;

        describe('when status is approved', function () {

          beforeEach(function () {
            returnValue = controller.getNavBadge('approved')
          });

          it('returns badge-success', function () {
            expect(returnValue).toBe('badge-success');
          })
        });

        describe('when status is rejected', function () {

          beforeEach(function () {
            returnValue = controller.getNavBadge('rejected')
          });

          it('returns badge-danger', function () {
            expect(returnValue).toBe('badge-danger');
          })
        });

        describe('when status is cancelled', function () {

          beforeEach(function () {
            returnValue = controller.getNavBadge('cancelled')
          });

          it('returns blank string', function () {
            expect(returnValue).toBe('');
          })
        });

        describe('when status is all', function () {

          beforeEach(function () {
            returnValue = controller.getNavBadge('all')
          });

          it('returns blank string', function () {
            expect(returnValue).toBe('');
          })
        });

        describe('when status is some other value', function () {

          beforeEach(function () {
            returnValue = controller.getNavBadge(jasmine.any(String))
          });

          it('returns blank string', function () {
            expect(returnValue).toBe('badge-primary');
          })
        });
      });

      describe('refreshWithFilter', function () {

        var mockStatus;

        beforeEach(function () {
          spyOn(controller, 'refresh');
          mockStatus = optionGroupMock.getCollection('hrleaveandabsences_leave_request_status')[0];;
          controller.refreshWithFilter(mockStatus);
        });

        it('refreshes the data', function () {
          expect(controller.refresh).toHaveBeenCalled();
        });

        it('sets the leaveStatus', function () {
          expect(controller.filters.leaveRequestFilters.leaveStatus).toEqual(mockStatus);
        });
      });

      describe('getUserNameByID', function () {

        var returnValue;

        beforeEach(function () {
          returnValue = controller.getUserNameByID(controller.filteredUsers[0].id);
        });

        it('returns name of the user', function () {
          expect(returnValue).toBe(controller.filteredUsers[0].display_name);
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
