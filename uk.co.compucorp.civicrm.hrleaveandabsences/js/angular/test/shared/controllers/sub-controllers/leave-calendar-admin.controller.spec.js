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

      describe('loadContacts()', function () {
        var contacts;

        describe('when loading all of the contacts', function () {
          beforeEach(function (done) {
            vm.filters.userSettings.assignedTo.type = 'all';

            controller.loadContacts()
              .then(function (_contacts_) {
                contacts = _contacts_;
              })
              .finally(done);
            $rootScope.$digest();
          });

          it('requests the contact ids for contacts with valid contracts within the selected period', function () {
            expect(leaveCalendarServiceMock.instance.loadContactIdsToReduceTo).toHaveBeenCalledWith();
          });

          it('stores the contact ids to reduce to', function () {
            expect(vm.contactIdsToReduceTo).toEqual(leaveCalendarServiceMock.data.contactIdsToReduceTo);
          });

          it('requests contacts based on the selected indexes', function () {
            expect(leaveCalendarServiceMock.instance.loadFilteredContacts).toHaveBeenCalledWith();
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
          selectedPeriod: { start_date: '2016-01-01', end_date: '2016-12-31' }
        };
        controller = $controller('LeaveCalendarAdminController').init(vm);
      }
    });
  });
}(CRM));
