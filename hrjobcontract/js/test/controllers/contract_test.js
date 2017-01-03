define([
    'common/moment',
    'common/angularMocks',
    'job-contract/app',
    'job-contract/controllers/contract'
], function (moment) {
    'use strict';

    describe('ContractCtrl', function () {
        var ctrl, $rootScope, $controller, $scope;

        beforeEach(module('hrjc'));
        beforeEach(inject(function (_$controller_, _$rootScope_) {
          $controller = _$controller_;
          $rootScope = _$rootScope_;
          makeController();
        }));

        describe("Update contract based on new end date", function () {

          describe("When end date is past", function () {
            beforeEach(function () {
              var date = moment().day(-7); // Seven days ago
              $scope.updateContractList(date);
            });
            it("Make contract past", function () {
              expect($scope.$parent.contract.is_current).toBe("0");
            });
          });

          describe("When end date is today", function () {
            beforeEach(function () {
              var date = moment();
              $scope.updateContractList(date);
            });
            it("Make contract current", function () {
              expect($scope.$parent.contract.is_current).toBe("1");
            });
          });

          describe("When date is future", function () {
            beforeEach(function () {
              var date = moment().day(7); // Seven days from now
              $scope.updateContractList(date);
            });
            it("Make contract current", function () {
              expect($scope.$parent.contract.is_current).toBe("1");
            });
          });
        });

        function makeController() {
          $scope = $rootScope.$new();

          $scope.contract = {
            id: '1',
            contact_id: "84",
            deleted: "0",
            is_current: "0",
            is_primary: "1"
          };

          $scope.$parent.contract = {
            id: '1',
            contact_id: "84",
            deleted: "0",
            is_current: "0",
            is_primary: "1"
          };

          $scope.$parent.contractCurrent = [];
          $scope.$parent.contractPast = [];

          ctrl = $controller('ContractCtrl', {
            $scope: $scope
          });
        }
    });
});
