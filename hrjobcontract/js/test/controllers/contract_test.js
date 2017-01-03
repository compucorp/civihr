define([
    'common/moment',
    'common/angularMocks',
    'job-contract/app',
    'job-contract/controllers/contract'
], function () {
    'use strict';

    describe('ContractCtrl', function () {
        var ctrl, $rootScope, $scope, $controller;

        beforeEach(module('hrjc'));
        beforeEach(inject(function (_$controller_, _$rootScope_) {

          $controller = _$controller_;
          $rootScope = _$rootScope_;

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

        }));

        it('should make current contract', function () {
          var d = new Date().toISOString().split("T")[0];
          $scope.updateContractList(d);
          expect($scope.$parent.contract.is_current).toBe("1");
        });

        it('should make past contract', function () {
          $scope.updateContractList("2016-01-06");
          expect($scope.$parent.contract.is_current).toBe("0");
        });

    });
});
