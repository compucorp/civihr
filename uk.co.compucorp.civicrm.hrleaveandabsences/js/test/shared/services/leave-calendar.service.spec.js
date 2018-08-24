/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/mocks/data/contact.data',
  'common/mocks/data/contract.data',
  'common/models/contact',
  'common/models/contract',
  'common/services/hr-settings',
  'leave-absences/shared/services/leave-calendar.service'
], function (_, contactsMockData, contractsMockData) {
  'use strict';

  describe('LeaveCalendarService', function () {
    var $rootScope, Contact, contractApi, customContractValues, leaveCalendar,
      loadedContacts, mockedContracts, vm;
    var mockData = {};

    beforeEach(module('common.mocks', 'common.models', 'common.services',
      'leave-absences.services', function ($provide) {
        contractApi = {
          all: jasmine.createSpy('all')
        };
        customContractValues = [
          { contact_id: _.uniqueId(), period_start_date: '2000-01-01', period_end_date: '2000-01-31' },
          { contact_id: _.uniqueId(), period_start_date: '2000-02-01', period_end_date: '2000-02-29' },
          { contact_id: _.uniqueId(), period_start_date: '2000-03-01', period_end_date: '2000-03-31' }
        ];
        mockedContracts = getMockedContracts(customContractValues);

        $provide.value('api.contract', contractApi);
      })
    );

    beforeEach(inject(function ($q, _$rootScope_, _Contact_, LeaveCalendarService) {
      var contacts = contactsMockData.all.values;
      $rootScope = _$rootScope_;
      Contact = _Contact_;
      mockData = {
        lookupContacts: contacts,
        contacts: contacts.slice(0, 2)
      };
      vm = {
        contactId: _.uniqueId(),
        lookupContacts: [],
        selectedPeriod: { start_date: '2000-01-01', end_date: '2000-03-31' },
        filters: {
          userSettings: {
            assignedTo: { type: 'all' },
            department: { value: _.uniqueId() },
            level_type: { value: _.uniqueId() },
            location: { value: _.uniqueId() },
            region: { value: _.uniqueId() }
          }
        }
      };

      spyOn(Contact, 'all').and.returnValue($q.resolve({ list: mockData.contacts }));
      spyOn(Contact, 'leaveManagees').and.returnValue($q.resolve(mockData.lookupContacts));
      contractApi.all.and.returnValue($q.resolve(mockedContracts));

      leaveCalendar = LeaveCalendarService.init(vm);
    }));

    it('returns a leave calendar service instance', function () {
      expect(leaveCalendar).toBeDefined();
    });

    describe('loadContactsForAdmin()', function () {
      describe('when loading all contacts', function () {
        beforeEach(function (done) {
          vm.filters.userSettings.assignedTo.type = 'all';
          loadContactsForAdmin(done);
        });

        it('stores all look up contact', function () {
          expect(vm.lookupContacts).toEqual(mockData.contacts);
        });
      });

      describe('when loading my assigned contacts', function () {
        beforeEach(function (done) {
          vm.filters.userSettings.assignedTo.type = 'me';
          loadContactsForAdmin(done);
        });

        it('requests my assigned contacts', function () {
          expect(Contact.leaveManagees).toHaveBeenCalledWith(vm.contactId);
        });

        it('returns a list of contacts', function () {
          expect(loadedContacts).toEqual(mockData.contacts);
        });

        it('stores my assignees as look up contacts', function () {
          expect(vm.lookupContacts).toEqual(mockData.lookupContacts);
        });
      });

      describe('when loading unassigned contacts', function () {
        beforeEach(function (done) {
          vm.filters.userSettings.assignedTo.type = 'unassigned';

          loadContactsForAdmin(done);
        });

        it('requests unassigned contacts', function () {
          expect(Contact.leaveManagees)
            .toHaveBeenCalledWith(undefined, { unassigned: true });
        });

        it('returns a list of contacts', function () {
          expect(loadedContacts).toEqual(mockData.contacts);
        });

        it('stores the unassigned contacts as look up contacts', function () {
          expect(vm.lookupContacts).toEqual(mockData.lookupContacts);
        });
      });

      describe('populating the contact ids to reduce to', function () {
        var expectedContactIdsToReduceTo;

        describe('when all the contracts are covered by the period filters', function () {
          beforeEach(function (done) {
            expectedContactIdsToReduceTo = _.map(customContractValues, 'contact_id');
            vm.selectedPeriod = {
              start_date: customContractValues[0].period_start_date,
              end_date: customContractValues[2].period_end_date
            };

            loadContactsForAdmin(done);
          });

          it('returns all the contact ids with contracts within the selected period', function () {
            expect(vm.contactIdsToReduceTo).toEqual(expectedContactIdsToReduceTo);
          });
        });

        describe('when only some of the contracts are covered by the period filters', function () {
          beforeEach(function (done) {
            expectedContactIdsToReduceTo = _.map(customContractValues, 'contact_id').slice(1, 3);
            vm.selectedPeriod = {
              start_date: customContractValues[1].period_start_date,
              end_date: customContractValues[2].period_end_date
            };

            loadContactsForAdmin(done);
          });

          it('returns all the contact ids with contracts within the selected period', function () {
            expect(vm.contactIdsToReduceTo).toEqual(expectedContactIdsToReduceTo);
          });
        });
      });
    });

    describe('loadFilteredContacts()', function () {
      var expectedFilters;

      beforeEach(function () {
        expectedFilters = {
          department: vm.filters.userSettings.department.value,
          level_type: vm.filters.userSettings.level_type.value,
          location: vm.filters.userSettings.location.value,
          region: vm.filters.userSettings.region.value
        };
      });

      describe('general case', function () {
        var filteredContacts;

        beforeEach(function (done) {
          leaveCalendar.loadFilteredContacts()
            .then(function (_filteredContacts_) {
              filteredContacts = _filteredContacts_;
            })
            .finally(done);
          $rootScope.$digest();
        });

        it('requests a list of contacts using the selected filters and sorted by display name', function () {
          expect(Contact.all).toHaveBeenCalledWith(
            jasmine.objectContaining(expectedFilters), null, 'display_name');
        });

        it('returns a list of filtered contacts', function () {
          expect(filteredContacts).toEqual(mockData.contacts);
        });
      });

      describe('when the contact filter is selected', function () {
        beforeEach(function () {
          vm.filters.userSettings.contact = { id: _.uniqueId() };
          expectedFilters.id = { IN: [vm.filters.userSettings.contact.id] };

          leaveCalendar.loadFilteredContacts();
        });

        it('only returns the selected contact from the filter', function () {
          expect(Contact.all).toHaveBeenCalledWith(expectedFilters, null, 'display_name');
        });
      });

      describe('when there is a list of look up contacts', function () {
        var expectedContactIds;

        beforeEach(function () {
          vm.lookupContacts = _.shuffle(mockData.contacts).slice(0, 2);
          expectedContactIds = _.map(vm.lookupContacts, 'id');
          expectedFilters.id = { IN: expectedContactIds };

          leaveCalendar.loadFilteredContacts();
        });

        it('only returns the contacts from the look up list', function () {
          expect(Contact.all).toHaveBeenCalledWith(expectedFilters, null, 'display_name');
        });
      });

      describe('when no contact or look up list are selected as filters', function () {
        beforeEach(function () {
          delete vm.lookupContacts;
          delete vm.filters.userSettings.contact;

          leaveCalendar.loadFilteredContacts();
        });

        it('does not filter the request by contact id', function () {
          expect(Contact.all).not.toHaveBeenCalledWith(jasmine.objectContaining({
            id: { 'IN': jasmine.any(Array) }
          }), null, 'display_name');
        });
      });

      describe('when the asegnee filter is not "all" and the look up list is empty', function () {
        beforeEach(function () {
          vm.lookupContacts = [];
          vm.filters.userSettings.assignedTo.type = 'me';

          leaveCalendar.loadFilteredContacts();
        });

        it('filters the request by look up contact ids', function () {
          expect(Contact.all).toHaveBeenCalledWith(jasmine.objectContaining({
            id: { 'IN': vm.lookupContacts }
          }), null, 'display_name');
        });
      });
    });

    describe('loadLookUpAndFilteredContacts()', function () {
      beforeEach(function (done) {
        vm.filters.userSettings.assignedTo.type = 'me';

        leaveCalendar.loadLookUpAndFilteredContacts()
          .then(function (contacts) {
            loadedContacts = contacts;
          })
          .finally(done);
        $rootScope.$digest();
      });

      it('requests a list of look up contacts', function () {
        expect(Contact.leaveManagees).toHaveBeenCalled();
      });

      it('stores a list of look up contacts', function () {
        expect(vm.lookupContacts).toEqual(mockData.lookupContacts);
      });

      it('requests a list of filtered contacts using the look ups', function () {
        expect(Contact.all).toHaveBeenCalledWith(jasmine.objectContaining({
          id: { 'IN': _.map(vm.lookupContacts, 'id') }
        }), null, 'display_name');
      });

      it('returns a list of contacts', function () {
        expect(loadedContacts).toEqual(mockData.contacts);
      });
    });

    describe('loadLookUpContacts()', function () {
      describe('when the assignees filter value is "all"', function () {
        beforeEach(function (done) {
          vm.filters.userSettings.assignedTo.type = 'all';

          loadLookUpContacts(done);
        });

        it('requests all contacts', function () {
          expect(Contact.all).toHaveBeenCalledWith();
        });

        it('returns a list of contacts', function () {
          expect(loadedContacts).toEqual(mockData.contacts);
        });
      });

      describe('when the assignees filter value is "me"', function () {
        beforeEach(function (done) {
          vm.filters.userSettings.assignedTo.type = 'me';

          loadLookUpContacts(done);
        });

        it('requests the logged in user\'s assigned contacts', function () {
          expect(Contact.leaveManagees).toHaveBeenCalledWith(vm.contactId);
        });

        it('returns a list of the logged in user\'s assigned contacts', function () {
          expect(loadedContacts).toEqual(mockData.lookupContacts);
        });
      });

      describe('when the assignees filter value is "unassigned"', function () {
        beforeEach(function (done) {
          vm.filters.userSettings.assignedTo.type = 'unassigned';

          loadLookUpContacts(done);
        });

        it('requests unassigned contacts', function () {
          expect(Contact.leaveManagees)
            .toHaveBeenCalledWith(undefined, { unassigned: true });
        });

        it('returns a list of unassigned contacts', function () {
          expect(loadedContacts).toEqual(mockData.lookupContacts);
        });
      });
    });

    /**
     * Returns a list of mocked contracts with overriden values as provided by
     * the custom contract values parameter.
     *
     * @param {Array} customContractValues a list of values to override for each contract.
     * @return {Array} the list of overriden contracts.
     */
    function getMockedContracts (customContractValues) {
      var mockedContracts = _.cloneDeep(contractsMockData.all);

      mockedContracts.values = customContractValues.map(function (customContractValue, i) {
        var contractData = mockedContracts.values[i] || {};

        return _.extend(contractData, {
          contact_id: customContractValue.contact_id,
          'info': {
            details: {
              period_start_date: customContractValue.period_start_date,
              period_end_date: customContractValue.period_end_date
            }
          }
        });
      });

      return mockedContracts.values;
    }

    /**
     * Executes the load contacts for admin function and stores the result.
     *
     * @param {Function} done the jasmine done function to execute once the contact
     * ids have been loaded.
     */
    function loadContactsForAdmin (done) {
      leaveCalendar.loadContactsForAdmin()
        .then(function (_loadedContacts_) {
          loadedContacts = _loadedContacts_;
        })
        .finally(done);
      $rootScope.$digest();
    }

    function loadLookUpContacts (done) {
      leaveCalendar.loadLookUpContacts()
        .then(function (_loadedContacts_) {
          loadedContacts = _loadedContacts_;
        })
        .finally(done);
      $rootScope.$digest();
    }
  });
});
