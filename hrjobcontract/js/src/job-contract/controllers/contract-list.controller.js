/* eslint-env amd */

define([
  'common/angular',
  'common/lodash',
  'job-contract/controllers/controllers',
  'job-contract/filters/get-obj-by-id',
  'job-contract/services/contract-details',
  'job-contract/services/contract-hour',
  'job-contract/services/contract-health',
  'job-contract/services/contract-leave',
  'job-contract/services/contract-pay',
  'job-contract/services/contract-pension',
  'job-contract/services/utils',
  'common/services/pub-sub'
], function (angular, _, controllers) {
  'use strict';

  controllers.controller('ContractListCtrl', ['$scope', '$rootElement', '$rootScope', '$uibModal', '$q', '$filter', '$sce',
    'contractList', 'ContractService', 'ContractDetailsService', 'ContractHourService', 'ContractPayService',
    'ContractLeaveService', 'ContractHealthService', 'ContractPensionService', 'UtilsService', 'settings', '$log', 'pubSub', '$window',
    function ($scope, $rootElement, $rootScope, $modal, $q, $filter, $sce, contractList, ContractService, ContractDetailsService,
      ContractHourService, ContractPayService, ContractLeaveService, ContractHealthService, ContractPensionService,
      UtilsService, settings, $log, pubSub, $window) {
      $log.debug('Controller: ContractListCtrl');

      var entityName;
      var entityServices = {
        details: ContractDetailsService,
        hour: ContractHourService,
        pay: ContractPayService,
        leave: ContractLeaveService,
        health: ContractHealthService,
        pension: ContractPensionService
      };
      var promiseUtils = {
        hoursLocation: UtilsService.getHoursLocation(),
        payScaleGrade: UtilsService.getPayScaleGrade(),
        absenceTypes: UtilsService.getAbsenceTypes()
      };
      var promiseFields = {};
      var promiseModel = {};

      $scope.contractListLoaded = false;
      $scope.contractCurrent = [];
      $scope.contractPast = [];
      $scope.utils = {
        contractListLen: contractList.length
      };

      $scope.tooltips = {
        changeContractTerms: $sce.trustAsHtml('<div>' +
          '<p class="text-left"><strong>Change Contract Terms:</strong><br>' +
          'When an employeees job or role changes, i.e. promotion, secondment or move,' +
          'you can use this wizard to update the details of the contract and record a new' +
          'revision of the contract. A contract history is kept so you can always see the' +
          'previous version of the contract.</p>' +
          '<p class="text-left"><strong>Correct an error on the contract record:</strong><br>' +
          'If you notice an issue or error with the job terms you can correct these without' +
          'creating a new job history record. These changes are not stored as a new revision' +
          'of the contract.</p>' +
          '</div>')
      };

      for (entityName in entityServices) {
        promiseFields[entityName] = entityServices[entityName].getFields();
      }

      $q.all(promiseFields).then(function (fields) {
        $scope.fields = fields;

        $log.debug('FIELDS:');
        $log.debug(fields);

        for (entityName in entityServices) {
          promiseModel[entityName] = entityServices[entityName].model(fields[entityName]);
        }

        return $q.all(promiseModel);
      }).then(function (model) {
        $scope.model = model;

        $log.debug('MODEL:');
        $log.debug(model);

        contractList = $filter('orderBy')(contractList, '-is_primary');

        angular.forEach(contractList, function (contract) {
          +contract.is_current ? $scope.contractCurrent.push(contract) : $scope.contractPast.push(contract);
        });

        $scope.$watchCollection('contractCurrent', function () {
          $scope.utils.contractListLen = $scope.contractCurrent.length + $scope.contractPast.length;
        });

        $scope.$watchCollection('contractPast', function () {
          $scope.utils.contractListLen = $scope.contractCurrent.length + $scope.contractPast.length;
        });

        $rootScope.$broadcast('hrjc-loader-hide');
        $scope.contractListLoaded = true;
      });

      $q.all(promiseUtils).then(function (utils) {
        angular.extend($scope.utils, utils);
      });

      $scope.toggleIsPrimary = function (contractId) {
        function unsetIsPrimary (contractArray) {
          var i = 0;
          var len = contractArray.length;

          for (i; i < len; i++) {
            if (+contractArray[i].id !== +contractId && +contractArray[i].is_primary) {
              contractArray[i].is_primary = '0';

              return contractArray[i].id;
            }
          }

          return null;
        }

        unsetIsPrimary($scope.contractCurrent) || unsetIsPrimary($scope.contractPast);

        ($filter('getObjById')($scope.contractCurrent, contractId) || $filter('getObjById')($scope.contractPast, contractId) || {}).is_primary = '1';

        $scope.contractCurrent = $filter('orderBy')($scope.contractCurrent, '-is_primary');
        $scope.contractPast = $filter('orderBy')($scope.contractPast, '-is_primary');
      };

      $scope.modalContract = function (action) {
        if (!action || action !== 'new') {
          return null;
        }

        var modalInstance;
        var options = {
          appendTo: $rootElement.find('div').eq(0),
          templateUrl: settings.pathApp + 'views/modalForm.html?v=2222',
          size: 'lg',
          controller: 'ModalContractNewCtrl',
          windowClass: 'modal-contract',
          resolve: {
            model: function () {
              return $scope.model;
            },
            utils: function () {
              return $q.all(angular.extend(promiseUtils, {
                contractListLen: $scope.utils.contractListLen
              }));
            }
          }
        };

        modalInstance = $modal.open(options);

        modalInstance.result.then(function (contract) {
          ContractService.updateHeaderInfo();
          +contract.is_current ? $scope.contractCurrent.push(contract) : $scope.contractPast.push(contract);

          if (+contract.is_primary) {
            $scope.toggleIsPrimary(contract.id);
          }

          $window.location.assign(UtilsService.getManageEntitlementsPageURL(contract.contact_id));
        });
      };

      $scope.delete = function (contractId) {
        function removeContractById (contractArray, id) {
          var i = 0;
          var len = contractArray.length;

          for (i; i < len; i++) {
            if (+contractArray[i].id === +id) {
              $scope.$emit('hrjc-loader-hide');
              contractArray.splice(i, 1);
              return id;
            }
          }

          return null;
        }

        var modalInstance = $modal.open({
          appendTo: $rootElement.find('div').eq(0),
          templateUrl: settings.pathApp + 'views/modalDialog.html',
          size: 'sm',
          controller: 'ModalDialogCtrl',
          resolve: {
            content: function () {
              return {
                msg: 'Are you sure you want to delete this job contract?'
              };
            }
          }
        });

        modalInstance.result.then(function (confirm) {
          if (confirm) {
            $scope.$emit('hrjc-loader-show');
            ContractService.delete(contractId).then(function (result) {
              if (!result.is_error) {
                ContractService.updateHeaderInfo();
                removeContractById($scope.contractCurrent, contractId) || removeContractById($scope.contractPast, contractId);
                pubSub.publish('Contract::deleted', {
                  contactId: settings.contactId,
                  contractId: contractId
                });
              }
            });
          }
        });
      };
    }
  ]);
});
