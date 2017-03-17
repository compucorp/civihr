(function (CRM) {
  define([
    'common/angular',
    'mocks/data/option-group-mock-data',
    'mocks/data/absence-type-data',
    'mocks/data/leave-request-data',
    'common/mocks/services/api/contact-mock',
    'mocks/apis/absence-period-api-mock',
    'mocks/apis/absence-type-api-mock',
    'mocks/apis/leave-request-api-mock',
    'mocks/apis/option-group-api-mock',
    'leave-absences/shared/config',
    'leave-absences/manager-leave/app',
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
        ContactAPIMock,
        OptionGroupAPIMock;

      beforeEach(module('leave-absences.templates', 'manager-leave',
        'leave-absences.mocks', function (_$provide_) {
          $provide = _$provide_;
      }));

      beforeEach(inject(function (AbsencePeriodAPIMock, AbsenceTypeAPIMock,
        LeaveRequestAPIMock) {
        $provide.value('AbsencePeriodAPI', AbsencePeriodAPIMock);
        $provide.value('AbsenceTypeAPI', AbsenceTypeAPIMock);
        $provide.value('LeaveRequestAPI', LeaveRequestAPIMock);
      }));

      beforeEach(inject(['api.contact.mock', function (_ContactAPIMock_) {
        ContactAPIMock = _ContactAPIMock_;
      }]));

      beforeEach(inject(function (
        _$compile_, _$log_, _$rootScope_, _$q_, _OptionGroup_, _OptionGroupAPIMock_,
        _AbsencePeriod_, _AbsenceType_, _LeaveRequest_, _Contact_) {

        $compile = _$compile_;
        $log = _$log_;
        $q = _$q_;
        $rootScope = _$rootScope_;
        OptionGroupAPIMock = _OptionGroupAPIMock_;
        OptionGroup = _OptionGroup_;
        AbsenceType = _AbsenceType_;
        AbsencePeriod = _AbsencePeriod_;
        LeaveRequest = _LeaveRequest_;
        Contact = _Contact_;
      }));

      beforeEach(function () {
        spyOn($log, 'debug');

        spyOn(AbsencePeriod, 'all').and.callThrough();
        spyOn(AbsenceType, 'all').and.callThrough();

        spyOn(Contact, 'all').and.callFake(function () {
          return $q.resolve(ContactAPIMock.mockedContacts());
        });

        spyOn(OptionGroup, 'valuesOf').and.callFake(function (name) {
          return OptionGroupAPIMock.valuesOf(name);
        });
      });

      beforeEach(function () {
        compileComponent();
      });

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      describe('initially', function () {
        it('the filter section is closed', function () {
          expect(controller.isFilterExpanded).toBe(false);
        });

        describe('pagination', function () {
          it('is set to page 1', function () {
            expect(controller.pagination.page).toBe(1);
          });

          it('has page size set to 7', function () {
            expect(controller.pagination.size).toBe(7);
          });
        });
      });

      describe('data loading', function () {
        //TODO need to figure out how to test variables which are changed when controller gets initialized
        xdescribe('before loading starts', function () {
          it('loader is hidden', function () {});
          it('leave requests are empty', function () {});
          it('absencePeriods are empty', function () {});
          it('absenceTypes are empty', function () {});
          it('statusCount is reset', function () {});
        });

        describe('after data loading is complete', function () {
          describe('loading', function () {
            it('loader is hidden for page', function () {
              expect(controller.loading.page).toBe(false);
            });

            it('loader is hidden for content', function () {
              expect(controller.loading.content).toBe(false);
            });
          });

          it('leave requests status have loaded', function () {
            var expectedResult = optionGroupMock.getCollection('hrleaveandabsences_leave_request_status').concat({
              name: 'all',
              label: 'All'
            });

            expect(controller.leaveRequestStatuses).toEqual(expectedResult);
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

          it('absence periods have loaded', function () {
            expect(controller.absencePeriods.length).not.toBe(0);
          });

          it('absence types have loaded', function () {
            expect(controller.absenceTypes.length).not.toBe(0);
          });

          it('filtered list of contacts have loaded', function () {
            expect(controller.filteredUsers).toEqual(ContactAPIMock.mockedContacts().list);
          });

          it('leave requests have loaded', function () {
            expect(controller.leaveRequests.table.list.length).toEqual(leaveRequestData.all().values.length);
            expect(controller.leaveRequests.filter.list.length).toEqual(leaveRequestData.all().values.length);
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

      describe('getLeaveStatusByValue', function () {
        var status,
          returnValue;

        beforeEach(function () {
          status = optionGroupMock.getCollection('hrleaveandabsences_leave_request_status')[0];
          returnValue = controller.getLeaveStatusByValue(status.value);
        });

        it('returns label of the status', function () {
          expect(returnValue).toBe(status.label);
        });
      });

      describe('getAbsenceTypesByID', function () {
        var absence,
          returnValue;

        describe('when id is passed', function () {
          beforeEach(function () {
            absence = absenceTypeData.all().values[0];
            returnValue = controller.getAbsenceTypesByID(absence.id);
          });

          it('returns title of the absence', function () {
            expect(returnValue).toBe(absence.title);
          });
        });

        describe('when id is not passed', function () {
          beforeEach(function () {
            returnValue = controller.getAbsenceTypesByID();
          });

          it('returns undefined value', function () {
            expect(returnValue).toBeUndefined();
          });
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
            filteredList;

          beforeEach(function () {
            status = optionGroupMock.getCollection('hrleaveandabsences_leave_request_status')[0];
            returnValue = controller.filterLeaveRequestByStatus(status);
            filteredList = controller.leaveRequests.filter.list.filter(function (request) {
              return request.status_id == status.value;
            });
          });

          it('returns filtered data', function () {
            expect(returnValue).toEqual(filteredList);
          });
        });
      });

      describe('getNavBadge', function () {
        var returnValue;

        describe('when status is approved', function () {

          beforeEach(function () {
            returnValue = controller.getNavBadge('approved');
          });

          it('returns badge-success', function () {
            expect(returnValue).toBe('badge-success');
          });
        });

        describe('when status is rejected', function () {
          beforeEach(function () {
            returnValue = controller.getNavBadge('rejected');
          });

          it('returns badge-danger', function () {
            expect(returnValue).toBe('badge-danger');
          });
        });

        describe('when status is cancelled', function () {
          beforeEach(function () {
            returnValue = controller.getNavBadge('cancelled');
          });

          it('returns blank string', function () {
            expect(returnValue).toBe('');
          });
        });

        describe('when status is all', function () {
          beforeEach(function () {
            returnValue = controller.getNavBadge('all');
          });

          it('returns blank string', function () {
            expect(returnValue).toBe('');
          });
        });

        describe('when status is some other value', function () {
          beforeEach(function () {
            returnValue = controller.getNavBadge(jasmine.any(String));
          });

          it('returns badge-primary', function () {
            expect(returnValue).toBe('badge-primary');
          });
        });
      });

      describe('refreshWithFilter', function () {
        var mockStatus;

        beforeEach(function () {
          spyOn(controller, 'refresh');
          mockStatus = optionGroupMock.getCollection('hrleaveandabsences_leave_request_status')[0];
          controller.refreshWithFilter(mockStatus);
        });

        it('refreshes the data', function () {
          expect(controller.refresh).toHaveBeenCalled();
        });

        it('sets the leave status', function () {
          expect(controller.filters.leaveRequest.leaveStatus).toEqual(mockStatus);
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

      describe('refresh', function () {
        describe('page number', function () {
          describe('when no page number is sent', function () {
            beforeEach(function () {
              controller.refresh();
            });

            it('page number is set to 1', function () {
              expect(controller.pagination.page).toBe(1);
            });
          });

          describe('when page number is sent', function () {
            var pageNo = 5;
            beforeEach(function () {
              spyOn(controller, 'totalNoOfPages').and.returnValue(pageNo + 1);
              controller.refresh(pageNo);
            });

            it('page number is set to 1', function () {
              expect(controller.pagination.page).toBe(pageNo);
            });
          });

          describe('when sent page number is more than total no of pages', function () {
            var pageNoParam = 5,
              oldPageNo = 4;

            beforeEach(function () {
              controller.pagination.page = oldPageNo;
              spyOn(controller, 'totalNoOfPages').and.returnValue(oldPageNo);
              controller.refresh(pageNoParam);
            });

            it('page number did not change', function () {
              expect(controller.pagination.page).toBe(oldPageNo);
            });
          });
        });

        describe('content loading', function () {
          beforeEach(function () {
            controller.refresh();
          });

          it('loader is shown', function () {
            expect(controller.loading.content).toBe(true);
          });
        });

        describe('contactFilters', function () {
          describe('region', function () {
            describe('when region filter has value', function () {
              var mockRegion = {
                value: 'mockvalue'
              };

              beforeEach(function () {
                controller.filters.contact.region = mockRegion;
                controller.refresh();
              });

              it('Contact API gets called with region value', function () {
                expect(Contact.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  region: mockRegion.value
                }), jasmine.any(Object));
              });
            });

            describe('when region filter has no value', function () {
              beforeEach(function () {
                controller.filters.contact.region = null;
                controller.refresh();
              });

              it('Contact API gets called with null', function () {
                expect(Contact.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  region: null
                }), jasmine.any(Object));
              });
            });
          });

          describe('department', function () {
            describe('when department filter has value', function () {
              var mockDepartment = {
                value: 'mockvalue'
              };

              beforeEach(function () {
                controller.filters.contact.department = mockDepartment;
                controller.refresh();
              });

              it('Contact API gets called with department value', function () {
                expect(Contact.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  department: mockDepartment.value
                }), jasmine.any(Object));
              });
            });

            describe('when department filter has no value', function () {
              beforeEach(function () {
                controller.filters.contact.department = null;
                controller.refresh();
              });

              it('Contact API gets called with null', function () {
                expect(Contact.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  department: null
                }), jasmine.any(Object));
              });
            });
          });

          describe('location', function () {
            describe('when location filter has value', function () {
              var mockLocation = {
                value: 'mockvalue'
              };

              beforeEach(function () {
                controller.filters.contact.location = mockLocation;
                controller.refresh();
              });

              it('Contact API gets called with location value', function () {
                expect(Contact.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  location: mockLocation.value
                }), jasmine.any(Object));
              });
            });

            describe('when location filter has no value', function () {
              beforeEach(function () {
                controller.filters.contact.location = null;
                controller.refresh();
              });

              it('ContactAPI gets called with null', function () {
                expect(Contact.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  location: null
                }), jasmine.any(Object));
              });
            });
          });

          describe('levelTypes', function () {
            describe('when levelTypes filter has value', function () {
              var mockLevelType = {
                value: 'mockvalue'
              };

              beforeEach(function () {
                controller.filters.contact.level_type = mockLevelType;
                controller.refresh();
              });

              it('ContactAPI gets called with level types value', function () {
                expect(Contact.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  level_type: mockLevelType.value
                }), jasmine.any(Object));
              });
            });

            describe('when levelTypes filter has no value', function () {
              beforeEach(function () {
                controller.filters.contact.level_type = null;
                controller.refresh();
              });

              it('Contact API gets called with null', function () {
                expect(Contact.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  level_type: null
                }), jasmine.any(Object));
              });
            });
          });
        });

        describe('leaveRequestFilters', function () {
          var promise,
            defer;

          beforeEach(function () {
            defer = $q.defer();
            spyOn(LeaveRequest, 'all').and.callThrough();
            spyOn(controller, 'getUserNameByID');
            Contact.all.and.returnValue(defer.promise);
            promise = defer.promise;
          });

          afterEach(function () {
            $rootScope.$apply();
          });

          describe('managed_by', function () {
            beforeEach(function () {
              controller.refresh();
              defer.resolve({
                list: []
              });
            });

            it('fetches only the leave requests managed by the user', function () {
              promise.then(function () {
                expect(LeaveRequest.all.calls.mostRecent().args[0]).toEqual(jasmine.objectContaining({
                  managed_by: CRM.vars.leaveAndAbsences.contactId
                }));
              });
            });
          });

          describe('type_id', function () {
            describe('when selected absence types has value', function () {
              var mockAbsenceType = {
                id: 'mockedvalue'
              };

              beforeEach(function () {
                controller.filters.leaveRequest.selectedAbsenceTypes = mockAbsenceType;
                controller.refresh();
                defer.resolve({
                  list: []
                });
              });

              it('filtered by type id', function () {
                promise.then(function () {
                  expect(LeaveRequest.all.calls.mostRecent().args[0]).toEqual(jasmine.objectContaining({
                    type_id: 'mockedvalue'
                  }));
                });
              });
            });

            describe('when selectedAbsenceTypes has no value', function () {
              var mockAbsenceType = null;

              beforeEach(function () {
                controller.filters.leaveRequest.selectedAbsenceTypes = mockAbsenceType;
                controller.refresh();
                defer.resolve({
                  list: []
                });
              });

              it('not filtered by type id', function () {
                promise.then(function () {
                  expect(LeaveRequest.all.calls.mostRecent().args[0]).toEqual(jasmine.objectContaining({
                    type_id: null
                  }));
                });
              });
            });
          });

          describe('from_date', function () {
            var mockFromDate = new Date();

            beforeEach(function () {
              controller.filters.leaveRequest.selectedPeriod.start_date = mockFromDate;
              controller.refresh();
              defer.resolve({
                list: []
              });
            });

            it('filtered by from date', function () {
              promise.then(function () {
                expect(LeaveRequest.all.calls.mostRecent().args[0]).toEqual(jasmine.objectContaining({
                  from_date: {
                    from: mockFromDate
                  }
                }));
              });
            });
          });

          describe('to_date', function () {
            var mockToDate = new Date();

            beforeEach(function () {
              controller.filters.leaveRequest.selectedPeriod.end_date = mockToDate;
              controller.refresh();
              defer.resolve({
                list: []
              });
            });

            it('filtered by to date', function () {
              promise.then(function () {
                expect(LeaveRequest.all.calls.mostRecent().args[0]).toEqual(jasmine.objectContaining({
                  to_date: {
                    to: mockToDate
                  }
                }));
              });
            });
          });

          describe('contact_id', function () {
            describe('when contact has value', function () {
              var mockUser = {
                contact_id: '202'
              };

              beforeEach(function () {
                controller.filters.leaveRequest.contact = mockUser;
                controller.refresh();
                defer.resolve({
                  list: []
                });
              });

              it('filtered by contact id', function () {
                promise.then(function () {
                  expect(LeaveRequest.all.calls.mostRecent().args[0]).toEqual(jasmine.objectContaining({
                    contact_id: mockUser.contact_id
                  }));
                });
              });
            });

            describe('when contact is null and filtered users has value', function () {
              var mockUsers = [{
                contact_id: '202'
              }];

              beforeEach(function () {
                controller.filters.leaveRequest.contact = null;
                controller.refresh();
                defer.resolve({
                  list: mockUsers
                });
              });

              it('filtered by filtered users', function () {
                promise.then(function () {
                  expect(LeaveRequest.all.calls.mostRecent().args[0]).toEqual(jasmine.objectContaining({
                    contact_id: {
                      "IN": [mockUsers[0].contact_id]
                    }
                  }));
                });
              });
            });

            describe('when contact is null and filtered users is null', function () {
              beforeEach(function () {
                controller.filters.leaveRequest.contact = null;
                controller.refresh();
                defer.resolve({
                  list: []
                });
              });

              it('is not filtered by contact id', function () {
                promise.then(function () {
                  expect(LeaveRequest.all.calls.mostRecent().args[0]).toEqual(jasmine.objectContaining({
                    contact_id: null
                  }));
                });
              });
            });
          });

          describe('status_id', function () {
            describe('when filtering by a specific status', function () {
              var mockStatus = {
                value: 'mockvalue'
              };

              beforeEach(function () {
                controller.filters.leaveRequest.leaveStatus = mockStatus;
                controller.refresh();
                defer.resolve({
                  list: []
                });
              });

              it('filtered by selected leave status', function () {
                promise.then(function () {
                  expect(LeaveRequest.all).toHaveBeenCalledWith(jasmine.objectContaining({
                    status_id: {
                      IN: ['mockvalue']
                    }
                  }), jasmine.any(Object), null, jasmine.any(Object), jasmine.any(Boolean));
                });
              });
            });

            describe('when only pending requests need to be loaded', function () {
              var waitingApprovalValue = optionGroupMock.getCollection('hrleaveandabsences_leave_request_status').find(function (data) {
                return data.name === 'waiting_approval';
              }).value;

              beforeEach(function () {
                controller.filters.leaveRequest.pending_requests = true;
                controller.refresh();
                defer.resolve({
                  list: []
                });
              });

              it('filtered by waiting approval status', function () {
                promise.then(function () {
                  expect(LeaveRequest.all.calls.mostRecent().args[0]).toEqual(jasmine.objectContaining({
                    status_id: {
                      IN: [waitingApprovalValue]
                    }
                  }));
                });
              });
            });
          });
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
  });
})(CRM);
