define([
  'job-contract/app'
], function() {
  'use strict';

  describe('ModalContractNewCtrl', function () {
    var ctrl, $rootScope, $controller, $scope, $q, $httpBackend, $uibModalInstanceMock, ContractHealthService;

    beforeEach(module('hrjc'));

    beforeEach(module(function ($provide) {
      $provide.factory('ContractHealthService', function () {
        return {
          getOptions: function () {}
        }
      });
    }));

    beforeEach(inject(function (_$controller_, _$rootScope_, _$httpBackend_, _$q_,
      _ContractDetailsService_, _ContractHealthService_) {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      $httpBackend = _$httpBackend_;
      ContractHealthService = _ContractHealthService_;
      $q = _$q_;
    }));

    beforeEach(function () {
      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond({});
      $httpBackend.whenGET(/action=get&entity=HRHoursLocation/).respond({});
      $httpBackend.whenGET(/action=get&entity=HRPayScale/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobDetails/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobHour/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobPay/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobLeave/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobHealth/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobPension/).respond({});
      $httpBackend.whenGET(/action=getoptions&entity=HRJobHealth/).respond({});
      $httpBackend.whenGET(/views.*/).respond({});
    });

    beforeEach(function () {
      var health = {};

      $rootScope.$digest();
      health.plan_type = {};
      health.plan_type_life_insurance = {};
      $rootScope.options = {
        health: health
      };
    });

    beforeEach(function () {
      mockUIBModalInstance();
      contractHealthServiceSpy();
      makeController();
    });

    describe("init()", function () {
      describe("when ModalContractNewCtrl is initialized", function () {
        beforeEach(function () {
          $rootScope.$digest();
        });

        var result = {
          Family: "Family",
          Individual: "Individual"
        };

        it("calls getOptions() form ContractHealthService", function () {
          expect(ContractHealthService.getOptions).toHaveBeenCalled();
        });

        it("fetches health insurance plan types", function () {
          expect($rootScope.options.health.plan_type).toEqual(result)
        });

        it("fetches life insurance plan types", function () {
          expect($rootScope.options.health.plan_type_life_insurance).toEqual(result)
        });
      })
    });

    function makeController() {
      $scope = $rootScope.$new();
      ctrl = $controller('ModalContractNewCtrl', {
        $scope: $scope,
        $rootScope: $rootScope,
        model: {},
        $uibModalInstance: $uibModalInstanceMock,
        utils: {
          contractListLen: 1
        }
      });
    }

    function mockUIBModalInstance() {
      $uibModalInstanceMock = {
        opened: {
          then: jasmine.createSpy()
        }
      };
    }

    function contractHealthServiceSpy() {
      spyOn(ContractHealthService, "getOptions").and.callFake(function () {
        return $q.resolve([
          {
            "key": "Family",
            "value": "Family"
          },
          {
            "key": "Individual",
            "value": "Individual"
          }
        ]);
      })
    }
  });
});
