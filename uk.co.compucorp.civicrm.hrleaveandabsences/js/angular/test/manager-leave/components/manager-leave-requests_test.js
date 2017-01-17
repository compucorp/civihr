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
          mockStatus = optionGroupMock.getCollection('hrleaveandabsences_leave_request_status')[0];
          ;
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

      describe('refresh', function () {

        describe('page no', function () {

          describe('when no page no is sent', function () {

            beforeEach(function () {
              controller.refresh();
            });

            it('page no is set to 1', function () {
              expect(controller.pagination.page).toBe(1);
            });
          });

          describe('when page no is sent', function () {

            var pageNo = 5;
            beforeEach(function () {
              controller.refresh(pageNo);
            });

            it('page no is set to 1', function () {
              expect(controller.pagination.page).toBe(pageNo);
            });
          });
        });

        describe('content loading', function () {

          beforeEach(function () {
            controller.refresh();
          });

          it('shows the content loading', function () {
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
                controller.filters.contactFilters.region = mockRegion;
                controller.refresh();
              });

              it('ContactAPI gets called with region value', function () {
                expect(Contact.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  region: mockRegion.value
                }), jasmine.any(Object));
              });
            });

            describe('when region filter has no value', function () {

              beforeEach(function () {
                controller.filters.contactFilters.region = null;
                controller.refresh();
              });

              it('ContactAPI gets called with null', function () {
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
                controller.filters.contactFilters.department = mockDepartment;
                controller.refresh();
              });

              it('ContactAPI gets called with department value', function () {
                expect(Contact.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  department: mockDepartment.value
                }), jasmine.any(Object));
              });
            });

            describe('when department filter has no value', function () {

              beforeEach(function () {
                controller.filters.contactFilters.department = null;
                controller.refresh();
              });

              it('ContactAPI gets called with null', function () {
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
                controller.filters.contactFilters.location = mockLocation;
                controller.refresh();
              });

              it('ContactAPI gets called with location value', function () {
                expect(Contact.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  location: mockLocation.value
                }), jasmine.any(Object));
              });
            });

            describe('when location filter has no value', function () {

              beforeEach(function () {
                controller.filters.contactFilters.location = null;
                controller.refresh();
              });

              it('ContactAPI gets called with null', function () {
                expect(Contact.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  location: null
                }), jasmine.any(Object));
              });
            });
          });

          describe('level_type', function () {

            describe('when level_type filter is an empty array', function () {

              beforeEach(function () {
                controller.filters.contactFilters.level_type = [];
                controller.refresh();
              });

              it('ContactAPI gets called with null', function () {
                expect(Contact.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  level_type: null
                }), jasmine.any(Object));
              });
            });

            describe('when level_type filter is not empty', function () {

              var mockLevelType = [{
                value: 'mockvalue'
              }];

              beforeEach(function () {
                controller.filters.contactFilters.level_type = mockLevelType;
                controller.refresh();
              });

              it('ContactAPI gets called with level types', function () {
                expect(Contact.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  level_type: {
                    "IN": ['mockvalue']
                  }
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

            it('filtered by managed_by', function () {
              promise.then(function () {
                expect(LeaveRequest.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  managed_by: 202
                }), jasmine.any(Object));
              });
            })
          });

          describe('type_id', function () {

            describe('when selectedAbsenceTypes has value', function () {

              var mockAbsenceType = {
                id: 'mockedvalue'
              };

              beforeEach(function () {
                controller.filters.leaveRequestFilters.selectedAbsenceTypes = mockAbsenceType;
                controller.refresh();
                defer.resolve({
                  list: []
                });
              });

              it('filtered by type_id', function () {
                promise.then(function () {
                  expect(LeaveRequest.all).toHaveBeenCalledWith(jasmine.objectContaining({
                    type_id: 'mockedvalue'
                  }), jasmine.any(Object));
                });
              });
            });

            describe('when selectedAbsenceTypes has no value', function () {

              var mockAbsenceType = null;

              beforeEach(function () {
                controller.filters.leaveRequestFilters.selectedAbsenceTypes = mockAbsenceType;
                controller.refresh();
                defer.resolve({
                  list: []
                });
              });

              it('not filtered by type_id', function () {
                promise.then(function () {
                  expect(LeaveRequest.all).toHaveBeenCalledWith(jasmine.objectContaining({
                    type_id: null
                  }), jasmine.any(Object));
                });
              });
            });
          });

          describe('from_date', function () {

            var mockFromDate = new Date();

            beforeEach(function () {
              controller.filters.leaveRequestFilters.selectedPeriod.start_date = mockFromDate;
              controller.refresh();
              defer.resolve({
                list: []
              });
            });

            it('filtered by from_date', function () {
              promise.then(function () {
                expect(LeaveRequest.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  from_date: {
                    from: mockFromDate
                  }
                }), jasmine.any(Object));
              });
            });
          });

          describe('to_date', function () {

            var mockToDate = new Date();

            beforeEach(function () {
              controller.filters.leaveRequestFilters.selectedPeriod.end_date = mockToDate;
              controller.refresh();
              defer.resolve({
                list: []
              });
            });

            it('filtered by to_date', function () {
              promise.then(function () {
                expect(LeaveRequest.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  to_date: {
                    to: mockToDate
                  }
                }), jasmine.any(Object));
              });
            });
          });

          describe('contact_id', function () {

            describe('when selectedUsers has value', function () {

              var mockUser = {
                contact_id: '202'
              };

              beforeEach(function () {
                controller.filters.leaveRequestFilters.selectedUsers = mockUser;
                controller.refresh();
                defer.resolve({
                  list: []
                });
              });

              it('filtered by contact_id', function () {
                promise.then(function () {
                  expect(LeaveRequest.all).toHaveBeenCalledWith(jasmine.objectContaining({
                    contact_id: mockUser.contact_id
                  }), jasmine.any(Object));
                });
              });
            });

            describe('when selectedUsers is null and filteredUsers has value', function () {

              var mockUsers = [{
                contact_id: '202'
              }];

              beforeEach(function () {
                controller.filters.leaveRequestFilters.selectedUsers = null;
                controller.refresh();
                defer.resolve({
                  list: mockUsers
                });
              });

              it('filtered by filteredUsers', function () {
                promise.then(function () {
                  expect(LeaveRequest.all).toHaveBeenCalledWith(jasmine.objectContaining({
                    contact_id: {
                      "IN": [mockUsers[0].contact_id]
                    }
                  }), jasmine.any(Object));
                });
              });
            });

            describe('when selectedUsers is null and filteredUsers is null', function () {

              beforeEach(function () {
                controller.filters.leaveRequestFilters.selectedUsers = null;
                controller.refresh();
                defer.resolve({
                  list: []
                });
              });

              it('is not filtered by contact_id', function () {
                promise.then(function () {
                  expect(LeaveRequest.all).toHaveBeenCalledWith(jasmine.objectContaining({
                    contact_id: {
                      "IN": ['user_contact_id']
                    }
                  }), jasmine.any(Object));
                });
              });
            });
          });

          describe('status_id', function () {

            describe('when leaveStatus has value', function () {

              var mockStatus = {
                value: 'mockvalue'
              };

              beforeEach(function () {
                controller.filters.leaveRequestFilters.leaveStatus = mockStatus;
                controller.refresh();
                defer.resolve({
                  list: []
                });
              });

              it('filtered by selected leaveStatus', function () {
                promise.then(function () {
                  expect(LeaveRequest.all).toHaveBeenCalledWith(jasmine.objectContaining({
                    status_id: {
                      IN: ['mockvalue']
                    }
                  }), jasmine.any(Object));
                });
              });
            });

            describe('when pending_requests is true', function () {

              var waitingApprovalValue = optionGroupMock.getCollection('hrleaveandabsences_leave_request_status').find(function (data) {
                return data.name === 'waiting_approval';
              }).value;

              beforeEach(function () {
                controller.filters.leaveRequestFilters.pending_requests = true;
                controller.refresh();
                defer.resolve({
                  list: []
                });
              });

              it('filtered by waiting_approval', function () {
                promise.then(function () {
                  expect(LeaveRequest.all).toHaveBeenCalledWith(jasmine.objectContaining({
                    status_id: {
                      IN: [waitingApprovalValue]
                    }
                  }), jasmine.any(Object));
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
  })
})(CRM);
