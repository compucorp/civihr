/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'leave-absences/mocks/services/leave-calendar.service.mock',
    'leave-absences/my-leave/app'
  ], function (leaveCalendarServiceMock) {
    'use strict';

    describe('LeaveCalendarAdminController', function () {
      var $controller, $log, $provide, $rootScope, Contact, controller, vm;
      var contactId = CRM.vars.leaveAndAbsences.contactId;

      beforeEach(module('my-leave', function (_$provide_) {
        $provide = _$provide_;
      }));

      beforeEach(inject(['api.contact.mock', function (ContactAPIMock) {
        $provide.value('api.contact', ContactAPIMock);
        $provide.value('LeaveCalendarService', leaveCalendarServiceMock.service);
      }]));

      beforeEach(inject(function (_$controller_, _$log_, $q, _$rootScope_, _Contact_) {
        $controller = _$controller_;
        $log = _$log_;
        $rootScope = _$rootScope_;
        Contact = _Contact_;

        spyOn($log, 'debug');
        spyOn(Contact, 'all').and.callThrough();
        leaveCalendarServiceMock.setup($q);

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

        describe('when loading all of the contacts', function () {
          beforeEach(function (done) {
            controller.loadContacts()
              .then(function (_contacts_) {
                contacts = _contacts_;
              })
              .finally(done);
            $rootScope.$digest();
          });

          it('requests contacts by assignation type', function () {
            expect(leaveCalendarServiceMock.instance.loadContactsForAdmin).toHaveBeenCalledWith();
          });

          it('returns the list of filtered contacts', function () {
            expect(contacts).toEqual(leaveCalendarServiceMock.data.filteredContacts);
          });
        });
      });

      function initController () {
        vm = {
          contactId: contactId,
          filters: { userSettings: {} },
          selectedPeriod: { start_date: '2016-01-01', end_date: '2016-12-31' },
          filtersByAssignee: [
            { type: 'me', label: 'People I approve' },
            { type: 'unassigned', label: 'People without approver' },
            { type: 'all', label: 'All' }
          ]
        };
        controller = $controller('LeaveCalendarAdminController').init(vm);
      }
    });
  });
}(CRM));
