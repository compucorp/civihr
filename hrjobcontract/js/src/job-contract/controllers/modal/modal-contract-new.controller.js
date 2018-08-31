/* eslint-env amd */

define([
  'common/angular',
  'common/lodash',
  'common/moment'
], function (angular, _, moment) {
  'use strict';

  ModalContractNewController.$inject = [
    '$log', '$q', '$rootElement', '$rootScope', '$sce', '$scope', '$uibModalInstance',
    '$uibModal', 'crmAngService', 'Contract', 'contractService', 'contractDetailsService',
    'contractHourService', 'contractPayService', 'contractLeaveService',
    'contractHealthService', 'contractPensionService', 'contractFilesService',
    'model', 'OptionGroup', 'utilsService', 'utils', 'settings', 'pubSub'
  ];

  function ModalContractNewController ($log, $q, $rootElement, $rootScope, $sce,
    $scope, $modalInstance, $modal, crmAngService, Contract, contractService, contractDetailsService,
    contractHourService, contractPayService, contractLeaveService, contractHealthService,
    contractPensionService, contractFilesService, model, OptionGroup, utilsService, utils,
    settings, pubSub) {
    $log.debug('Controller: ModalContractNewController');

    $scope.allowSave = true;
    $scope.action = 'new';
    $scope.entity = {};
    $scope.fileMaxSize = settings.CRM.maxFileSize || 0;
    $scope.isDisabled = false;
    $scope.showIsPrimary = utils.contractListLen;
    $scope.utils = utils;
    $scope.copy = {
      close: 'Cancel',
      save: 'Add New Job Contract',
      title: 'Add New Job Contract'
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
    $scope.uploader = {
      details: {
        contract_file: contractFilesService.uploader('civicrm_hrjobcontract_details')
      },
      pension: {
        evidence_file: contractFilesService.uploader('civicrm_hrjobcontract_pension', 1)
      }
    };

    $scope.cancel = cancel;
    $scope.filesValidate = filesValidate;
    $scope.openOptionsEditor = openOptionsEditor;
    $scope.openHoursLocationOptionsEditor = openHoursLocationOptionsEditor;
    $scope.openPayScaleGradeOptionsEditor = openPayScaleGradeOptionsEditor;
    $scope.openAnnualBenefitOptionsEditor = openAnnualBenefitOptionsEditor;
    $scope.openAnnualDeductionOptionsEditor = openAnnualDeductionOptionsEditor;
    $scope.save = save;

    (function init () {
      angular.copy(model, $scope.entity);
      $scope.entity.contract = {
        is_primary: 0
      };

      angular.forEach($scope.uploader, function (entity) {
        angular.forEach(entity, function (field) {
          field.onAfterAddingAll = function () {
            $scope.filesValidate();
          };
        });
      });

      // Since we are adding a new Contract, we set the values for each leave type with the AbsenceTypes values
      setDefaultLeaveValuesFromAbsenceType();

      $rootScope.$broadcast('hrjc-loader-show');
      fetchInsurancePlanTypes().then(function () {
        $rootScope.$broadcast('hrjc-loader-hide');
      });
    }());

    function cancel () {
      $modalInstance.dismiss('cancel');
    }

    function filesValidate () {
      var entityName, fieldName, i, len, uploaderEntity, uploaderEntityField, uploaderEntityFieldQueue;
      var fileMaxSize = $scope.fileMaxSize;
      var isValid = true;
      var uploader = $scope.uploader;

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
    }

    /**
     * Saves a new contract
     */
    function saveContract () {
      var contract = new Contract();

      contract.$save({
        action: 'create',
        json: {
          sequential: 1,
          contact_id: settings.contactId,
          is_primary: utils.contractListLen ? $scope.entity.contract.is_primary : 1
        }
      }, function (data) {
        var modalInstance, promiseContractNew, revisionId;
        var contract = data.values[0];
        var contractId = contract.id;
        var entityDetails = angular.copy($scope.entity.details);
        var entityHour = $scope.entity.hour;
        var entityPay = $scope.entity.pay;
        var entityLeave = $scope.entity.leave;
        var entityHealth = $scope.entity.health;
        var entityPension = $scope.entity.pension;
        var promiseUpload = [];
        var uploader = $scope.uploader;

        contract.is_current = !entityDetails.period_end_date || moment().diff(entityDetails.period_end_date, 'day') <= 0;

        utilsService.prepareEntityIds(entityDetails, contractId);

        contractDetailsService.save(entityDetails).then(function (results) {
          revisionId = results.jobcontract_revision_id;
        }, function (reason) {
          CRM.alert(reason, 'Error', 'error');
          contractService.delete(contractId);
          $modalInstance.dismiss();
          return $q.reject();
        }).then(function () {
          angular.forEach($scope.entity, function (entity) {
            utilsService.prepareEntityIds(entity, contractId, revisionId);
          });

          promiseContractNew = [
            contractHourService.save(entityHour),
            contractPayService.save(entityPay),
            contractLeaveService.save(entityLeave),
            contractHealthService.save(entityHealth),
            contractPensionService.save(entityPension)
          ];

          if ($scope.uploader.details.contract_file.queue.length) {
            promiseUpload.push(contractFilesService.upload(uploader.details.contract_file, revisionId));
          }

          if ($scope.uploader.pension.evidence_file.queue.length) {
            promiseUpload.push(contractFilesService.upload(uploader.pension.evidence_file, revisionId));
          }

          if (promiseUpload.length) {
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
                  return promiseUpload;
                }
              }
            });

            promiseContractNew.push(modalInstance.result);
          }

          return $q.all(promiseContractNew);
        }).then(function () {
          $scope.$broadcast('hrjc-loader-hide');
          $modalInstance.close(contract);

          pubSub.publish('Contract::created', settings.contactId);
        }, function (reason) {
          CRM.alert(reason, 'Error', 'error');
          contractService.delete(contractId).then(function (result) {
            $scope.$broadcast('hrjc-loader-hide');
            if (result.is_error) {
              CRM.alert((result.error_message || 'Unknown error'), 'Error', 'error');
            }
          }, function (error) {
            $scope.$broadcast('hrjc-loader-hide');
            CRM.alert((error || 'Unknown error'), 'Error', 'error');
          });
        });
      }, function (reason) {
        $scope.$broadcast('hrjc-loader-hide');
        $modalInstance.dismiss();
        CRM.alert((reason.statusText || 'Unknown error'), 'Error', 'error');
        return $q.reject();
      });
    }

    /*
     * Fetch updated Health and Life Insurance Plan Types
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

    function save () {
      $scope.$broadcast('hrjc-loader-show');

      contractDetailsService.validateDates({
        contact_id: settings.contactId,
        period_start_date: $scope.entity.details.period_start_date,
        period_end_date: $scope.entity.details.period_end_date
      }).then(function (result) {
        if (result.success) {
          saveContract();
        } else {
          CRM.alert(result.message, 'Error', 'error');
          $scope.$broadcast('hrjc-loader-hide');
        }
      }, function (reason) {});
    }

    /**
     * This method sets the Leave default values based on their respective Absence Type.
     *
     * It will set both the leave amount and if public holidays should be added to it.
     */
    function setDefaultLeaveValuesFromAbsenceType () {
      if (!$scope.entity.leave) {
        return;
      }

      $scope.entity.leave.forEach(function (leave, index) {
        var absenceTypeID = $scope.entity.leave[index].leave_type;

        $scope.entity.leave[index].leave_amount = utils.absenceTypes[absenceTypeID].default_entitlement;
        $scope.entity.leave[index].add_public_holidays = utils.absenceTypes[absenceTypeID].add_public_holiday_to_entitlement;
      });
    }

    /**
     * Opens option editor window for contract type, location, end reason or insurance
     *
     * @param {String} optionUrl
     * @param {String} fieldName
     */
    function openOptionsEditor (optionUrl, fieldName) {
      var optionTypes = {
        'hrjobcontract_details_contract_type': 'contract_type',
        'hrjobcontract_details_location': 'location',
        'hrjobcontract_details_end_reason': 'end_reason',
        'hrjobcontract_health_health_plan_type': 'provider_life_insurance'
      };

      crmAngService.loadForm(optionUrl)
        .on('crmUnload', function () {
          if (fieldName === 'hrjobcontract_health_health_plan_type') {
            contractHealthService.getOptions(fieldName, true)
              .then(function (data) {
                var healthOptions = _.mapValues(_.keyBy(data, 'key'), 'value');

                $rootScope.options.health.plan_type = healthOptions;
                $rootScope.options.health.plan_type_life_insurance = healthOptions;
              });
          } else {
            contractDetailsService.getOptions(fieldName, true)
              .then(function (data) {
                $rootScope.options.details[optionTypes[fieldName]] = data.obj;
              });
          }
        });
    }

    /**
     * Opens the hours location options editor window
     */
    function openHoursLocationOptionsEditor () {
      crmAngService.loadForm('/civicrm/standard_full_time_hours?reset=1')
        .on('crmUnload', function () {
          utilsService.getHoursLocation()
            .then(function (data) {
              $scope.utils.hoursLocation = data;
            });
        });
    }

    /**
     * Opens the pay scale grade options editor window
     */
    function openPayScaleGradeOptionsEditor () {
      crmAngService.loadForm('/civicrm/pay_scale?reset=1')
        .on('crmUnload', function () {
          utilsService.getPayScaleGrade()
            .then(function (data) {
              $scope.utils.payScaleGrade = data;
            });
        });
    }

    /**
     * Opens annual benefit options editor for editing
     */
    function openAnnualBenefitOptionsEditor () {
      crmAngService.loadForm('/civicrm/admin/options/hrjc_benefit_name?reset=1')
        .on('crmUnload', function () {
          loadAnnualPayOptions('hrjc_benefit_name', 'benefit_name');
        });
    }

    /**
     * Opens annual deduction options editor for editing
     */
    function openAnnualDeductionOptionsEditor () {
      crmAngService.loadForm('/civicrm/admin/options/hrjc_deduction_name?reset=1')
        .on('crmUnload', function () {
          loadAnnualPayOptions('hrjc_deduction_name', 'deduction_name');
        });
    }

    /**
     * Reload updated changes for annual deduction and benefit options
     *
     * @param {String} optionType
     * @param {String} optionName
     * @returns {Promise}
     */
    function loadAnnualPayOptions (optionType, optionName) {
      return OptionGroup.valuesOf(optionType, false)
        .then(function (data) {
          $rootScope.options.pay[optionName] = _.mapValues(_.keyBy(data, 'value'), 'label');
        });
    }
  }

  return { ModalContractNewController: ModalContractNewController };
});
