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
        vm = {
          displaySingleContact: false,
          contactId: contactId,
          filters: { userSettings: {} },
          canManageRequests: canManageRequests
        };

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
        var scenarios = [
          { role: 'staff' },
          { role: 'manager' }
        ];

        scenarios.forEach(function (scenario) {
          describe('as a ' + scenario.role, function () {
            beforeEach(function (done) {
              vm.userPermissionRole = scenario.role;

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

            it('loads the look up contacts', function () {
              expect(leaveCalendarServiceMock.instance.loadLookUpContacts).toHaveBeenCalledWith();
            });

            it('stores a list of look up contacts', function () {
              expect(vm.lookupContacts).toEqual(leaveCalendarServiceMock.data.lookedUpContacts);
            });

            it('returns the filtered contacts', function () {
              expect(loadedContacts).toEqual(leaveCalendarServiceMock.data.filteredContacts);
            });
          });
        });

        describe('as an admin', function () {
          beforeEach(function (done) {
            vm.userPermissionRole = 'admin';
            controller.loadContacts()
              .then(function (_loadedContacts_) {
                loadedContacts = _loadedContacts_;
              })
              .finally(done);
            $rootScope.$digest();
          });

          it('loads the filtered contacts', function () {
            expect(leaveCalendarServiceMock.instance.loadContactsForAdmin).toHaveBeenCalledWith();
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
            initController();

            controller.loadContacts();
          });

          it('only loads the information for the given contact', function () {
            expect(vm.lookupContacts).toEqual([{ id: vm.contactId }]);
            expect(leaveCalendarServiceMock.instance.loadFilteredContacts).toHaveBeenCalledWith();
          });
        });
      });

      describe('when the component only displays a single contact', function () {
        beforeEach(function () {
          vm.displaySingleContact = true;
          initController();
        });

        it('does not show the filters', function () {
          expect(vm.showFilters).toEqual(false);
        });
      });

      /**
       * Initializes the leave calendar staff sub controller.
       */
      function initController () {
        controller = $controller('LeaveCalendarStaffController').init(vm);
      }
    });
  });
}(CRM));
