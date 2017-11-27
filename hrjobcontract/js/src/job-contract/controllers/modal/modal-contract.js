/* eslint-env amd */

define([
  'common/angular',
  'common/lodash',
  'job-contract/controllers/controllers',
  'job-contract/services/contract',
  'job-contract/services/contract-revision',
  'job-contract/services/contract-details',
  'job-contract/services/contract-hour',
  'job-contract/services/contract-pay',
  'job-contract/services/contract-leave',
  'job-contract/services/contract-health',
  'job-contract/services/contract-pension',
  'job-contract/services/contract-files',
  'job-contract/services/utils',
  'common/services/pub-sub'
], function (angular, _, controllers) {
  'use strict';

  controllers.controller('ModalContractCtrl', ['$scope', '$uibModal', '$uibModalInstance', '$q', '$rootElement', '$rootScope', '$filter',
    'ContractService', 'ContractRevisionService', 'ContractDetailsService', 'ContractHourService', 'ContractPayService', 'ContractLeaveService',
    'ContractHealthService', 'ContractPensionService', 'ContractFilesService', 'action', 'entity',
    'content', 'files', 'UtilsService', 'utils', 'settings', '$log', 'pubSub',
    function ($scope, $modal, $modalInstance, $q, $rootElement, $rootScope, $filter, ContractService, ContractRevisionService,
      ContractDetailsService, ContractHourService, ContractPayService, ContractLeaveService, ContractHealthService,
      ContractPensionService, ContractFilesService, action, entity, content, files,
      UtilsService, utils, settings, $log, pubSub) {
      $log.debug('Controller: ModalContractCtrl');

      var copy = content.copy || {};

      copy.close = copy.close || 'Close';
      copy.save = copy.save || 'Save changes';
      copy.title = copy.title || 'Contract';

      $scope.action = action || 'view';
      $scope.allowSave = typeof content.allowSave !== 'undefined' ? content.allowSave : false;
      $scope.copy = copy;
      $scope.entity = {};
      $scope.fileMaxSize = settings.CRM.maxFileSize || 0;
      $scope.files = {};
      $scope.filesTrash = {};
      $scope.isDisabled = typeof content.isDisabled !== 'undefined' ? content.isDisabled : true;
      $scope.isPrimaryDisabled = +entity.contract.is_primary;
      $scope.showIsPrimary = utils.contractListLen > 1 && action !== 'change';
      $scope.uploader = {
        details: {
          contract_file: ContractFilesService.uploader('civicrm_hrjobcontract_details')
        },
        pension: {
          evidence_file: ContractFilesService.uploader('civicrm_hrjobcontract_pension', 1)
        }
      };
      $scope.utils = utils;

      angular.copy(entity, $scope.entity);
      angular.copy(files, $scope.files);
      $scope.entity.details.period_start_date = convertToDateObject($scope.entity.details.period_start_date);
      $scope.entity.details.period_end_date = convertToDateObject($scope.entity.details.period_end_date);

      // Init
      (function init () {
        angular.forEach($scope.files, function (entityFiles, entityName) {
          $scope.filesTrash[entityName] = [];
        });

        $modalInstance.opened.then(function () {
          $rootScope.$broadcast('hrjc-loader-hide');
        });

        angular.forEach($scope.uploader, function (entity) {
          angular.forEach(entity, function (field) {
            field.onAfterAddingAll = function () {
              $scope.filesValidate();
            };
          });
        });

        $rootScope.$broadcast('hrjc-loader-show');
        fetchInsurancePlanTypes().then(function () {
          $rootScope.$broadcast('hrjc-loader-hide');
        });
      }());

      $scope.cancel = function () {
        if (action === 'view' ||
          (angular.equals(entity, $scope.entity) && angular.equals(files, $scope.files) &&
            !$scope.uploader.details.contract_file.queue.length && !$scope.uploader.pension.evidence_file.queue.length)) {
          $scope.$broadcast('hrjc-loader-hide');
          $modalInstance.dismiss('cancel');
          return;
        }

        // DEBUG
        if (settings.debug) {
          angular.forEach(entity, function (entityData, entityName) {
            if (!angular.equals(entityData, $scope.entity[entityName])) {
              $log.debug('======================');
              $log.debug('Changed entity: ' + entityName);
              $log.debug('Before:');
              $log.debug(entityData);
              $log.debug('After:');
              $log.debug($scope.entity[entityName]);
            }
          });
        }

        var modalInstance = $modal.open({
          appendTo: $rootElement.find('div').eq(0),
          templateUrl: settings.pathApp + 'views/modalDialog.html?v=' + (new Date()).getTime(),
          size: 'sm',
          controller: 'ModalDialogCtrl',
          resolve: {
            content: function () {
              return {
                copyCancel: 'No',
                title: 'Alert',
                msg: 'Are you sure you want to cancel? Changes will be lost!'
              };
            }
          }
        });

        modalInstance.result.then(function (confirm) {
          if (confirm) {
            $scope.$broadcast('hrjc-loader-hide');
            $modalInstance.dismiss('cancel');
          }
        });
      };

      $scope.fileMoveToTrash = function (index, entityName) {
        var entityFiles = $scope.files[entityName];
        var entityFilesTrash = $scope.filesTrash[entityName];

        entityFilesTrash.push(entityFiles[index]);
        entityFiles.splice(index, 1);
      };

      $scope.filesValidate = function () {
        var entityName, fieldName, i, len, uploaderEntity, uploaderEntityField, uploaderEntityFieldQueue;
        var fileMaxSize = $scope.fileMaxSize;
        var uploader = $scope.uploader;
        var isValid = true;

        for (entityName in uploader) {
          uploaderEntity = uploader[entityName];

          for (fieldName in uploaderEntity) {
            i = 0;
            len = uploaderEntityFieldQueue.length;
            uploaderEntityField = uploaderEntity[fieldName];
            uploaderEntityFieldQueue = uploaderEntityField.queue;

            for (; i < len && isValid; i++) {
              isValid = uploaderEntityFieldQueue[i].file.size < fileMaxSize;
            }
          }
        }

        $scope.contractForm.$setValidity('maxFileSize', isValid);
      };

      if ($scope.allowSave) {
        $scope.save = function () {
          $scope.$broadcast('hrjc-loader-show');
          ContractDetailsService.validateDates({
            contact_id: settings.contactId,
            period_start_date: $scope.entity.details.period_start_date,
            period_end_date: $scope.entity.details.period_end_date,
            jobcontract_id: entity.contract.id
          }).then(function (result) {
            if (result.success) {
              confirmUpdateEntitlements()
                .then(function () {
                  processContractUpdate();
                });
            } else {
              CRM.alert(result.message, 'Error', 'error');
              $scope.$broadcast('hrjc-loader-hide');
            }
          }, function (reason) {});
          $scope.$broadcast('hrjc-loader-hide');
        };
      }

      function processContractUpdate () {
        if (angular.equals(entity, $scope.entity) &&
          angular.equals(files, $scope.files) &&
          !$scope.uploader.details.contract_file.queue.length &&
          !$scope.uploader.pension.evidence_file.queue.length) {
          $scope.$broadcast('hrjc-loader-hide');
          $modalInstance.dismiss('cancel');
          return;
        }

        switch (action) {
          case 'edit':
            if ($scope.entity.contract.is_primary === entity.contract.is_primary) {
              confirmEdit().then(function (confirmed) {
                switch (confirmed) {
                  case 'edit':
                    contractEdit();
                    break;
                  case 'change':
                    changeReason().then(function (results) {
                      contractChange(results.reasonId, results.date);
                    });
                    break;
                }
              });
            } else {
              contractEdit();
            }
            break;
          case 'change':
            changeReason().then(function (results) {
              contractChange(results.reasonId, results.date);
            });
            break;
          default:
            $scope.$broadcast('hrjc-loader-hide');
            $modalInstance.dismiss('cancel');
        }
      }

      /**
       * Shows a confirmation dialog warning the user that, if they proceed, the staff
       * leave entitlement will be updated.
       *
       * @returns {*}
       */
      function confirmUpdateEntitlements () {
        var modalUpdateEntitlements = $modal.open({
          appendTo: $rootElement.find('div').eq(0),
          size: 'sm',
          templateUrl: settings.pathApp + 'views/modalDialog.html?v=' + (new Date()).getTime(),
          controller: 'ModalDialogCtrl',
          resolve: {
            content: {
              title: 'Update leave entitlements?',
              msg: 'The system will now update the staff member leave entitlement.',
              copyConfirm: 'Proceed'
            }
          }
        });

        return modalUpdateEntitlements.result;
      }

      /**
       * # TO DO: This should probably happen inside the service that returns the data #
       *
       * Converts a date string into a Date object (if string is not empty)
       *
       * @param {string} dateString
       * @param {Date/null}
       */
      function convertToDateObject (dateString) {
        var dateObj = $filter('formatDate')(dateString, Date);

        return dateObj !== 'Unspecified' ? dateObj : dateString;
      }

      function changeReason () {
        var modalChangeReason = $modal.open({
          appendTo: $rootElement.find('div').eq(0),
          templateUrl: settings.pathApp + 'views/modalChangeReason.html?v=' + (new Date()).getTime(),
          controller: 'ModalChangeReasonCtrl',
          resolve: {
            content: function () {
              return {
                copy: {
                  title: copy.title
                }
              };
            },
            date: null,
            reasonId: null
          }
        });

        return modalChangeReason.result;
      }

      function confirmEdit () {
        var modalConfirmEdit = $modal.open({
          appendTo: $rootElement.find('div').eq(0),
          templateUrl: settings.pathApp + 'views/modalConfirmEdit.html?v=' + (new Date()).getTime(),
          controller: 'ModalDialogCtrl',
          resolve: {
            content: function () {
              return {
                msg: 'Save without making a new revision?'
              };
            }
          }
        });

        return modalConfirmEdit.result;
      }

      function contractEdit () {
        $scope.$broadcast('hrjc-loader-show');
        $scope.entity.details.period_end_date = $scope.entity.details.period_end_date || '';

        var entityName, file, i, len, modalInstance;
        var entityNew = angular.copy($scope.entity);
        var filesTrash = $scope.filesTrash;
        var uploader = $scope.uploader;

        var promiseContractEdit = {
          contract: ContractService.save(entityNew.contract),
          details: ContractDetailsService.save(entityNew.details),
          hour: ContractHourService.save(entityNew.hour),
          pay: ContractPayService.save(entityNew.pay),
          leave: ContractLeaveService.save(entityNew.leave),
          health: ContractHealthService.save(entityNew.health),
          pension: ContractPensionService.save(entityNew.pension)
        };
        var promiseFilesEditUpload = [];
        var promiseFilesEditDelete = [];

        for (entityName in filesTrash) {
          i = 0;
          len = filesTrash[entityName].length;
          for (i; i < len; i++) {
            file = filesTrash[entityName][i];
            promiseFilesEditDelete.push(ContractFilesService.delete(file.fileID, file.entityID, file.entityTable));
          }
        }

        angular.extend(promiseContractEdit, {
          files: promiseFilesEditDelete.length ? $q.all(promiseFilesEditDelete) : false
        });

        $q.all(promiseContractEdit).then(function (results) {
          angular.forEach(uploader, function (entity) {
            angular.forEach(entity, function (field) {
              angular.forEach(field.queue, function (item) {
                if (item.file.size > $scope.fileMaxSize) {
                  item.remove();
                }
              });
            });
          });

          if (uploader.details.contract_file.queue.length) {
            promiseFilesEditUpload.push(ContractFilesService.upload(uploader.details.contract_file, entityNew.details.jobcontract_revision_id));
          }

          if (uploader.pension.evidence_file.queue.length) {
            promiseFilesEditUpload.push(ContractFilesService.upload(uploader.pension.evidence_file, entityNew.pension.jobcontract_revision_id));
          }

          // TODO (incorrect date format in the API response)
          results.details.period_start_date = entityNew.details.period_start_date;
          results.details.period_end_date = entityNew.details.period_end_date;
          //

          // TODO (incorrect JSON format in the API response)
          results.pay.annual_benefits = entityNew.pay.annual_benefits;
          results.pay.annual_deductions = entityNew.pay.annual_deductions;

          if (promiseFilesEditUpload.length) {
            modalInstance = $modal.open({
              appendTo: $rootElement.find('div').eq(0),
              templateUrl: settings.pathApp + 'views/modalProgress.html?v=' + (new Date()).getTime(),
              size: 'sm',
              controller: 'ModalProgressCtrl',
              resolve: {
                uploader: function () {
                  return uploader;
                },
                promiseFilesUpload: function () {
                  return promiseFilesEditUpload;
                }
              }
            });

            results.files = modalInstance.result;
            return $q.all(results);
          }

          return results;
        }).then(function (results) {
          $scope.$broadcast('hrjc-loader-hide');
          $modalInstance.close(results);
          pubSub.publish('Contract::updated');
        }, function (reason) {
          $scope.$broadcast('hrjc-loader-hide');
          CRM.alert(reason, 'Error', 'error');
        });
      }

      function contractChange (reasonId, date) {
        $scope.$broadcast('hrjc-loader-show');

        ContractRevisionService.validateEffectiveDate({
          contact_id: settings.contactId,
          effective_date: date
        }).then(function (result) {
          if (result.success) {
            saveContractChange(reasonId, date);
          } else {
            CRM.alert(result.message, 'Error', 'error');
            $scope.$broadcast('hrjc-loader-hide');
          }
        }, function (reason) {});
      }

      function saveContractChange (reasonId, date) {
        var entityName, entityFilesTrashLen, field, fieldName, file, ii,
          isChanged, item, modalInstance, revisionId;
        var entityChangedList = [];
        var entityNew = angular.copy($scope.entity);
        var filesTrash = $scope.filesTrash;
        var uploader = $scope.uploader;
        var entityChangedListLen = 0;
        var fieldQueueLen = 0;
        var i = 0;
        var promiseContractChange = {};
        var promiseFilesChangeDelete = [];
        var promiseFilesChangeUpload = [];
        var entityServices = {
          details: ContractDetailsService,
          hour: ContractHourService,
          pay: ContractPayService,
          leave: ContractLeaveService,
          health: ContractHealthService,
          pension: ContractPensionService
        };

        for (entityName in entityServices) {
          isChanged = !angular.equals(entity[entityName], entityNew[entityName]);

          if (!isChanged) {
            isChanged = !!filesTrash[entityName] && !!filesTrash[entityName].length;

            if (!isChanged && uploader[entityName]) {
              for (fieldName in uploader[entityName]) {
                field = uploader[entityName][fieldName];
                if (field.queue.length) {
                  isChanged = true;
                  break;
                }
              }
            }
          }

          if (isChanged) {
            entityChangedList[i] = {};
            entityChangedList[i].name = entityName;
            entityChangedList[i].data = entityNew[entityName];
            entityChangedList[i].service = entityServices[entityName];
            i++;
            entityChangedListLen = i;
          }
        }

        if (entityChangedListLen) {
          UtilsService.prepareEntityIds(entityChangedList[0].data, entity.contract.id);

          entityChangedList[0].service.save(entityChangedList[0].data).then(function (results) {
            i = 1;
            revisionId = !angular.isArray(results) ? results.jobcontract_revision_id : results[0].jobcontract_revision_id;
            promiseContractChange[entityChangedList[0].name] = results;

            for (i; i < entityChangedListLen; i++) {
              entityName = entityChangedList[i].name;

              UtilsService.prepareEntityIds(entityChangedList[i].data, entity.contract.id, revisionId);
              promiseContractChange[entityName] = entityChangedList[i].service.save(entityChangedList[i].data);
            }

            return $q.all(angular.extend(promiseContractChange, {
              revisionCreated: ContractService.saveRevision({
                id: revisionId,
                change_reason: reasonId,
                effective_date: date
              })
            }, {
              files: false
            }));
          }).then(function (results) {
            for (entityName in entityServices) {
              results[entityName] = results[entityName] || entityNew[entityName];

              if (filesTrash[entityName] && filesTrash[entityName].length) {
                i = 0;
                entityFilesTrashLen = filesTrash[entityName].length;
                for (i; i < entityFilesTrashLen; i++) {
                  file = filesTrash[entityName][i];
                  promiseFilesChangeDelete.push(ContractFilesService.delete(file.fileID, revisionId, file.entityTable));
                }
              }
            }

            // TODO (incorrect date format in the API response)
            results.details.period_start_date = entityNew.details.period_start_date;
            results.details.period_end_date = entityNew.details.period_end_date;
            results.revisionCreated.effective_date = date || '';
            //

            // TODO (incorrect JSON format in the API response)
            results.pay.annual_benefits = entityNew.pay.annual_benefits;
            results.pay.annual_deductions = entityNew.pay.annual_deductions;

            angular.extend(results.revisionCreated, {
              details_revision_id: results.details.jobcontract_revision_id,
              health_revision_id: results.health.jobcontract_revision_id,
              hour_revision_id: results.hour.jobcontract_revision_id,
              jobcontract_id: entity.contract.id,
              leave_revision_id: results.leave[0].jobcontract_revision_id,
              pay_revision_id: results.pay.jobcontract_revision_id,
              pension_revision_id: results.pension.jobcontract_revision_id
            });

            if (promiseFilesChangeDelete.length) {
              results.files = $q.all(promiseFilesChangeDelete);
              return $q.all(results);
            }

            return results;
          }).then(function (results) {
            i = 0;
            for (i; i < entityChangedListLen; i++) {
              entityName = entityChangedList[i].name;

              if (uploader[entityName]) {
                for (fieldName in uploader[entityName]) {
                  field = uploader[entityName][fieldName];
                  fieldQueueLen = field.queue.length;
                  ii = 0;

                  for (ii; ii < fieldQueueLen; ii++) {
                    item = field.queue[ii];
                    if (item.file.size > $scope.fileMaxSize) {
                      item.remove();
                      ii--;
                      fieldQueueLen--;
                    }
                  }

                  if (fieldQueueLen) {
                    promiseFilesChangeUpload.push(ContractFilesService.upload(field, revisionId));
                  }
                }
              }
            }

            if (promiseFilesChangeUpload.length) {
              modalInstance = $modal.open({
                appendTo: $rootElement.find('div').eq(0),
                templateUrl: settings.pathApp + 'views/modalProgress.html',
                size: 'sm',
                controller: 'ModalProgressCtrl',
                resolve: {
                  uploader: function () {
                    return uploader;
                  },
                  promiseFilesUpload: function () {
                    return promiseFilesChangeUpload;
                  }
                }
              });

              results.files = modalInstance.result;
              return $q.all(results);
            }

            return results;
          }).then(function (results) {
            $scope.$broadcast('hrjc-loader-hide');
            $modalInstance.close(results);
            pubSub.publish('Contract::updated');
          });
        } else {
          $scope.$broadcast('hrjc-loader-hide');
          $modalInstance.close();
        }
      }

      /*
       * Fetch updated Health and Life Insurance Plan Types
       */
      function fetchInsurancePlanTypes () {
        return $q.all([
          { name: 'hrjobcontract_health_health_plan_type', key: 'plan_type' },
          { name: 'hrjobcontract_health_life_insurance_plan_type', key: 'plan_type_life_insurance' }
        ].map(function (planTypeData) {
          ContractHealthService.getOptions(planTypeData.name, true)
          .then(function (planTypes) {
            $rootScope.options.health[planTypeData.key] = _.transform(planTypes, function (acc, type) {
              acc[type.key] = type.value;
            }, {});
          });
        }));
      }
    }
  ]);
});
