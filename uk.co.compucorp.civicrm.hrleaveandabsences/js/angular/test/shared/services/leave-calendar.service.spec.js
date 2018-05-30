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
    var $rootScope, Contact, contractApi, customContractValues, leaveCalendar, mockedContracts, vm;

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
      $rootScope = _$rootScope_;
      vm = {};
      Contact = _Contact_;
      leaveCalendar = LeaveCalendarService.init(vm);

      spyOn(Contact, 'all').and.returnValue($q.resolve({ list: contactsMockData.all.values }));
      contractApi.all.and.returnValue($q.resolve(mockedContracts));
    }));

    it('defines a leave calendar service', function () {
      expect(leaveCalendar).toBeDefined();
    });

    describe('loadAllLookUpContacts()', function () {
      var allLookUpContacts;

      beforeEach(function (done) {
        leaveCalendar.loadAllLookUpContacts()
          .then(function (_allLookupContacts_) {
            allLookUpContacts = _allLookupContacts_;
          })
          .finally(done);
        $rootScope.$digest();
      });

      it('requests all contacts', function () {
        expect(Contact.all).toHaveBeenCalledWith();
      });

      it('returns a list of look up contacts', function () {
        expect(allLookUpContacts).toEqual(contactsMockData.all.values);
      });
    });

    describe('loadContactIdsToReduceTo()', function () {
      var contactIdsToReduceTo, expectedContactIdsToReduceTo;

      describe('when all the contracts are covered by the period filters', function () {
        beforeEach(function (done) {
          expectedContactIdsToReduceTo = _.pluck(customContractValues, 'contact_id');
          vm.selectedPeriod = {
            start_date: customContractValues[0].period_start_date,
            end_date: customContractValues[2].period_end_date
          };

          loadContactIdsToReduceTo(done);
        });

        it('returns all the contact ids with contracts within the selected period', function () {
          expect(contactIdsToReduceTo).toEqual(expectedContactIdsToReduceTo);
        });
      });

      describe('when only some of the contracts are covered by the period filters', function () {
        beforeEach(function (done) {
          expectedContactIdsToReduceTo = _.pluck(customContractValues, 'contact_id').slice(1, 3);
          vm.selectedPeriod = {
            start_date: customContractValues[1].period_start_date,
            end_date: customContractValues[2].period_end_date
          };

          loadContactIdsToReduceTo(done);
        });

        it('returns all the contact ids with contracts within the selected period', function () {
          expect(contactIdsToReduceTo).toEqual(expectedContactIdsToReduceTo);
        });
      });

      /**
       * Executes the load contact ids to reduce to function and stores the result.
       *
       * @param {Function} done the jasmine done function to execute once the contact
       * ids have been loaded.
       */
      function loadContactIdsToReduceTo (done) {
        leaveCalendar.loadContactIdsToReduceTo()
          .then(function (_contactIdsToReduceTo_) {
            contactIdsToReduceTo = _contactIdsToReduceTo_;
          })
          .finally(done);
        $rootScope.$digest();
      }
    });

    describe('loadFilteredContacts()', function () {
      var expectedFilters;

      beforeEach(function () {
        vm.lookupContacts = [];
        vm.filters = {
          userSettings: {
            department: { value: _.uniqueId() },
            level_type: { value: _.uniqueId() },
            location: { value: _.uniqueId() },
            region: { value: _.uniqueId() }
          }
        };
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
          expect(filteredContacts).toEqual(contactsMockData.all.values);
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
          vm.lookupContacts = _.shuffle(contactsMockData.all.values).slice(0, 2);
          expectedContactIds = _.pluck(vm.lookupContacts, 'id');
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
  });
});
