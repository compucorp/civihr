define([
    'job-contract/controllers/controllers',
    'job-contract/services/contract',
    'job-contract/services/contract-details',
    'job-contract/services/contract-hour',
    'job-contract/services/contract-pay',
    'job-contract/services/contract-leave',
    'job-contract/services/contract-health',
    'job-contract/services/contract-pension',
    'job-contract/services/contract-files',
    'job-contract/services/utils'
], function (controllers) {
    'use strict';

    controllers.controller('ModalContractNewCtrl', ['$scope', '$modalInstance', '$q', '$modal', '$rootElement',
        'Contract','ContractService', 'ContractDetailsService', 'ContractHourService', 'ContractPayService', 'ContractLeaveService',
        'ContractHealthService', 'ContractPensionService', 'ContractFilesService', 'model', 'UtilsService', 'utils',
        'settings', '$log',
        function ($scope, $modalInstance, $q, $modal, $rootElement, Contract, ContractService, ContractDetailsService, ContractHourService,
                 ContractPayService, ContractLeaveService, ContractHealthService, ContractPensionService,
                 ContractFilesService, model, UtilsService, utils, settings, $log) {
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
                    evidence_file: ContractFilesService.uploader('civicrm_hrjobcontract_pension',1)
                }
            };
            $scope.utils = utils;

            angular.copy(model,$scope.entity);
            $scope.entity.contract = {
                is_primary: 0
            };

            $scope.filesValidate = function() {
                var entityName,
                    fieldName,
                    fileMaxSize = $scope.fileMaxSize,
                    uploader = $scope.uploader,
                    uploaderEntity,
                    uploaderEntityField,
                    uploaderEntityFieldQueue,
                    isValid = true, i, len;

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

            angular.forEach($scope.uploader, function(entity){
                angular.forEach(entity, function(field){
                    field.onAfterAddingAll = function(){
                        $scope.filesValidate();
                    }
                });
            });

            $scope.cancel = function () {
                $modalInstance.dismiss('cancel');
            };

            $scope.save = function () {
                $scope.$broadcast('hrjc-loader-show');
                var contract = new Contract();

                ContractDetailsService.validateDates({
                    contact_id: settings.contactId,
                    period_start_date: $scope.entity.details.period_start_date,
                    period_end_date: $scope.entity.details.period_end_date
                }).then(function(result){
                    if (result.success) {
                        contract.$save({
                            action: 'create',
                            json: {
                                sequential: 1,
                                contact_id: settings.contactId,
                                is_primary: utils.contractListLen ? $scope.entity.contract.is_primary : 1
                            }
                        },function(data){
                            var contract = data.values[0],
                                contractId = contract.id,
                                entityDetails = $scope.entity.details,
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

                            contract.is_current = !entityDetails.period_end_date || new Date(entityDetails.period_end_date) > new Date();

                            UtilsService.prepareEntityIds(entityDetails, contractId);

                            ContractDetailsService.save(entityDetails).then(function(results){
                                revisionId = results.jobcontract_revision_id;
                            },function(reason){
                                CRM.alert(reason, 'Error', 'error');
                                ContractService.delete(contractId);
                                $modalInstance.dismiss();
                                return $q.reject();
                            }).then(function(){

                                angular.forEach($scope.entity, function(entity){
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
                                    modalInstance  = $modal.open({
                                        targetDomEl: $rootElement.find('div').eq(0),
                                        templateUrl: settings.pathApp+'views/modalProgress.html',
                                        size: 'sm',
                                        controller: 'ModalProgressCtrl',
                                        resolve: {
                                            uploader: function(){
                                                return uploader;
                                            },
                                            promiseFilesUpload: function(){
                                                return promiseUpload;
                                            }
                                        }
                                    });

                                    promiseContractNew.push(modalInstance.result);
                                }

                                return $q.all(promiseContractNew);
                            },function(reason){
                                CRM.alert(reason, 'Error', 'error');
                                $modalInstance.dismiss();
                                return $q.reject();
                            }).then(function(){
                                $scope.$broadcast('hrjc-loader-hide');
                                $modalInstance.close(contract);
                            });

                        },function(reason){
                            $scope.$broadcast('hrjc-loader-hide');
                            $modalInstance.dismiss();
                            CRM.alert((reason.statusText || 'Unknown error'), 'Error', 'error');
                            return $q.reject();
                        });
                    } else {
                        CRM.alert(result.message, 'Error', 'error');
                        $scope.$broadcast('hrjc-loader-hide');
                    }
                },function(reason){
                });
            };

        }]);
});
