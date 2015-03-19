define(['controllers/controllers',
        'filters/getObjById',
        'services/contractDetails',
        'services/contractHour',
        'services/contractHealth',
        'services/contractLeave',
        'services/contractPay',
        'services/contractPension',
        'services/utils'], function(controllers){
    controllers.controller('ContractListCtrl',['$scope','$rootElement','$rootScope','$modal','$q', '$filter', 'contractList','ContractService',
        'ContractDetailsService', 'ContractHourService', 'ContractPayService', 'ContractLeaveService', 'ContractHealthService',
        'ContractPensionService', 'UtilsService','settings', '$log',
        function($scope, $rootElement, $rootScope, $modal, $q, $filter, contractList, ContractService, ContractDetailsService,
                 ContractHourService, ContractPayService, ContractLeaveService, ContractHealthService, ContractPensionService,
                 UtilsService, settings, $log){
            $log.debug('Controller: ContractListCtrl');

            var entityServices = {
                    details: ContractDetailsService,
                    hour: ContractHourService,
                    pay: ContractPayService,
                    leave: ContractLeaveService,
                    health: ContractHealthService,
                    pension: ContractPensionService
                }, entityName, promiseFields = {}, promiseModel = {}, promiseUtils = {};

            $scope.contractListLoaded = false;
            $scope.contractCurrent = [];
            $scope.contractPast = [];

            for (entityName in entityServices) {
                promiseFields[entityName] = entityServices[entityName].getFields();
            }

            $q.all(promiseFields).then(function(fields){
                $scope.fields = fields;

                $log.debug('FIELDS:');
                $log.debug(fields);

                for (entityName in entityServices) {
                    promiseModel[entityName] = entityServices[entityName].model(fields[entityName]);
                }

                return $q.all(promiseModel);

            }).then(function(model){
                $scope.model = model;

                $log.debug('MODEL:');
                $log.debug(model);

                contractList = $filter('orderBy')(contractList,'-is_primary');

                angular.forEach(contractList,function(contract){
                    +contract.is_current ? $scope.contractCurrent.push(contract) : $scope.contractPast.push(contract);
                });

                $scope.$watchCollection('contractCurrent',function(){
                    $scope.utils.contractListLen = $scope.contractCurrent.length + $scope.contractPast.length;
                });

                $scope.$watchCollection('contractPast',function(){
                    $scope.utils.contractListLen = $scope.contractCurrent.length + $scope.contractPast.length;
                });

                $rootScope.$broadcast('hrjc-loader-hide');
                $scope.contractListLoaded = true;
            });

            $scope.utils = $q.all({
                contractListLen: contractList.length,
                hoursLocation: UtilsService.getHoursLocation(),
                payScaleGrade: UtilsService.getPayScaleGrade()
            });

            $scope.toggleIsPrimary = function(contractId) {
                function unsetIsPrimary(contractArray){
                    var i = 0,
                        len = contractArray.length;

                    for (i; i < len; i++){
                        if (+contractArray[i].id != +contractId && +contractArray[i].is_primary) {
                            contractArray[i].is_primary = '0';
                            return contractArray[i].id;
                        }
                    }

                    return null;
                }

                unsetIsPrimary($scope.contractCurrent) || unsetIsPrimary($scope.contractPast);

                ($filter('getObjById')($scope.contractCurrent,contractId) || $filter('getObjById')($scope.contractPast,contractId) || {}).is_primary = '1';

                $scope.contractCurrent = $filter('orderBy')($scope.contractCurrent,'-is_primary');
                $scope.contractPast = $filter('orderBy')($scope.contractPast,'-is_primary');
            };

            $scope.modalContract = function(action){

                if (!action || action !== 'new') {
                    return null;
                }

                var modalInstance,
                    options = {
                        targetDomEl: $rootElement.find('div').eq(0),
                        templateUrl: settings.pathApp+'views/modalForm.html?v=fbwefg',
                        size: 'lg',
                        controller: 'ModalContractNewCtrl',
                        resolve: {
                            model: function() {
                                return $scope.model;
                            },
                            utils: function(){
                                return $scope.utils;
                            }
                        }
                    }

                modalInstance = $modal.open(options);

                modalInstance.result.then(function(contract){
                    +contract.is_current ? $scope.contractCurrent.push(contract) : $scope.contractPast.push(contract);
                    if (+contract.is_primary) {
                        $scope.toggleIsPrimary(contract.id);
                    }
                });
            }

            $scope.delete = function(contractId) {

                var modalInstance = $modal.open({
                    targetDomEl: $rootElement.find('div').eq(0),
                    templateUrl: settings.pathApp+'views/modalDialog.html',
                    size: 'sm',
                    controller: 'ModalDialogCtrl',
                    resolve: {
                        content: function(){
                            return {
                                msg: 'Are you sure you want to delete this job contract?'
                            };
                        }
                    }
                });

                modalInstance.result.then(function(confirm){
                    if (confirm) {
                        $scope.$emit('hrjc-loader-show');
                        ContractService.delete(contractId).then(function(result){

                            if (!result.is_error) {
                                function removeContractById(contractArray, id){

                                    var i = 0,
                                        len = contractArray.length;

                                    for (i; i < len; i++){
                                        if (+contractArray[i].id == id) {
                                            $scope.$emit('hrjc-loader-hide');
                                            contractArray.splice(i,1);
                                            return id;
                                        }
                                    }

                                    return null;
                                }

                                removeContractById($scope.contractCurrent, contractId) || removeContractById($scope.contractPast, contractId);
                            }
                        });
                    }
                })

            }

        }]);
});