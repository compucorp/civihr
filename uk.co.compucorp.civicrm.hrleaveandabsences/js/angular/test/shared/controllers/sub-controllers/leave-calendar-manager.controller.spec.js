/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'leave-absences/my-leave/app'
  ], function () {
    'use strict';

    describe('LeaveCalendarManagerController', function () {
      var $controller, $log, $provide, $rootScope, Contact, ContactInstance, controller, realContactInstance;
      var contactId = CRM.vars.leaveAndAbsences.contactId;

      beforeEach(module('my-leave', function (_$provide_) {
        $provide = _$provide_;
      }));

      beforeEach(inject(['api.contact.mock', function (ContactAPIMock) {
        $provide.value('api.contact', ContactAPIMock);
      }]));

      beforeEach(inject(function (_$controller_, _$log_, _$rootScope_, _Contact_, _ContactInstance_) {
        $controller = _$controller_;
        $log = _$log_;
        $rootScope = _$rootScope_;
        Contact = _Contact_;
        ContactInstance = _ContactInstance_;

        spyOn($log, 'debug');
        spyOn(Contact, 'all').and.callThrough();
        spyOnContactInstance();

        initController();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      describe('loadContacts()', function () {
        beforeEach(function () {
          controller.loadContacts();
          $rootScope.$digest();
        });

        it('gets the leave managees of the current contact', function () {
          expect(realContactInstance.leaveManagees).toHaveBeenCalled();
        });

        it('filters the contact using the filters selected by the user', function () {
          expect(Object.keys(Contact.all.calls.mostRecent().args[0])).toContain('department');
          expect(Object.keys(Contact.all.calls.mostRecent().args[0])).toContain('level_type');
          expect(Object.keys(Contact.all.calls.mostRecent().args[0])).toContain('location');
          expect(Object.keys(Contact.all.calls.mostRecent().args[0])).toContain('region');
          expect(Object.keys(Contact.all.calls.mostRecent().args[0])).toContain('id');
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
        controller = $controller('LeaveCalendarManagerController').init({
          contactId: contactId,
          filters: { userSettings: {} }
        });
      }
    });
  });
}(CRM));
