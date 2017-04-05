define([
  'job-contract/app'
], function() {
  'use strict';

  describe('ContractListCtrl', function() {
    var ctrl, $rootScope, $controller, $scope, $q, $httpBackend, ContractHealthService;

    beforeEach(module('hrjc'));

    beforeEach(module(function($provide) {
      $provide.factory('ContractHealthService', function() {
        return {
          getOptions: function() {},
          getFields: function() {}
        }
      });
    }));

    beforeEach(inject(function(_$controller_, _$rootScope_, _$q_, _$httpBackend_, _ContractHealthService_) {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      $httpBackend = _$httpBackend_;
      $q = _$q_;
      ContractHealthService = _ContractHealthService_;
      contractHealthServiceSpy()
      makeController();
    }));

    beforeEach(function() {
      $httpBackend.whenGET(/action=get&entity=HRHoursLocation/).respond({});
      $httpBackend.whenGET(/action=get&entity=HRPayScale/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobDetails/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobHour/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobPay/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobLeave/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobHealth/).respond({});
      $httpBackend.whenGET(/action=getfields&entity=HRJobPension/).respond({});
      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond({});
      $httpBackend.whenGET(/action=getoptions&entity=HRJobHealth/).respond({});
      $httpBackend.whenGET(/views.*/).respond({});
    });

    describe("modalContract()", function() {
      describe("when clicking Add New Job Contract Button", function () {
        beforeEach(function() {
          $rootScope.$digest();
        });

        beforeEach(function() {
          var action = "new",
          health = {};

          health.plan_type = {};
          health.plan_type_life_insurance = {};
          $rootScope.options = {
            health: health
          };
          $scope.modalContract(action);
          $rootScope.$digest();
        });

        var result = {
          Family: "Family",
          Individual: "Individual"
        }

        it("calls getOptions() form ContractHealthService", function() {
          expect(ContractHealthService.getOptions).toHaveBeenCalled();
        });

        it("fetches health insurance plan types", function() {
          expect($rootScope.options.health.plan_type).toEqual(result)
        });

        it("fetches life insurance plan types", function() {
          expect($rootScope.options.health.plan_type_life_insurance).toEqual(result)
        });

      })
    });

    function makeController() {
      $scope = $rootScope.$new();
      ctrl = $controller('ContractListCtrl', {
        $scope: $scope,
        $rootScope: $rootScope,
        contractList: []
      });
    }

    function contractHealthServiceSpy() {
      spyOn(ContractHealthService, "getOptions").and.callFake(function() {
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
