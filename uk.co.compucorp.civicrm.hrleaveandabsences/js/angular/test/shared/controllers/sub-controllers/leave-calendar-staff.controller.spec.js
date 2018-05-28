/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/lodash',
    'common/mocks/data/contact.data',
    'leave-absences/my-leave/app'
  ], function (_, contactsMock) {
    'use strict';

    describe('LeaveCalendarStaffController', function () {
      var $controller, $log, $rootScope, Contact, contactIdsToReduceTo, controller,
        filteredContacts, leaveCalendar, LeaveCalendarService, lookedUpContacts,
        vm;
      var contactId = CRM.vars.leaveAndAbsences.contactId;

      beforeEach(module('my-leave'));
      beforeEach(inject(function (_$controller_, _$log_, $q, _$rootScope_, _Contact_,
        _LeaveCalendarService_) {
        $controller = _$controller_;
        $log = _$log_;
        $rootScope = _$rootScope_;
        Contact = _Contact_;
        contactIdsToReduceTo = [_.uniqueId(), _.uniqueId(), _.uniqueId()];
        filteredContacts = _.clone(contactsMock.all.values.slice(0, 2));
        LeaveCalendarService = _LeaveCalendarService_;
        leaveCalendar = jasmine.createSpyObj('leaveCalendar', [
          'loadAllLookUpContacts', 'loadContactIdsToReduceTo',
          'loadFilteredContacts']);
        lookedUpContacts = _.clone(contactsMock.all.values);

        spyOn($log, 'debug');
        spyOn(Contact, 'all').and.callThrough();
        spyOn(LeaveCalendarService, 'init').and.returnValue(leaveCalendar);
        leaveCalendar.loadAllLookUpContacts.and.returnValue($q.resolve(
          lookedUpContacts));
        leaveCalendar.loadContactIdsToReduceTo.and.returnValue($q.resolve(
          contactIdsToReduceTo));
        leaveCalendar.loadFilteredContacts.and.returnValue($q.resolve(
          filteredContacts));

        initController();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      it('initializes the leave calendar service', function () {
        expect(LeaveCalendarService.init).toHaveBeenCalledWith(vm);
      });

      it('displays the contact names', function () {
        expect(vm.showContactName).toBe(true);
      });

      it('displays the contact filters', function () {
        expect(vm.showFilters).toBe(true);
      });

      describe('loadContacts()', function () {
        var result, expectedContactIdsToReduceTo;

        beforeEach(function (done) {
          expectedContactIdsToReduceTo = _.clone(contactIdsToReduceTo);

          expectedContactIdsToReduceTo.push(vm.contactId);
          controller.loadContacts()
            .then(function (_result_) {
              result = _result_;
            })
            .finally(done);
          $rootScope.$digest();
        });

        it('loads all the look up contacts', function () {
          expect(leaveCalendar.loadAllLookUpContacts).toHaveBeenCalled();
        });

        it('loads the contact ids to reduce to', function () {
          expect(leaveCalendar.loadContactIdsToReduceTo).toHaveBeenCalled();
        });

        it('loads the filtered contacts', function () {
          expect(leaveCalendar.loadFilteredContacts).toHaveBeenCalled();
        });

        it('stores the looked up contacts', function () {
          expect(vm.lookupContacts).toEqual(lookedUpContacts);
        });

        it('stores the contact ids to reduce to by contract plus the logged in contact id', function () {
          expect(vm.contactIdsToReduceTo).toEqual(expectedContactIdsToReduceTo);
        });

        it('returns the filtered contacts', function () {
          expect(result).toEqual(filteredContacts);
        });
      });

      function initController () {
        vm = {
          contactId: contactId,
          filters: { userSettings: {} }
        };
        controller = $controller('LeaveCalendarStaffController').init(vm);
      }
    });
  });
}(CRM));
