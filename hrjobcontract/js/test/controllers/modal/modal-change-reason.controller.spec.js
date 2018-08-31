/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/moment',
  'common/angularMocks',
  'job-contract/job-contract.module'
], function (angular, moment) {
  'use strict';

  describe('ModalChangeReasonController', function () {
    var $q, $rootScope, $scope, $controller, modalInstanceSpy, crmAngService,
      ContractServiceMock, ContractServiceSpy,
      ContractRevisionServiceMock, ContractRevisionServiceSpy, popupFormUrl;

    beforeEach(function () {
      // Need to skip the whole job-contract.run module
      // `contractService`, while mocked in this test suite, is also expected
      // to be used in the run() method, which leads to errors. Skipping the module
      // solves the issue
      angular.module('job-contract.run', []);

      module('job-contract');
      module(function ($provide) {
        $provide.value('contractRevisionService', ContractRevisionServiceMock);
        $provide.value('contractService', ContractServiceMock);
      });

      ContractRevisionServiceMock = {
        validateEffectiveDate: function () {}
      };
    });

    beforeEach(inject(function (_$controller_, _$rootScope_, _$q_, _crmAngService_, contractRevisionService, contractService) {
      $controller = _$controller_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      crmAngService = _crmAngService_;
      ContractRevisionServiceSpy = contractRevisionService;
      ContractServiceSpy = contractService;

      modalInstanceSpy = jasmine.createSpyObj('modalInstanceSpy', ['dismiss', 'close']);

      spyOn(window.CRM, 'alert');

      makeController();
    }));

    describe('when saving change reason form ', function () {
      it(' should have save() and cancel() fuctions defined', function () {
        expect($scope.save).toBeDefined();
        expect($scope.cancel).toBeDefined();
      });

      describe('if effective_date matches with available revisions ', function () {
        beforeEach(function () {
          spyOn(ContractRevisionServiceSpy, 'validateEffectiveDate').and.callFake(function () {
            var deferred = $q.defer();
            deferred.resolve({
              success: false,
              message: 'Sample alert message'
            });

            return deferred.promise;
          });

          $scope.save();
          $scope.$digest();
        });

        it('should call ValidateEffectiveDate form ContractRevisionService to validate effective_date', function () {
          expect(ContractRevisionServiceSpy.validateEffectiveDate).toHaveBeenCalled();
        });

        it('should not close Modal', function () {
          expect(modalInstanceSpy.close).not.toHaveBeenCalled();
        });

        it('should call alert with message', function () {
          expect(window.CRM.alert).toHaveBeenCalled();
        });
      });

      describe('if effective_date does not match with available revisions ', function () {
        beforeEach(function () {
          spyOn(ContractRevisionServiceSpy, 'validateEffectiveDate').and.callFake(function () {
            var deferred = $q.defer();
            deferred.resolve({
              success: true,
              message: ''
            });

            return deferred.promise;
          });

          $scope.save();
          $scope.$digest();
        });

        it('should close Modal ', function () {
          expect(modalInstanceSpy.close).toHaveBeenCalled();
        });

        it('should not call alert with message', function () {
          expect(window.CRM.alert).not.toHaveBeenCalled();
        });
      });
    });

    describe('when user clicks on the wrench icon', function () {
      popupFormUrl = '/civicrm/admin/options/hrjc_revision_change_reason?reset=1';

      beforeEach(function () {
        spyOn(crmAngService, 'loadForm').and.callFake(function () {
          return {
            on: function (event, callback) {
            }
          };
        });
        $scope.openRevisionChangeReasonEditor();
      });

      it('calls the crmAngService with the requested url', function () {
        expect(crmAngService.loadForm).toHaveBeenCalledWith(popupFormUrl);
      });
    });

    function makeController () {
      $scope = $rootScope.$new();

      $scope.copy = {};
      $scope.copy.title = 'Revision data';
      $scope.change_reason = '';
      $scope.effective_date = '';
      $scope.isPast = false;

      $controller('ModalChangeReasonController', {
        $scope: $rootScope,
        $uibModalInstance: modalInstanceSpy,
        content: 'some string',
        date: '',
        reasonId: '',
        settings: '',
        contractRevisionService: ContractRevisionServiceSpy,
        contractService: ContractServiceSpy
      });
    }
  });
});
