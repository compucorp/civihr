define([
    'common/moment',
    'common/angularMocks',
    'job-contract/app'
], function (moment) {
    'use strict';

    describe('ContractCtrl', function () {
        var ctrl, $rootScope, $controller, $scope, $modal, $q, $httpBackend;

        beforeEach(module('hrjc', 'job-contract.templates'));
        beforeEach(inject(function (_$controller_, _$rootScope_, _$uibModal_, _$q_, _$httpBackend_) {
          $controller = _$controller_;
          $rootScope = _$rootScope_;
          $q = _$q_;
          $httpBackend = _$httpBackend_;
          $modal = _$uibModal_;

          $httpBackend.whenGET(/action=getfulldetails&entity=HRJobContract/).respond({});
          $httpBackend.whenGET(/action=getcurrentcontract&entity=HRJobContract/).respond({});
          $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond({});

          makeController();
        }));


        describe("Update contract based on new end date", function () {
          describe("When end date is past", function () {
            beforeEach(function () {
              var date = moment().day(-7); // Seven days ago
              createModalSpy(date);
              $scope.modalContract("edit");
              $rootScope.$digest();
            });

            it("Marks the contract as past", function () {
              expect($scope.$parent.contract.is_current).toBe("0");
            });

          });

          describe("When end date is today", function () {
            beforeEach(function () {
              var date = moment();
              createModalSpy(date);
              $scope.modalContract("edit");
              $rootScope.$digest();
            });

            it("Marks the contract as current", function () {
              expect($scope.$parent.contract.is_current).toBe("1");
            });
          });

          describe("When date is future", function () {
            beforeEach(function () {
              var date = moment().day(7); // Seven days from now
              createModalSpy(date);
              $scope.modalContract("edit");
              $rootScope.$digest();
            });

            it("Marks the contract as current", function () {
              expect($scope.$parent.contract.is_current).toBe("1");
            });
          });
        });

        function makeController() {
          $scope = $rootScope.$new();

          $scope.contract = {
            id: '1',
            contact_id: "04",
            deleted: "0",
            is_current: "1",
            is_primary: "1"
          };
          $scope.details = {};
          $scope.pay = {};
          $scope.hour = {};
          $scope.health = {};
          $scope.leave = [];
          $scope.$parent.contract = {
            id: '1',
            contact_id: "84",
            deleted: "0",
            is_current: "0",
            is_primary: "1"
          };
          $scope.pension = {};
          $scope.$parent.contractCurrent = [];
          $scope.$parent.contractPast = [];

          ctrl = $controller('ContractCtrl', {
            $scope: $scope,
            $modal: $modal
          });
        }

        function createModalSpy( newEndDate ) {
          spyOn($modal, "open").and.callFake(function() {
            return {
              result: $q.resolve({
                "files": false,
                "health": {},
                "contract": {
                  "id": "48",
                  "contact_id": "84",
                  "is_primary": "1",
                  "deleted": "0"
                },
                "pay": {},
                "hour": {},
                "leave": [],
                "details": {
                  "period_end_date": newEndDate,
                },
                "pension": {}
              })
            }
          });
        }
    });
});
