/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'mocks/data/option-group-mock-data',
  'mocks/data/absence-period-data',
  'mocks/data/absence-type-data',
  'mocks/data/leave-request-data',
  'common/mocks/services/api/contact-mock',
  'mocks/apis/absence-period-api-mock',
  'mocks/apis/absence-type-api-mock',
  'mocks/apis/leave-request-api-mock',
  'mocks/apis/option-group-api-mock',
  'leave-absences/shared/config',
  'leave-absences/manager-leave/app'
], function (angular, _, optionGroupMock, absencePeriodData, absenceTypeData, leaveRequestData) {
  'use strict';

  describe('manageLeaveRequests', function () {
    var $componentController, $log, $q, $provide, $rootScope, controller,
      OptionGroup, AbsenceType, AbsencePeriod, LeaveRequest,
      Contact, ContactAPIMock, sharedSettings, OptionGroupAPIMock, LeavePopup;
    var contactId = '204';
    var role = 'admin'; // change this value to set other roles

    beforeEach(module('leave-absences.templates', 'manager-leave',
      'leave-absences.mocks', 'leave-absences.settings', function (_$provide_) {
        $provide = _$provide_;
      }));

    beforeEach(inject(function (AbsencePeriodAPIMock, AbsenceTypeAPIMock,
      LeaveRequestAPIMock) {
      $provide.value('AbsencePeriodAPI', AbsencePeriodAPIMock);
      $provide.value('AbsenceTypeAPI', AbsenceTypeAPIMock);
      $provide.value('LeaveRequestAPI', LeaveRequestAPIMock);
      $provide.value('checkPermissions', function (permission) {
        var returnValue = false;
        if (role === 'admin') {
          returnValue = permission === sharedSettings.permissions.admin.administer;
        }
        if (role === 'manager') {
          returnValue = permission === sharedSettings.permissions.ssp.manage;
        }

        return $q.resolve(returnValue);
      });
    }));

    beforeEach(inject(['api.contact.mock', 'shared-settings', function (_ContactAPIMock_, _sharedSettings_) {
      ContactAPIMock = _ContactAPIMock_;
      sharedSettings = _sharedSettings_;
    }]));

    beforeEach(inject(function (
      _$componentController_, _$log_, _$rootScope_, _$q_, _OptionGroup_,
      _OptionGroupAPIMock_, _AbsencePeriod_, _AbsenceType_, _LeaveRequest_, _Contact_, _LeavePopup_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      OptionGroupAPIMock = _OptionGroupAPIMock_;
      OptionGroup = _OptionGroup_;
      AbsenceType = _AbsenceType_;
      AbsencePeriod = _AbsencePeriod_;
      LeaveRequest = _LeaveRequest_;
      LeavePopup = _LeavePopup_;
      Contact = _Contact_;
    }));

    beforeEach(function () {
      spyOn($log, 'debug');
      spyOn(AbsencePeriod, 'all').and.callThrough();
      spyOn(AbsenceType, 'all').and.callThrough();
      spyOn(Contact, 'leaveManagees').and.callFake(function () {
        return ContactAPIMock.leaveManagees();
      });
      spyOn(Contact, 'all').and.callFake(function (filters) {
        return ContactAPIMock.all(filters);
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
      /*
       * @TODO need to figure out how to test variables which are
       * changed when controller gets initialized
       */
      describe('before loading starts', function () {
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

        describe('absence periods', function () {
          it('loads the absence periods', function () {
            expect(controller.absencePeriods.length).toBe(absencePeriodData.all().values.length);
          });

          it('sorts absence periods by start_date', function () {
            var extractStartDate = function (period) {
              return period.start_date;
            };
            var absencePeriodSortedByDate = _.sortBy(absencePeriodData.all().values, 'start_date').map(extractStartDate);

            expect(controller.absencePeriods.map(extractStartDate)).toEqual(absencePeriodSortedByDate);
          });
        });

        it('loaded absence types', function () {
          expect(controller.absenceTypes.length).not.toBe(0);
        });

        it('filtered list of contacts have loaded', function () {
          expect(controller.filteredUsers).toEqual(ContactAPIMock.mockedContacts().list);
        });

        it('loaded leave requests data for filters', function () {
          expect(controller.leaveRequests.table.list.length).toEqual(leaveRequestData.all().values.length);
        });

        it('loaded leave requests', function () {
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
            return request.status_id === status.value;
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
          returnValue = controller.getNavBadge(sharedSettings.statusNames.approved);
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
        spyOn(controller, 'refresh').and.callThrough();
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
          var pageNoParam = 5;
          var oldPageNo = 4;

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
        _.each(['manager', 'admin'], function (_role_) {
          beforeEach(function () {
            role = _role_;
            compileComponent();
          });

          describe('region', function () {
            describe('when region filter has value', function () {
              var mockRegion = 'mockvalue';

              beforeEach(function () {
                setContactFilter('region', mockRegion);
              });

              it('calls Contact API with a region value', function () {
                expectContactApiToHaveBeenCalledWith(role, { region: mockRegion });
              });
            });

            describe('when region filter has no value', function () {
              beforeEach(function () {
                setContactFilter('region', null);
              });

              it('calls Contact API with a null value', function () {
                expectContactApiToHaveBeenCalledWith(role, { region: null });
              });
            });
          });

          describe('department', function () {
            describe('when department filter has value', function () {
              var mockDepartment = 'mockvalue';

              beforeEach(function () {
                setContactFilter('department', mockDepartment);
              });

              it('calls Contact API with a department value', function () {
                expectContactApiToHaveBeenCalledWith(role, { department: mockDepartment });
              });
            });

            describe('when department filter has no value', function () {
              beforeEach(function () {
                setContactFilter('department', null);
              });

              it('calls Contact API with a null value', function () {
                expectContactApiToHaveBeenCalledWith(role, { department: null });
              });
            });
          });

          describe('location', function () {
            describe('when location filter has value', function () {
              var mockLocation = 'mockvalue';

              beforeEach(function () {
                setContactFilter('location', mockLocation);
              });

              it('calls Contact API with a location value', function () {
                expectContactApiToHaveBeenCalledWith(role, { location: mockLocation });
              });
            });

            describe('when location filter has no value', function () {
              beforeEach(function () {
                setContactFilter('location', null);
              });

              it('calls Contact API with a null value', function () {
                expectContactApiToHaveBeenCalledWith(role, { location: null });
              });
            });
          });

          describe('levelTypes', function () {
            describe('when levelTypes filter has value', function () {
              var mockLevelType = 'mockvalue';

              beforeEach(function () {
                setContactFilter('level_type', mockLevelType);
              });

              it('calls Contact API with a level types value', function () {
                expectContactApiToHaveBeenCalledWith(role, { level_type: mockLevelType });
              });
            });

            describe('when levelTypes filter has no value', function () {
              beforeEach(function () {
                setContactFilter('level_type', null);
              });

              it('calls Contact API with a null value', function () {
                expectContactApiToHaveBeenCalledWith(role, { level_type: null });
              });
            });
          });

          /**
           * Tests if Contact API has been called with correct parameters
           *
           * @param {string} role - admin|manager
           * @param {Object} params - params the API is expected to be called with
           */
          function expectContactApiToHaveBeenCalledWith (role, params) {
            var object = jasmine.objectContaining(params);

            (role === 'manager') && expect(Contact.leaveManagees).toHaveBeenCalledWith(controller.contactId, object);
            (role === 'admin') && expect(Contact.all).toHaveBeenCalledWith(object);
          }

          /**
           * Sets contact filter
           *
           * @param {string} name - filter name
           * @param {Object} value - filter value
           */
          function setContactFilter (name, value) {
            controller.filters.contact[name] = value;

            controller.refresh();
          }
        });
      });

      describe('leaveRequestFilters', function () {
        var promise, defer;

        beforeEach(function () {
          defer = $q.defer();
          spyOn(LeaveRequest, 'all').and.callThrough();
          spyOn(controller, 'getUserNameByID');
          Contact.leaveManagees.and.returnValue(defer.promise);
          promise = defer.promise;
        });

        afterEach(function () {
          $rootScope.$apply();
        });

        describe('managed_by', function () {
          describe('when user is manager', function () {
            beforeEach(function () {
              role = 'manager';

              compileComponent();
              executeFilter();
            });

            it('fetches only the leave requests managed by the user', function () {
              expectLeaveRequestsFilteredBy({ managed_by: contactId });
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
              executeFilter();
            });

            it('filtered by type id', function () {
              expectLeaveRequestsFilteredBy({ type_id: 'mockedvalue' });
            });
          });

          describe('when selectedAbsenceTypes has no value', function () {
            var mockAbsenceType = null;

            beforeEach(function () {
              controller.filters.leaveRequest.selectedAbsenceTypes = mockAbsenceType;
              executeFilter();
            });

            it('not filtered by type id', function () {
              expectLeaveRequestsFilteredBy({ type_id: null });
            });
          });
        });

        describe('from_date', function () {
          var mockFromDate = new Date();

          beforeEach(function () {
            controller.filters.leaveRequest.selectedPeriod.start_date = mockFromDate;
            executeFilter();
          });

          it('filtered by from date', function () {
            expectLeaveRequestsFilteredBy({ from_date: { from: mockFromDate } });
          });
        });

        describe('to_date', function () {
          var mockToDate = new Date();

          beforeEach(function () {
            controller.filters.leaveRequest.selectedPeriod.end_date = mockToDate;
            executeFilter();
          });

          it('filtered by to date', function () {
            expectLeaveRequestsFilteredBy({ to_date: { to: mockToDate } });
          });
        });

        describe('contact_id', function () {
          describe('when contact has value', function () {
            var mockUserID = '202';

            beforeEach(function () {
              controller.filters.leaveRequest.contact_id = mockUserID;
              executeFilter();
            });

            it('filtered by contact id', function () {
              expectLeaveRequestsFilteredBy({ contact_id: mockUserID });
            });
          });

          describe('when contact is null and filtered users has value', function () {
            beforeEach(function () {
              controller.filters.leaveRequest.contact_id = null;
              executeFilter();
            });

            it('filtered by filtered users', function () {
              expectLeaveRequestsFilteredBy({
                contact_id: {
                  'IN': controller.filteredUsers.map(function (contact) { return contact.id; })
                }
              });
            });
          });
        });

        describe('status_id', function () {
          describe('when filtering by a specific status', function () {
            var mockStatus = { value: 'mockvalue' };

            beforeEach(function () {
              controller.filters.leaveRequest.leaveStatus = mockStatus;
              controller.filters.leaveRequest.pending_requests = false;
              executeFilter();
            });

            it('filtered by selected leave status', function () {
              promise.then(function () {
                expect(LeaveRequest.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  status_id: {
                    IN: ['mockvalue']
                  }
                }), jasmine.any(Object), jasmine.any(String), jasmine.any(Object), jasmine.any(Boolean));
              });
            });
          });

          describe('when only pending requests need to be loaded', function () {
            var waitingApprovalValue = optionGroupMock.getCollection('hrleaveandabsences_leave_request_status').find(function (data) {
              return data.name === 'awaiting_approval';
            }).value;
            var moreInformationRequiredID = optionGroupMock.getCollection('hrleaveandabsences_leave_request_status').find(function (data) {
              return data.name === 'more_information_required';
            }).value;

            beforeEach(function () {
              controller.filters.leaveRequest.pending_requests = true;
              executeFilter();
            });

            it('filtered by waiting approval and more information required status', function () {
              expectLeaveRequestsFilteredBy({
                status_id: { IN: [moreInformationRequiredID, waitingApprovalValue] }
              });
            });
          });
        });

        describe('filter by assignee', function () {
          var filterTypes;
          beforeEach(function () {
            role = 'admin';

            compileComponent();

            filterTypes = controller.filtersByAssignee.map(function (filter) {
              return filter.type;
            });
          });

          it('has a correctly ordered list of options', function () {
            expect(filterTypes).toEqual(['me', 'unassigned', 'all']);
          });

          it('defaults to "Assigned to me"', function () {
            expect(controller.filters.leaveRequest.assignedTo).toBe('me');
          });

          describe('when "All" filter is selected', function () {
            beforeEach(function () {
              executeFilterByAssignee('all');
            });

            it('ignores both "managed_by" and "unassigned" parameters in API call', function () {
              expectLeaveRequestsFilteredBy({ managed_by: undefined, unassigned: undefined });
            });
          });

          describe('when "Assigned to me" filter is selected', function () {
            beforeEach(function () {
              executeFilterByAssignee('me');
            });

            it('calls API with "managed_by" parameter but ignores "unassigned" parameter', function () {
              expectLeaveRequestsFilteredBy({ managed_by: contactId, unassigned: undefined });
            });
          });

          describe('when "Unassigned" filter is selected', function () {
            beforeEach(function () {
              executeFilterByAssignee('unassigned');
            });

            it('calls API with "unassigned" parameter but ignores "managed_by" parameter', function () {
              expectLeaveRequestsFilteredBy({ managed_by: undefined, unassigned: true });
            });
          });

          /**
           * Executes the filter by refreshing the controller with a specified
           * filter type and resolving the promise with mocked contacts
           *
           * @param {string} type - all|unassigned|me
           */
          function executeFilterByAssignee (type) {
            controller.refreshWithFilterByAssignee(type);
            defer.resolve(ContactAPIMock.mockedContacts().list);
          }
        });

        /**
         * Executes the filter by refreshing the controller
         * and resolving the promise with mocked contacts
         */
        function executeFilter () {
          controller.refresh();
          defer.resolve(ContactAPIMock.mockedContacts().list);
        }

        /**
         * Tests if the leave request's recent call
         * included specific set of parameters
         *
         * @param {Object} parameters
         */
        function expectLeaveRequestsFilteredBy (parameters) {
          promise.then(function () {
            expect(LeaveRequest.all.calls.mostRecent().args[0]).toEqual(jasmine.objectContaining(parameters));
          });
        }
      });
    });

    describe('when new leave request is created', function () {
      beforeEach(function () {
        $rootScope.$emit('LeaveRequest::new', jasmine.any(Object));
      });

      it('calls related contact API to update', function () {
        expect(role === 'admin' ? Contact.all : Contact.leaveManagees).toHaveBeenCalled();
      });
    });

    describe('when new leave request is updated', function () {
      beforeEach(function () {
        $rootScope.$emit('LeaveRequest::updatedByManager', jasmine.any(Object));
      });

      it('calls related contact API to update', function () {
        expect(role === 'admin' ? Contact.all : Contact.leaveManagees).toHaveBeenCalled();
      });
    });

    describe('openLeavePopup()', function () {
      var leaveRequest = { key: 'value' };
      var leaveType = 'some_leave_type';
      var selectedContactId = '101';
      var isSelfRecord = true;

      beforeEach(function () {
        spyOn(LeavePopup, 'openModal');
        controller.openLeavePopup(leaveRequest, leaveType, selectedContactId, isSelfRecord);
      });

      it('opens the leave request popup', function () {
        expect(LeavePopup.openModal).toHaveBeenCalledWith(leaveRequest, leaveType, selectedContactId, isSelfRecord);
      });
    });

    function compileComponent () {
      controller = $componentController('manageLeaveRequests', null, { contactId: contactId });
      $rootScope.$digest();
    }
  });
});
