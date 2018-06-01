/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/lodash',
    'leave-absences/mocks/services/leave-calendar.service.mock',
    'leave-absences/my-leave/app'
  ], function (_, leaveCalendarServiceMock) {
    'use strict';

    describe('LeaveCalendarStaffController', function () {
      var $controller, $log, $provide, $rootScope, canManageRequests, controller,
        vm;
      var contactId = CRM.vars.leaveAndAbsences.contactId;

      beforeEach(module('my-leave', function (_$provide_) {
        $provide = _$provide_;
      }));

      beforeEach(inject(['api.contact.mock', function (ContactAPIMock) {
        $provide.value('api.contact', ContactAPIMock);
        $provide.value('LeaveCalendarService', leaveCalendarServiceMock.service);
      }]));

      beforeEach(inject(function (_$controller_, _$log_, $q, _$rootScope_) {
        $controller = _$controller_;
        $log = _$log_;
        $rootScope = _$rootScope_;
        canManageRequests = jasmine.createSpy('canManageRequests');

        spyOn($log, 'debug');
        leaveCalendarServiceMock.setup($q);
        initController();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      it('initializes the leave calendar service', function () {
        expect(leaveCalendarServiceMock.service.init).toHaveBeenCalledWith(vm);
      });

      it('displays the contact names', function () {
        expect(vm.showContactName).toBe(true);
      });

      it('displays the contact filters', function () {
        expect(vm.showFilters).toBe(true);
      });

      it('always displays the logged in contact even if they do not have requests for the selected period', function () {
        expect(vm.showTheseContacts).toEqual([vm.contactId]);
      });

      describe('loadContacts()', function () {
        var loadedContacts;

        describe('as a staff', function () {
          beforeEach(function (done) {
            canManageRequests.and.returnValue(false);
            controller.loadContacts()
              .then(function (_loadedContacts_) {
                loadedContacts = _loadedContacts_;
              })
              .finally(done);
            $rootScope.$digest();
          });

          it('loads the filtered contacts', function () {
            expect(leaveCalendarServiceMock.instance.loadFilteredContacts).toHaveBeenCalledWith();
          });

          it('returns the filtered contacts', function () {
            expect(loadedContacts).toEqual(leaveCalendarServiceMock.data.filteredContacts);
          });
        });

        describe('as a manager or admin', function () {
          beforeEach(function (done) {
            canManageRequests.and.returnValue(true);
            controller.loadContacts()
              .then(function (_loadedContacts_) {
                loadedContacts = _loadedContacts_;
              })
              .finally(done);
            $rootScope.$digest();
          });

          it('loads the filtered contacts', function () {
            expect(leaveCalendarServiceMock.instance.loadContactsByAssignationType).toHaveBeenCalledWith();
          });

          it('returns the filtered contacts', function () {
            expect(loadedContacts).toEqual(leaveCalendarServiceMock.data.filteredContacts);
          });
        });

        describe('when the component should only display a single contact', function () {
          beforeEach(function () {
            vm.displaySingleContact = true;
            vm.contactId = _.uniqueId();
            vm.lookupContacts = [];

            controller.loadContacts();
          });

          it('only loads the information for the given contact', function () {
            expect(vm.lookupContacts).toEqual([{ id: vm.contactId }]);
            expect(leaveCalendarServiceMock.instance.loadFilteredContacts).toHaveBeenCalledWith();
          });
        });
      });

      function initController () {
        vm = {
          displaySingleContact: false,
          contactId: contactId,
          filters: { userSettings: {} },
          canManageRequests: canManageRequests
        };
        controller = $controller('LeaveCalendarStaffController').init(vm);
      }
    });
  });
}(CRM));
