/* eslint-env amd */

define([
  'common/angular',
  'common/lodash'
], function (angular, _) {
  'use strict';

  ContractListController.__name = 'ContractListController';
  ContractListController.$inject = [
    '$filter', '$log', '$q', '$rootElement', '$rootScope', '$sce', '$scope', '$window',
    '$uibModal', 'contractList', 'contractService', 'contractDetailsService',
    'contractHourService', 'contractPayService', 'contractLeaveService', 'contractHealthService',
    'contractPensionService', 'utilsService', 'settings', 'pubSub'
  ];

  function ContractListController ($filter, $log, $q, $rootElement, $rootScope,
    $sce, $scope, $window, $modal, contractList, contractService, contractDetailsService,
    contractHourService, contractPayService, contractLeaveService, contractHealthService,
    contractPensionService, utilsService, settings, pubSub) {
    $log.debug('Controller: ContractListController');

    var entityName;
    var promiseFields = {};
    var promiseModel = {};
    var entityServices = {
      details: contractDetailsService,
      hour: contractHourService,
      pay: contractPayService,
      leave: contractLeaveService,
      health: contractHealthService,
      pension: contractPensionService
    };
    var promiseUtils = {
      hoursLocation: utilsService.getHoursLocation(),
      payScaleGrade: utilsService.getPayScaleGrade(),
      absenceTypes: utilsService.getAbsenceTypes()
    };

    $scope.contractCurrent = [];
    $scope.contractListLoaded = false;
    $scope.contractPast = [];
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
    $scope.utils = {
      contractListLen: contractList.length
    };

    $scope.delete = deleteContract;
    $scope.modalContract = modalContract;
    $scope.toggleIsPrimary = toggleIsPrimary;

    (function init () {
      for (entityName in entityServices) {
        promiseFields[entityName] = entityServices[entityName].getFields();
      }

      $q.all(promiseFields)
        .then(function (fields) {
          $scope.fields = fields;

          $log.debug('FIELDS:');
          $log.debug(fields);

          for (entityName in entityServices) {
            promiseModel[entityName] = entityServices[entityName].model(fields[entityName]);
          }

          return $q.all(promiseModel);
        })
        .then(function (model) {
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
    }());

    function deleteContract (contractId) {
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
        controller: 'ModalDialogController',
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
          contractService.delete(contractId).then(function (result) {
            if (!result.is_error) {
              contractService.updateHeaderInfo();
              removeContractById($scope.contractCurrent, contractId) || removeContractById($scope.contractPast, contractId);
              pubSub.publish('Contract::deleted', {
                contactId: settings.contactId,
                contractId: contractId
              });
            }
          });
        }
      });
    }

    function modalContract (action) {
      if (!action || action !== 'new') {
        return null;
      }

      var modalInstance;
      var options = {
        appendTo: $rootElement.find('div').eq(0),
        templateUrl: settings.pathApp + 'views/modalForm.html?v=2222',
        size: 'lg',
        controller: 'ModalContractNewController',
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
        contractService.updateHeaderInfo();
        +contract.is_current ? $scope.contractCurrent.push(contract) : $scope.contractPast.push(contract);

        if (+contract.is_primary) {
          $scope.toggleIsPrimary(contract.id);
        }

        utilsService.updateEntitlements(contract.contact_id);
      });
    }

    function toggleIsPrimary (contractId) {
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
    }
  }

  return ContractListController;
});
