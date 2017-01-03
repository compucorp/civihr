define([
    'common/moment',
    'common/angularMocks',
    'job-contract/app',
    'job-contract/controllers/contract'
], function () {
    'use strict';

    describe('ContractCtrl', function () {
        var ctrl, $rootScope, $scope, $controller, ContractService;

        beforeEach(module('hrjc'));
        beforeEach(inject(function (_$controller_, _$rootScope_, _ContractService_) {

          $controller = _$controller_;
          $rootScope = _$rootScope_;
          ContractService = _ContractService_;

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

          $scope.model =  {"details":{"id":null,"position":"","title":"","funding_notes":"","contract_type":"","period_start_date":"","period_end_date":"","end_reason":"","notice_amount":"","notice_unit":"","notice_amount_employee":"","notice_unit_employee":"","location":null,"jobcontract_revision_id":null},"hour":{"id":null,"location_standard_hours":"","hours_type":"","hours_amount":"","hours_unit":"","hours_fte":"","fte_num":"","fte_denom":"","jobcontract_revision_id":null},"pay":{"id":null,"pay_scale":"","is_paid":"","pay_amount":"","pay_unit":"","pay_currency":"","pay_annualized_est":"","pay_is_auto_est":"","annual_benefits":[],"annual_deductions":[],"pay_cycle":"","pay_per_cycle_gross":"","pay_per_cycle_net":"","jobcontract_revision_id":null},"health":{"id":null,"provider":"","plan_type":"","description":"","dependents":"","provider_life_insurance":"","plan_type_life_insurance":"","description_life_insurance":"","dependents_life_insurance":"","jobcontract_revision_id":null},"pension":{"id":null,"is_enrolled":"","ee_contrib_pct":"","er_contrib_pct":"","pension_type":"","ee_contrib_abs":"","ee_evidence_note":"","jobcontract_revision_id":null},"leave":[{"id":null,"leave_type":"1","leave_amount":0,"add_public_holidays":"","jobcontract_revision_id":null},{"id":null,"leave_type":"2","leave_amount":0,"add_public_holidays":"","jobcontract_revision_id":null},{"id":null,"leave_type":"3","leave_amount":0,"add_public_holidays":"","jobcontract_revision_id":null},{"id":null,"leave_type":"4","leave_amount":0,"add_public_holidays":"","jobcontract_revision_id":null},{"id":null,"leave_type":"5","leave_amount":0,"add_public_holidays":"","jobcontract_revision_id":null},{"id":null,"leave_type":"6","leave_amount":0,"add_public_holidays":"","jobcontract_revision_id":null}]}

          ctrl = $controller('ContractCtrl', {
            $scope: $scope,
            ContractService: ContractService
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
