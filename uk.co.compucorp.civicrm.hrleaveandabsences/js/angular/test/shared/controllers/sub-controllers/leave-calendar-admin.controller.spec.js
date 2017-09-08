/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'leave-absences/my-leave/app'
  ], function () {
    'use strict';

    describe('LeaveCalendarAdminController', function () {
      var $controller, $log, $provide, $rootScope, Contact, controller;
      var contactId = CRM.vars.leaveAndAbsences.contactId;

      beforeEach(module('my-leave', function (_$provide_) {
        $provide = _$provide_;
      }));

      beforeEach(inject(['api.contact.mock', function (ContactAPIMock) {
        $provide.value('api.contact', ContactAPIMock);
      }]));

      beforeEach(inject(function (_$controller_, _$log_, _$rootScope_, _Contact_) {
        $controller = _$controller_;
        $log = _$log_;
        $rootScope = _$rootScope_;
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
          $rootScope.$digest();
        });

        it('filters the contact using the filters selected by the user', function () {
          expect(Object.keys(Contact.all.calls.mostRecent().args[0])).toContain('department');
          expect(Object.keys(Contact.all.calls.mostRecent().args[0])).toContain('level_type');
          expect(Object.keys(Contact.all.calls.mostRecent().args[0])).toContain('location');
          expect(Object.keys(Contact.all.calls.mostRecent().args[0])).toContain('region');
          expect(Object.keys(Contact.all.calls.mostRecent().args[0])).toContain('id');
        });
      });

      function initController () {
        controller = $controller('LeaveCalendarAdminController').init({
          contactId: contactId,
          filters: { userSettings: {} },
          selectedPeriod: { start_date: '2016-01-01', end_date: '2016-12-31' }
        });
      }
    });
  });
}(CRM));
