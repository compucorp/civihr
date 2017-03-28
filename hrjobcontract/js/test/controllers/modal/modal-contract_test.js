define([
  'job-contract/app'
], function() {
  'use strict';

  describe('ModalContractCtrl', function() {
    var ctrl, $rootScope, $controller, $scope, $q, $uibModal, $httpBackend, $uibModalInstanceMock,
      $uibModalMock, entity, ContractDetailsService;

    beforeEach(module('hrjc'));

    beforeEach(inject(function(_$controller_, _$rootScope_, _$httpBackend_, _$q_,
      _ContractDetailsService_) {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      $httpBackend = _$httpBackend_;
      ContractDetailsService = _ContractDetailsService_;
      $q = _$q_;
    }));

    beforeEach(function() {
      $httpBackend.whenGET(/action=validatedates&entity=HRJobDetails/).respond({ success: true });
      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond({});
      $httpBackend.whenPOST(/action=create&entity/).respond({});
      $httpBackend.whenPOST(/action=replace&entity/).respond({});
      $httpBackend.whenGET(/views.*/).respond({});
    });

    beforeEach(function() {
      mockObjects()
      makeController();
    });

    beforeEach(function() {
      callSpies();
    });

    describe("After call to save() function", function() {

      beforeEach(function() {
        $scope.save();
        $rootScope.$digest();
      });

      it("calls to validate the dates form ContractDetailsService", function() {
        expect(ContractDetailsService.validateDates).toHaveBeenCalled();

        ContractDetailsService.validateDates().then(function(result) {
          expect(result.success).toBe(true);
        });
      });

      it("gets confirmation fron user to save contract data", function() {
        expect($uibModalMock.open).toHaveBeenCalled();

        $uibModalMock.open().result.then(function(data) {
          expect(data).toBe('edit');
        });
      });

      it("sets contract period_end_date to ''  if it was null", function() {
        expect($scope.entity.details.period_end_date).toBe('');
      });
    });

    describe("When period_end_date is undefined", function() {
      beforeEach(function() {
        $scope.entity.details.period_end_date = undefined;
        $scope.save();
        $rootScope.$digest();
      });

      it("sets contract period_end_date to '' ", function() {
        expect($scope.entity.details.period_end_date).toBe('');
      });
    });

    // $uibModalInstanceSpy for $uibModalInstance.opened.then()
    $uibModalInstanceMock = {
      opened: {
        then: function() {
          jasmine.createSpy('modalInstance.opened');
        }
      }
    };

    // $uibModalSpy for $modal.open()
    $uibModalMock = {
      open: function() {
        return {
          result: {
            then: function(callback) {
              callback("edit");
            }
          }
        }
      }
    };

    function makeController() {
      $scope = $rootScope.$new();
      ctrl = $controller('ModalContractCtrl', {
        $scope: $scope,
        $uibModal: $uibModalMock,
        $uibModalInstance: $uibModalInstanceMock,
        ContractDetailsService: ContractDetailsService,
        action: 'edit',
        entity: entity,
        content: {
          allowSave: true
        },
        files: {},
        utils: {
          contractListLen: 1
        }
      });
    }

    function callSpies() {
      spyOn(ContractDetailsService, "validateDates").and.callFake(function() {
        var deferred = $q.defer();
        deferred.resolve({
          success: true
        });

        return deferred.promise;
      });

      spyOn($uibModalMock, "open").and.callThrough();
    }

    function mockObjects() {
      entity = {
        contract: {
          id: '1',
          contact_id: "04",
          deleted: "0",
          is_current: "1",
          is_primary: "1"
        },
        details: {
          id: "60",
          position: "Test-added",
          title: "Test-added",
          funding_notes: null,
          contract_type: "Apprentice",
          period_start_date: "2017-03-28",
          period_end_date: null,
          end_reason: "1",
          notice_amount: null,
          notice_unit: null,
          notice_amount_employee: null,
          notice_unit_employee: null,
          location: "Headquarters",
          jobcontract_revision_id: "60"
        },
        hour: {},
        pay: {},
        leave: [],
        health: {},
        pension: {}
      };
    }
  });
});
