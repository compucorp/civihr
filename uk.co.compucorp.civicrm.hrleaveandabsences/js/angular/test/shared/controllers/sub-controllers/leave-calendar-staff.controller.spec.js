/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'leave-absences/my-leave/app'
  ], function () {
    'use strict';

    describe('LeaveCalendarStaffController', function () {
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

        it('simply fetches the data of the current contact', function () {
          expect(Contact.all).toHaveBeenCalledWith({
            id: { in: [contactId] }
          });
        });
      });

      function initController () {
        controller = $controller('LeaveCalendarStaffController').init({
          contactId: contactId,
          filters: { userSettings: {} }
        });
      }
    });
  });
}(CRM));
