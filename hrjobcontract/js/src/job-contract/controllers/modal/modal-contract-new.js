define([
  'common/moment',
  'common/angular',
  'job-contract/controllers/controllers',
  'job-contract/services/contract',
  'job-contract/services/contract-details',
  'job-contract/services/contract-hour',
  'job-contract/services/contract-pay',
  'job-contract/services/contract-leave',
  'job-contract/services/contract-health',
  'job-contract/services/contract-pension',
  'job-contract/services/contract-files',
  'job-contract/services/utils',
  'common/services/pub-sub',
], function(moment, angular, controllers) {
  'use strict';

  controllers.controller('ModalContractNewCtrl', ['$rootScope', '$scope', '$uibModalInstance', '$q', '$uibModal', '$rootElement', '$sce',
    'Contract', 'ContractService', 'ContractDetailsService', 'ContractHourService', 'ContractPayService', 'ContractLeaveService',
    'ContractHealthService', 'ContractPensionService', 'ContractFilesService', 'model', 'UtilsService', 'utils',
    'settings', '$log', 'pubSub',
    function($rootScope, $scope, $modalInstance, $q, $modal, $rootElement, $sce, Contract, ContractService, ContractDetailsService,
      ContractHourService, ContractPayService, ContractLeaveService, ContractHealthService, ContractPensionService,
      ContractFilesService, model, UtilsService, utils, settings, $log, pubSub) {
      $log.debug('Controller: ModalContractNewCtrl');

      $scope.allowSave = true;
      $scope.action = 'new';
      $scope.copy = {
        close: 'Cancel',
        save: 'Add New Job Contract',
        title: 'Add New Job Contract'
      };
      $scope.entity = {};
      $scope.isDisabled = false;
      $scope.showIsPrimary = utils.contractListLen;

      $scope.fileMaxSize = settings.CRM.maxFileSize || 0;
      $scope.uploader = {
        details: {
          contract_file: ContractFilesService.uploader('civicrm_hrjobcontract_details')
        },
        pension: {
          evidence_file: ContractFilesService.uploader('civicrm_hrjobcontract_pension', 1)
        }
      };
      $scope.utils = utils;

      angular.copy(model, $scope.entity);
      $scope.entity.contract = {
        is_primary: 0
      };

      $scope.tooltips = {
        fileSize: $sce.trustAsHtml('<p>' +
          'THE FILE IS TOO LARGE AND CANNOT BE UPLOADED. PLEASE REDUCE THE SIZE OF THE FILE AND TRY AGAIN.' +
          '</p>'),
        fte: $sce.trustAsHtml('<div>' +
          '<strong>FTE</strong> stands for' +
          'Full Time Equivalent. This is a useful measure for' +
          'an organisation that has peopleworking part-time.' +
          'For a full-time person, FTE is always equal to' +
          '1.0, whereas for a part-time person, the FTE will represent' +
          'the fraction of standard hours that the person works on a' +
          'regular basis.<br>' +
          'E.g. if the standard working day at an organisation' +
          'comprises of 8 hours, then a person who regularly works for' +
          '8 hours each day would be considered to be full- time and' +
          'would have an FTE value of 1.0. A person who regularly works' +
          'for only 4 hours each day would be considered to be a' +
          'part-time person and would have an FTE value of 0.5. If the' +
          'organisation had 10 people, each with an FTE of 1.0 the' +
          'actual headcount of full-time people would be 10 and the' +
          'FTE headcount (equal to actual headcount multiplied by the' +
          'FTE value) would also be 10. However, if the organisation' +
          'had another 10 people who each worked part-time with an FTE' +
          'value of 0.5 the actual headcount of part-time people would' +
          'be 10 while the FTE headcount would only be 5. Thus for an' +
          'organisation that had a total of 10 full-time people, and 10' +
          'part-time people (each with an FTE of 0.5) the actual' +
          'headcount for the organisation would be 20 while the FTE' +
          'headcount would be 15.' +
          '</div>')
      };

      // Init
      (function init() {
        // Fetch updated Health and Life Insurance Plan Types
        $q.all([
          { name: "hrjobcontract_health_health_plan_type", key: 'plan_type' },
          { name: "hrjobcontract_health_life_insurance_plan_type", key: 'plan_type_life_insurance' }
        ].map(function (planTypeData) {
          ContractHealthService.getOptions(planTypeData.name, true)
          .then(function (planTypes) {
            $rootScope.options.health[planTypeData.key] = _.transform(planTypes, function(acc, type) {
              acc[type.key] = type.value;
            }, {});
          });
        }));

        angular.forEach($scope.uploader, function(entity){
          angular.forEach(entity, function(field){
            field.onAfterAddingAll = function(){
              $scope.filesValidate();
            }
          });
        });
      }());

      $scope.filesValidate = function() {
        var entityName,
          fieldName,
          fileMaxSize = $scope.fileMaxSize,
          uploader = $scope.uploader,
          uploaderEntity,
          uploaderEntityField,
          uploaderEntityFieldQueue,
          isValid = true,
          i, len;

        for (entityName in uploader) {
          uploaderEntity = uploader[entityName];

          for (fieldName in uploaderEntity) {
            uploaderEntityField = uploaderEntity[fieldName],
              uploaderEntityFieldQueue = uploaderEntityField.queue,
              i = 0, len = uploaderEntityFieldQueue.length;

            for (; i < len && isValid; i++) {
              isValid = uploaderEntityFieldQueue[i].file.size < fileMaxSize;
            }
          }
        }

        $scope.contractForm.$setValidity('maxFileSize', isValid);

      };

      $scope.cancel = function() {
        $modalInstance.dismiss('cancel');
      };

      $scope.save = function() {
        $scope.$broadcast('hrjc-loader-show');
        var contract = new Contract();

        ContractDetailsService.validateDates({
          contact_id: settings.contactId,
          period_start_date: $scope.entity.details.period_start_date,
          period_end_date: $scope.entity.details.period_end_date
        }).then(function(result) {
          if (result.success) {
            contract.$save({
              action: 'create',
              json: {
                sequential: 1,
                contact_id: settings.contactId,
                is_primary: utils.contractListLen ? $scope.entity.contract.is_primary : 1
              }
            }, function(data) {
              var contract = data.values[0],
                contractId = contract.id,
                entityDetails = angular.copy($scope.entity.details),
                entityHour = $scope.entity.hour,
                entityPay = $scope.entity.pay,
                entityLeave = $scope.entity.leave,
                entityHealth = $scope.entity.health,
                entityPension = $scope.entity.pension,
                modalInstance,
                promiseContractNew,
                promiseUpload = [],
                uploader = $scope.uploader,
                revisionId;

              contract.is_current = !entityDetails.period_end_date || moment().diff(entityDetails.period_end_date, "day") <= 0;

              UtilsService.prepareEntityIds(entityDetails, contractId);

              ContractDetailsService.save(entityDetails).then(function(results) {
                revisionId = results.jobcontract_revision_id;
              }, function(reason) {
                CRM.alert(reason, 'Error', 'error');
                ContractService.delete(contractId);
                $modalInstance.dismiss();
                return $q.reject();
              }).then(function() {

                angular.forEach($scope.entity, function(entity) {
                  UtilsService.prepareEntityIds(entity, contractId, revisionId);
                });

                promiseContractNew = [
                  ContractHourService.save(entityHour),
                  ContractPayService.save(entityPay),
                  ContractLeaveService.save(entityLeave),
                  ContractHealthService.save(entityHealth),
                  ContractPensionService.save(entityPension)
                ];

                if ($scope.uploader.details.contract_file.queue.length) {
                  promiseUpload.push(ContractFilesService.upload(uploader.details.contract_file, revisionId));
                }

                if ($scope.uploader.pension.evidence_file.queue.length) {
                  promiseUpload.push(ContractFilesService.upload(uploader.pension.evidence_file, revisionId));
                }

                if (promiseUpload.length) {
                  modalInstance = $modal.open({
                    appendTo: $rootElement.find('div').eq(0),
                    templateUrl: settings.pathApp + 'views/modalProgress.html',
                    size: 'sm',
                    controller: 'ModalProgressCtrl',
                    resolve: {
                      uploader: function() {
                        return uploader;
                      },
                      promiseFilesUpload: function() {
                        return promiseUpload;
                      }
                    }
                  });

                  promiseContractNew.push(modalInstance.result);
                }

                return $q.all(promiseContractNew);
              }, function(reason) {
                CRM.alert(reason, 'Error', 'error');
                $modalInstance.dismiss();
                return $q.reject();
              }).then(function() {
                $scope.$broadcast('hrjc-loader-hide');
                $modalInstance.close(contract);

                pubSub.publish('contract:created', settings.contactId);
                pubSub.publish('contract-refresh');
              });

            }, function(reason) {
              $scope.$broadcast('hrjc-loader-hide');
              $modalInstance.dismiss();
              CRM.alert((reason.statusText || 'Unknown error'), 'Error', 'error');
              return $q.reject();
            });
          } else {
            CRM.alert(result.message, 'Error', 'error');
            $scope.$broadcast('hrjc-loader-hide');
          }
        }, function(reason) {});
      };
    }
  ]);
});
