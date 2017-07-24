/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'leave-absences/my-leave/app'
  ], function () {
    'use strict';

    describe('LeaveCalendarAdminController', function () {
      var $controller, $log, Contact, controller;
      var contactId = CRM.vars.leaveAndAbsences.contactId;

      beforeEach(module('my-leave'));
      beforeEach(inject(function (_$controller_, _$log_, _Contact_) {
        $controller = _$controller_;
        $log = _$log_;
        Contact = _Contact_;

        spyOn($log, 'debug');
        spyOn(Contact, 'all').and.callThrough();

        initController();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      describe('loadContacts()', function () {
        beforeEach(function () {
          controller.loadContacts();
        });

        it('simply loads all the contacts', function () {
          expect(Contact.all).toHaveBeenCalled();
          expect(Contact.all.calls.mostRecent().args).toEqual([]);
        });
      });

      function initController () {
        controller = $controller('LeaveCalendarAdminController').init({ contactId: contactId });
      }
    });
  });
}(CRM));
