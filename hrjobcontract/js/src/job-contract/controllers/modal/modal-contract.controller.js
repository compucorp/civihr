/* eslint-env amd */

define([
  'common/angular',
  'common/lodash',
  'common/moment'
], function (angular, _, moment) {
  'use strict';

  ModalContractController.__name = 'ModalContractController';
  ModalContractController.$inject = [
    '$scope', '$uibModal', '$uibModalInstance', '$q', '$rootElement', '$rootScope',
    '$filter', 'contractService', 'contractRevisionService', 'contractDetailsService',
    'contractHourService', 'contractPayService', 'contractLeaveService',
    'contractHealthService', 'contractPensionService', 'contractFilesService',
    'action', 'entity', 'content', 'files', 'utilsService', 'utils', 'settings',
    '$log', 'pubSub'
  ];

  function ModalContractController ($scope, $modal, $modalInstance, $q, $rootElement,
    $rootScope, $filter, contractService, contractRevisionService, contractDetailsService,
    contractHourService, contractPayService, contractLeaveService, contractHealthService,
    contractPensionService, contractFilesService, action, entity, content, files,
    utilsService, utils, settings, $log, pubSub) {
    $log.debug('Controller: ModalContractController');

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
        contract_file: contractFilesService.uploader('civicrm_hrjobcontract_details')
      },
      pension: {
        evidence_file: contractFilesService.uploader('civicrm_hrjobcontract_pension', 1)
      }
    };
    $scope.utils = utils;

    $scope.cancel = promptAboutChangesLossAndCloseOnConfirm;
    $scope.fileMoveToTrash = fileMoveToTrash;
    $scope.filesValidate = filesValidate;
    $scope.save = save;

    // Init
    (function init () {
      initFormEntity();
      initFilesAndUploaders();

      $modalInstance.opened.then(function () {
        $rootScope.$broadcast('hrjc-loader-hide');
      });

      $rootScope.$broadcast('hrjc-loader-show');
      fetchInsurancePlanTypes().then(function () {
        $rootScope.$broadcast('hrjc-loader-hide');
      });
    }());

    /**
     * Hides the job contract modal, but before doing so asks for confirmation
     * before changes are lost.
     */
    function promptAboutChangesLossAndCloseOnConfirm () {
      if (action === 'view' ||
        (angular.equals(entity, $scope.entity) && angular.equals(files, $scope.files) &&
          !$scope.uploader.details.contract_file.queue.length && !$scope.uploader.pension.evidence_file.queue.length)) {
        $scope.$broadcast('hrjc-loader-hide');
        $modalInstance.dismiss('cancel');

        return;
      }

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
        controller: 'ModalDialogController',
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
    }

    /**
     * Displays the contract revision modal where the user specifies the reason
     * for the changes made.
     *
     * @return {Promise} resolves when the modal closes.
     */
    function changeReason () {
      var modalChangeReason = $modal.open({
        appendTo: $rootElement.find('div').eq(0),
        templateUrl: settings.pathApp + 'views/modalChangeReason.html?v=' + (new Date()).getTime(),
        controller: 'ModalChangeReasonController',
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

    /**
     * Determines if the contract entitlements have been modified. These include
     * the contract start and end dates as well as the leave entitlements.
     *
     * @return {Boolean}
     */
    function checkIfEntitlementFieldsChanged () {
      var hasEndDateChanged = hasContractDateChanged('period_end_date');
      var hasStartDateChanged = hasContractDateChanged('period_start_date');
      var haveEntitlementsChanged = !angular.equals($scope.entity.leave,
        entity.leave);

      return hasStartDateChanged || hasEndDateChanged || haveEntitlementsChanged;
    }

    /**
     * Displays a confirmation modal to see if the user wants to record a new
     * revision for the changes made.
     *
     * @return {Promise} resolves when the modal closes.
     */
    function confirmEdit () {
      var modalConfirmEdit = $modal.open({
        appendTo: $rootElement.find('div').eq(0),
        templateUrl: settings.pathApp + 'views/modalConfirmEdit.html?v=' + (new Date()).getTime(),
        controller: 'ModalDialogController',
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

    /**
     * Confirms that the contract change is valid and saves it.
     *
     * @param {Number} reasonId the ID of the reason option selected by the user.
     * @param {Date} date effective date for the change as selected by the user.
     * @return {Promise} resolves after the contract has been saved.
     */
    function validateContractAndSave (reasonId, date) {
      $scope.$broadcast('hrjc-loader-show');

      return contractRevisionService.validateEffectiveDate({
        contact_id: settings.contactId,
        effective_date: date
      }).then(function (result) {
        if (result.success) {
          return saveContractChange(reasonId, date);
        } else {
          CRM.alert(result.message, 'Error', 'error');
          $scope.$broadcast('hrjc-loader-hide');
        }
      }, function (reason) {});
    }

    /**
     * Saves all the tabs in the contract's form.
     *
     * @return {Promise} resolves when all the tabs are saved.
     */
    function saveAllContractSections () {
      $scope.$broadcast('hrjc-loader-show');
      $scope.entity.details.period_end_date = $scope.entity.details.period_end_date || '';

      var entityName, file, i, len, modalInstance;
      var entityNew = angular.copy($scope.entity);
      var filesTrash = $scope.filesTrash;
      var uploader = $scope.uploader;

      var promiseContractEdit = {
        contract: contractService.save(entityNew.contract),
        details: contractDetailsService.save(entityNew.details),
        hour: contractHourService.save(entityNew.hour),
        pay: contractPayService.save(entityNew.pay),
        leave: contractLeaveService.save(entityNew.leave),
        health: contractHealthService.save(entityNew.health),
        pension: contractPensionService.save(entityNew.pension)
      };
      var promiseFilesEditUpload = [];
      var promiseFilesEditDelete = [];

      for (entityName in filesTrash) {
        i = 0;
        len = filesTrash[entityName].length;
        for (i; i < len; i++) {
          file = filesTrash[entityName][i];
          promiseFilesEditDelete.push(contractFilesService.delete(file.fileID, file.entityID, file.entityTable));
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
          promiseFilesEditUpload.push(contractFilesService.upload(uploader.details.contract_file, entityNew.details.jobcontract_revision_id));
        }

        if (uploader.pension.evidence_file.queue.length) {
          promiseFilesEditUpload.push(contractFilesService.upload(uploader.pension.evidence_file, entityNew.pension.jobcontract_revision_id));
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
            controller: 'ModalProgressController',
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

        results.haveEntitlementFieldsChanged = checkIfEntitlementFieldsChanged();

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

    /**
     * Converts a date string into a Date object.
     * If the date is not valid it returns the same date string provided.
     *
     * @todo This should probably happen inside the service that returns the data #
     * @todo this function should not return Date or String, it should be more strict,
     * but we need more test coverage before refactoring it.
     *
     * @param  {String} dateString the string representing a date.
     * @return {Date|String}
     */
    function convertToDateObject (dateString) {
      var dateObj = $filter('formatDate')(dateString, Date);

      return dateObj !== 'Unspecified' ? dateObj : dateString;
    }

    /**
     * Fetch updated Health and Life Insurance Plan Types
     *
     * @return {Promise}
     */
    function fetchInsurancePlanTypes () {
      return $q.all([
        { name: 'hrjobcontract_health_health_plan_type', key: 'plan_type' },
        { name: 'hrjobcontract_health_life_insurance_plan_type', key: 'plan_type_life_insurance' }
      ].map(function (planTypeData) {
        contractHealthService.getOptions(planTypeData.name, true)
          .then(function (planTypes) {
            $rootScope.options.health[planTypeData.key] = _.transform(planTypes, function (acc, type) {
              acc[type.key] = type.value;
            }, {});
          });
      }));
    }

    /**
     * Moves the file to a trash list that will later be used to delete the files.
     *
     * @param {Number} index the index of the file in the section list.
     * @param {String} entityName the section name where the file is stored.
     */
    function fileMoveToTrash (index, entityName) {
      var entityFiles = $scope.files[entityName];
      var entityFilesTrash = $scope.filesTrash[entityName];

      entityFilesTrash.push(entityFiles[index]);
      entityFiles.splice(index, 1);
    }

    /**
     * Validates that all files are within the max file size limit.
     */
    function filesValidate () {
      var isValid = _.every($scope.uploader, function (uploaderEntity) {
        return _.every(uploaderEntity, function (uploaderEntityField) {
          // if there is no queue check other entities:
          if (!uploaderEntityField.queue) {
            return true;
          }

          return _.every(uploaderEntityField.queue, function (queue) {
            return queue.file.size < $scope.fileMaxSize;
          });
        });
      });

      $scope.contractForm.$setValidity('maxFileSize', isValid);
    }

    /**
     * Initializes files and uploader fields. For the files it creates a trash
     * container in case the file is deleted and for uploaders it adds validators
     * to run after file uploading.
     */
    function initFilesAndUploaders () {
      angular.copy(files, $scope.files);
      angular.forEach($scope.files, function (entityFiles, entityName) {
        $scope.filesTrash[entityName] = [];
      });
      angular.forEach($scope.uploader, function (entity) {
        angular.forEach(entity, function (field) {
          field.onAfterAddingAll = function () {
            $scope.filesValidate();
          };
        });
      });
    }

    /**
     * Determines if the given contract date has changed from its original value.
     *
     * @param  {String} dateName either "period_start_date" or "period_end_date".
     * @return {Boolean}
     */
    function hasContractDateChanged (dateName) {
      var currentDate, currentDateIsDifferentFromOriginal, originalDate,
        oneisEmptyAndTheOtherIsNot;

      originalDate = entity.details[dateName];
      currentDate = $scope.entity.details[dateName] !== ''
        ? $scope.entity.details[dateName]
        : null;

      oneisEmptyAndTheOtherIsNot = (currentDate === null || originalDate === null) &&
        currentDate !== originalDate;
      currentDateIsDifferentFromOriginal = moment.isDate(currentDate) &&
        !moment(originalDate).isSame(moment(currentDate), 'day');

      return oneisEmptyAndTheOtherIsNot || currentDateIsDifferentFromOriginal;
    }

    /**
     * Initializes the form entity fields by having a copy of the form entity
     * and converting date strings into date objects.
     */
    function initFormEntity () {
      angular.copy(entity, $scope.entity);
      $scope.entity.details.period_start_date = convertToDateObject($scope.entity.details.period_start_date);
      $scope.entity.details.period_end_date = convertToDateObject($scope.entity.details.period_end_date);
    }

    /**
     * Handles all the confirmations that need to be displayed when updating the contract.
     */
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
                  saveAllContractSections();
                  break;
                case 'change':
                  changeReason().then(function (results) {
                    return validateContractAndSave(results.reasonId, results.date);
                  });
                  break;
              }
            });
          } else {
            saveAllContractSections();
          }
          break;
        case 'change':
          changeReason().then(function (results) {
            validateContractAndSave(results.reasonId, results.date);
          });
          break;
        default:
          $scope.$broadcast('hrjc-loader-hide');
          $modalInstance.dismiss('cancel');
      }
    }

    /**
     * Runs the contract saving sequence which includes:
     * - Validating the contract dates.
     * - Confirming if entitlements should be updated.
     * - Updating the contract.
     */
    function save () {
      if (!$scope.allowSave) {
        return;
      }

      $scope.$broadcast('hrjc-loader-show');
      contractDetailsService.validateDates({
        contact_id: settings.contactId,
        period_start_date: $scope.entity.details.period_start_date,
        period_end_date: $scope.entity.details.period_end_date,
        jobcontract_id: entity.contract.id
      }).then(function (result) {
        if (result.success) {
          processContractUpdate();
        } else {
          CRM.alert(result.message, 'Error', 'error');
          $scope.$broadcast('hrjc-loader-hide');
        }
      }, function (reason) {});
      $scope.$broadcast('hrjc-loader-hide');
    }

    /**
     * This method saves the contract revision and the contract sections.
     *
     * @todo Need to integrate saveAllContractSections to avoid repetition.
     *
     * @param {Number} reasonId the reason given for the revision.
     * @param {Date} date the efective date of the revision.
     */
    function saveContractChange (reasonId, date) {
      var modalInstance, revisionId;
      var entityChangedList = [];
      var entityNew = angular.copy($scope.entity);
      var filesTrash = $scope.filesTrash;
      var uploader = $scope.uploader;
      var promiseContractChange = {};
      var promiseFilesChangeDelete = [];
      var promiseFilesChangeUpload = [];
      var entityServices = {
        details: contractDetailsService,
        hour: contractHourService,
        pay: contractPayService,
        leave: contractLeaveService,
        health: contractHealthService,
        pension: contractPensionService
      };

      for (var entityName in entityServices) {
        var hasChanged = !angular.equals(entity[entityName], entityNew[entityName]);

        if (!hasChanged) {
          hasChanged = !!filesTrash[entityName] && !!filesTrash[entityName].length;

          if (!hasChanged && uploader[entityName]) {
            for (var fieldName in uploader[entityName]) {
              var field = uploader[entityName][fieldName];
              if (field.queue.length) {
                hasChanged = true;
                break;
              }
            }
          }
        }

        if (hasChanged) {
          entityChangedList.push({
            name: entityName,
            data: entityNew[entityName],
            service: entityServices[entityName]
          });
        }
      }

      if (entityChangedList.length) {
        utilsService.prepareEntityIds(entityChangedList[0].data, entity.contract.id);

        entityChangedList[0].service.save(entityChangedList[0].data).then(function (results) {
          revisionId = !angular.isArray(results) ? results.jobcontract_revision_id : results[0].jobcontract_revision_id;
          promiseContractChange[entityChangedList[0].name] = results;

          entityChangedList.slice(1).forEach(function (entity) {
            utilsService.prepareEntityIds(entity.data, entity.contract.id, revisionId);
            promiseContractChange[entity.name] = entity.service.save(entity.data);
          });

          return $q.all(angular.extend(promiseContractChange, {
            revisionCreated: contractService.saveRevision({
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
              filesTrash[entityName].forEach(function (file) {
                promiseFilesChangeDelete.push(contractFilesService.delete(
                  file.fileID, revisionId, file.entityTable));
              });
            }
          }

          // TODO (incorrect date format in the API response)
          results.details.period_start_date = entityNew.details.period_start_date;
          results.details.period_end_date = entityNew.details.period_end_date;
          results.revisionCreated.effective_date = date || '';

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
          entityChangedList.forEach(function (entity) {
            if (!uploader[entity.name]) {
              return;
            }

            for (var fieldName in uploader[entity.name]) {
              field = uploader[entity.name][fieldName];

              field.queue.forEach(function (item) {
                if (item.file.size > $scope.fileMaxSize) {
                  item.remove();
                }
              });

              if (field.queue.length) {
                promiseFilesChangeUpload.push(contractFilesService.upload(field, revisionId));
              }
            }
          });

          if (promiseFilesChangeUpload.length) {
            modalInstance = $modal.open({
              appendTo: $rootElement.find('div').eq(0),
              templateUrl: settings.pathApp + 'views/modalProgress.html',
              size: 'sm',
              controller: 'ModalProgressController',
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

          results.haveEntitlementFieldsChanged = checkIfEntitlementFieldsChanged();

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
  }

  return ModalContractController;
});
