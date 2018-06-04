/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'leave-absences/mocks/services/leave-calendar.service.mock',
    'leave-absences/my-leave/app'
  ], function (leaveCalendarServiceMock) {
    'use strict';

    describe('LeaveCalendarManagerController', function () {
      var $controller, $log, $provide, $rootScope, Contact, ContactInstance, controller,
        realContactInstance, vm;
      var contactId = CRM.vars.leaveAndAbsences.contactId;

      beforeEach(module('my-leave', function (_$provide_) {
        $provide = _$provide_;
      }));

      beforeEach(inject(['api.contact.mock', function (ContactAPIMock) {
        $provide.value('api.contact', ContactAPIMock);
        $provide.value('LeaveCalendarService', leaveCalendarServiceMock.service);
      }]));

      beforeEach(inject(function (_$controller_, _$log_, $q, _$rootScope_, _Contact_, _ContactInstance_) {
        $controller = _$controller_;
        $log = _$log_;
        $rootScope = _$rootScope_;
        Contact = _Contact_;
        ContactInstance = _ContactInstance_;

        leaveCalendarServiceMock.setup($q);
        spyOn($log, 'debug');
        spyOn(Contact, 'all').and.callThrough();
        spyOnContactInstance();

        initController();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      it('initializes the leave calendar service', function () {
        expect(leaveCalendarServiceMock.service.init).toHaveBeenCalledWith(vm);
      });

      it('selects the assignee filter "People I approve" by default', function () {
        expect(vm.filters.userSettings.assignedTo.type).toBe('me');
      });

      describe('loadContacts()', function () {
        var contacts;

        beforeEach(function (done) {
          controller.loadContacts()
            .then(function (_contacts_) {
              contacts = _contacts_;
            })
            .finally(done);
          $rootScope.$digest();
        });

        it('gets the leave managees of the current contact', function () {
          expect(realContactInstance.leaveManagees).toHaveBeenCalled();
        });

        it('requests the filtered contacts', function () {
          expect(leaveCalendarServiceMock.instance.loadFilteredContacts).toHaveBeenCalledWith();
        });

        it('returns the filtered contacts', function () {
          expect(contacts).toEqual(leaveCalendarServiceMock.data.filteredContacts);
        });
      });

      /**
       * Mocks the `init` method of `ContactInstance` so that the
       * we can spy on the `leaveManagees()` method
       */
      function spyOnContactInstance () {
        realContactInstance = ContactInstance.init({ id: contactId });

        spyOn(ContactInstance, 'init').and.callFake(function () {
          if (typeof realContactInstance.leaveManagees.calls === 'undefined') {
            spyOn(realContactInstance, 'leaveManagees').and.callThrough();
          }

          return realContactInstance;
        });
      }

      function initController () {
        vm = {
          contactId: contactId,
          filters: { userSettings: {} },
          filtersByAssignee: [
            { type: 'me', label: 'People I approve' },
            { type: 'unassigned', label: 'People without approver' },
            { type: 'all', label: 'All' }
          ]
        };
        controller = $controller('LeaveCalendarManagerController').init(vm);
      }
    });
  });
}(CRM));
