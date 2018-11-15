/* eslint-env amd, jasmine */

define([
  'leave-absences/mocks/data/absence-period.data',
  'common/angular',
  'common/angularMocks',
  'job-contract/job-contract.module',
  'leave-absences/shared/apis/absence-period.api',
  'leave-absences/shared/models/absence-period.model'
], function (absencePeriodData) {
  'use strict';

  describe('utilsService', function () {
    var $httpBackend, $provide, $rootScope, $q, $uibModal, utilsService, apiService,
      AbsencePeriod, $window;

    beforeEach(module('job-contract', 'job-contract.templates', 'leave-absences.models', function (_$provide_, $qProvider) {
      $provide = _$provide_;
      $provide.value('$window', {
        location: {
          assign: jasmine.createSpy('spy')
        }
      });
      $qProvider.errorOnUnhandledRejections(false);
    }));

    beforeEach(inject(function (_$httpBackend_, _$rootScope_, _$q_, _$uibModal_, _$window_, _utilsService_, _apiService_, _AbsencePeriod_) {
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;
      $q = _$q_;
      $uibModal = _$uibModal_;
      $window = _$window_;
      utilsService = _utilsService_;
      apiService = _apiService_;
      AbsencePeriod = _AbsencePeriod_;
    }));

    beforeEach(function () {
      spyOn(apiService, 'resource').and.callFake(function () { return { get: function () {} }; });

      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(200);
    });

    describe('getAbsenceType', function () {
      beforeEach(function () {
        utilsService.getAbsenceTypes();
      });

      it('returns the id, name, and title of the absence types', function () {
        expect(apiService.resource).toHaveBeenCalledWith('AbsenceType', 'get', { return: 'id,title,default_entitlement,add_public_holiday_to_entitlement' });
      });
    });

    describe('getHoursLocation', function () {
      beforeEach(function () {
        utilsService.getHoursLocation();
      });

      it('returns only the active hours/location entries', function () {
        expect(apiService.resource).toHaveBeenCalledWith('HRHoursLocation', 'get', { sequential: 1, is_active: 1 });
      });
    });

    describe('getPayScaleGrade', function () {
      beforeEach(function () {
        utilsService.getPayScaleGrade();
      });

      it('returns only the active pay scale entries', function () {
        expect(apiService.resource).toHaveBeenCalledWith('HRPayScale', 'get', { sequential: 1, is_active: 1 });
      });
    });

    describe('getOptionValues', function () {
      var optionValueApiParams;
      var groupNames;

      beforeEach(function () {
        groupNames = 'hrjc_health_insurance_provider';
        optionValueApiParams = {
          'sequential': 1,
          'is_active': 1,
          'option_group_id.name': { 'IN': [groupNames] },
          'return': [ 'id', 'label', 'weight', 'value', 'is_active', 'option_group_id', 'option_group_id.name' ]
        };

        utilsService.getOptionValues([groupNames]);
      });

      it('returns only the active option value entries', function () {
        expect(apiService.resource).toHaveBeenCalledWith('OptionValue', 'get', optionValueApiParams);
      });
    });

    describe('getManageEntitlementsPageURL', function () {
      it('returns an URL to the Manage Entitlements page', function () {
        var url = utilsService.getManageEntitlementsPageURL(1);
        expect(url).toContain('index.php?q=civicrm/admin/leaveandabsences/periods/manage_entitlements');
      });

      it('adds the contact ID to the cid parameter of the URL', function () {
        var contactId = 1;
        var url = utilsService.getManageEntitlementsPageURL(contactId);
        expect(url).toContain('&cid=' + contactId);
      });
    });

    describe('updateEntitlements()', function () {
      describe('when absence periods are available', function () {
        beforeEach(function () {
          spyOn(AbsencePeriod, 'all').and.returnValue($q.resolve(absencePeriodData.all().values));
        });

        describe('and user confirms to update entitlements', function () {
          var contactID = '101';

          beforeEach(function () {
            mockModalInstanceAndReturn($q.resolve());

            utilsService.updateEntitlements(contactID);
            $rootScope.$digest();
          });

          it('redirects to entitlement updation page for the current user', function () {
            var urlForManageEntitlements = utilsService.getManageEntitlementsPageURL(contactID);

            expect($window.location.assign).toHaveBeenCalledWith(urlForManageEntitlements);
          });
        });

        describe('and user does not confirm to update entitlements', function () {
          beforeEach(function () {
            mockModalInstanceAndReturn($q.reject());

            utilsService.updateEntitlements();
            $rootScope.$digest();
          });

          it('does not redirect to entitlement updation page for the current user', function () {
            expect($window.location.assign).not.toHaveBeenCalled();
          });
        });

        function mockModalInstanceAndReturn (modalReturnValue) {
          var mockModalInstance = { result: modalReturnValue };
          spyOn(mockModalInstance.result, 'then').and.callThrough();
          spyOn($uibModal, 'open').and.returnValue(mockModalInstance);
        }
      });

      describe('when absence periods are not available', function () {
        beforeEach(function () {
          spyOn(AbsencePeriod, 'all').and.returnValue($q.resolve([]));
          utilsService.updateEntitlements();
          $rootScope.$digest();
        });

        it('does not show confirmation window to update entitlements', function () {
          expect($window.location.assign).not.toHaveBeenCalled();
        });
      });
    });
  });
});
