define(['controllers/controllers',
        'services/contractDetails',
        'services/contractHour',
        'services/contractPay',
        'services/contractLeave',
        'services/contractPension',
        'services/contractHealth',
        'services/contact',
        'services/utils'], function(controllers){
    controllers.controller('ContractCtrl',['$scope', '$route', '$filter', '$modal', '$rootElement', '$q', 'settings',
        'API', 'ContractDetailsService', 'ContractHourService', 'ContractPayService', 'ContractLeaveService',
        'ContractHealthService', 'ContractPensionService','ContractFilesService','ContactService','$log',
        function($scope, $route, $filter, $modal, $rootElement, $q, settings, API, ContractDetailsService,
                 ContractHourService, ContractPayService, ContractLeaveService, ContractHealthService,
                 ContractPensionService, ContractFilesService, ContactService, $log){
            $log.debug('Controller: ContractCtrl');

            var contractId = $scope.contract.id,
                promiseFiles;

            $scope.contractLoaded = false;
            $scope.isCollapsed = true;
            $scope.revisionCurrent = {};
            $scope.revisionList = [];
            $scope.revisionDataList = [];

            angular.extend($scope, angular.copy($scope.model));

            function updateContractView(newScope){
                var contractRevisionIdObj = {
                    id: null,
                    jobcontract_id: contractId,
                    jobcontract_revision_id: newScope.details.jobcontract_revision_id
                };

                angular.extend($scope.details, newScope.details);
                angular.extend($scope.hour, newScope.hour || contractRevisionIdObj);
                angular.extend($scope.pay, newScope.pay || contractRevisionIdObj);

                if (newScope.health &&
                    newScope.health.provider &&
                    newScope.health.provider != $scope.health.provider) {
                    ContactService.getOne(newScope.health.provider).then(function(contact){
                        $scope.health.provider_contact = contact;
                    });
                }

                if (newScope.health &&
                    newScope.health.provider_life_insurance &&
                    newScope.health.provider_life_insurance != $scope.health.provider_life_insurance) {
                    ContactService.getOne(newScope.health.provider_life_insurance).then(function(contact){
                        $scope.health.provider_life_insurance_contact = contact;
                    });
                }

                angular.extend($scope.health, newScope.health || contractRevisionIdObj);
                angular.extend($scope.pension, newScope.pension || contractRevisionIdObj);

                angular.forEach($scope.leave, function(leaveType, leaveTypeId){
                    angular.extend(leaveType, newScope.leave ? newScope.leave[leaveTypeId] || contractRevisionIdObj : contractRevisionIdObj);
                });
            }

            $q.all({
                details: ContractDetailsService.getOne({ jobcontract_id: contractId}),
                hour: ContractHourService.getOne({ jobcontract_id: contractId}),
                pay: ContractPayService.getOne({ jobcontract_id: contractId}),
                leave: ContractLeaveService.get({ jobcontract_id: contractId}),
                health: ContractHealthService.getOne({ jobcontract_id: contractId}),
                pension: ContractPensionService.getOne({ jobcontract_id: contractId})
            }).then(function(results){

                updateContractView(results);

                $scope.contractLoaded = true;

                $scope.$watch('contract.is_primary',function(){
                    $scope.isCollapsed = !+$scope.contract.is_primary;
                });

                $scope.$broadcast('hrjc-loader-hide');

            }).then(function(){
                promiseFiles = $q.all({
                    details: ContractFilesService.get($scope.details.jobcontract_revision_id,'civicrm_hrjobcontract_details'),
                    pension: ContractFilesService.get($scope.pension.jobcontract_revision_id,'civicrm_hrjobcontract_pension')
                });
            });

            $scope.modalContract = function(action, revisionEntityIdObj){
                $scope.$broadcast('hrjc-loader-show');

                var modalInstance,
                    options = {
                        controller: 'ModalContractCtrl',
                        targetDomEl: $rootElement.find('div').eq(0),
                        templateUrl: settings.pathApp+'views/modalForm.html?v=65463',
                        size: 'lg',
                        resolve: {
                            action: function(){
                                return action || 'view'
                            },
                            content: function(){
                                return null;
                            },
                            entity: function(){

                                if (!revisionEntityIdObj) {
                                    return {
                                        contract: $scope.contract,
                                        details: $scope.details,
                                        hour: $scope.hour,
                                        pay: $scope.pay,
                                        leave: $scope.leave,
                                        health: $scope.health,
                                        pension: $scope.pension
                                    };
                                }

                                return $q.all({
                                    details: ContractDetailsService.getOne({ jobcontract_revision_id: revisionEntityIdObj.details_revision_id }),
                                    hour: ContractHourService.getOne({ jobcontract_revision_id: revisionEntityIdObj.hour_revision_id }),
                                    pay: ContractPayService.getOne({ jobcontract_revision_id: revisionEntityIdObj.pay_revision_id }),
                                    leave: ContractLeaveService.get({ jobcontract_revision_id: revisionEntityIdObj.leave_revision_id }),
                                    health: ContractHealthService.getOne({ jobcontract_revision_id: revisionEntityIdObj.health_revision_id }),
                                    pension: ContractPensionService.getOne({ jobcontract_revision_id: revisionEntityIdObj.pension_revision_id })
                                }).then(function(results){

                                    var entity = {
                                            contract: $scope.contract
                                        },
                                        contractRevisionIdObj = {
                                            id: null,
                                            jobcontract_id: contractId,
                                            jobcontract_revision_id: results.details.jobcontract_revision_id
                                        };

                                    angular.extend(entity, angular.copy($scope.model));
                                    angular.extend(entity.details, results.details);
                                    angular.extend(entity.hour, results.hour || contractRevisionIdObj);
                                    angular.extend(entity.pay, results.pay || contractRevisionIdObj);
                                    angular.forEach(entity.leave, function(leaveType, leaveTypeId){
                                        angular.extend(leaveType, results.leave ? results.leave[leaveTypeId] || contractRevisionIdObj : contractRevisionIdObj);
                                    });
                                    angular.extend(entity.health, results.health || contractRevisionIdObj);
                                    angular.extend(entity.pension, results.pension || contractRevisionIdObj);

                                    return entity;
                                });
                            },
                            files: function(){

                                if (!revisionEntityIdObj) {
                                    return promiseFiles;
                                }

                                return $q.all({
                                    details: ContractFilesService.get(revisionEntityIdObj.details_revision_id,'civicrm_hrjobcontract_details'),
                                    pension: ContractFilesService.get(revisionEntityIdObj.pension_revision_id,'civicrm_hrjobcontract_pension')
                                })

                            },
                            utils: function(){
                                return $scope.utils
                            }
                        }
                    };

                switch(action){
                    case 'edit':
                        options.resolve.content = function(){
                            return {
                                allowSave: true,
                                isDisabled: false,
                                copy: {
                                    close: 'Cancel',
                                    save: 'Save without making a new revision',
                                    title: 'Edit contract'
                                }
                            }
                        };
                        break;
                    case 'change':
                        options.resolve.content = function(){
                            return {
                                allowSave: true,
                                isDisabled: false,
                                copy: {
                                    close: 'Cancel',
                                    save: 'Save and make a new revision',
                                    title: 'Change contract terms'
                                }
                            }
                        };
                        break;
                }

                modalInstance = $modal.open(options);

                modalInstance.result.then(function(results){

                    if (!results) {
                        return;
                    }

                    if ($scope.details.period_end_date ?
                        new Date($scope.details.period_end_date).getTime() !== new Date(results.details.period_end_date).getTime() :
                        !!$scope.details.period_end_date !== !!results.details.period_end_date) {

                        var isCurrent = !results.details.period_end_date || new Date(results.details.period_end_date) > new Date();

                        if (isCurrent != !!+$scope.$parent.contract.is_current) {
                            if (isCurrent) {
                                $scope.$parent.contract.is_current = '1';
                                $scope.$parent.$parent.contractCurrent.push($scope.$parent.contract);
                                $scope.$parent.$parent.contractPast.splice($scope.$parent.$parent.contractPast.indexOf($scope.$parent.contract),1);
                            } else {
                                $scope.$parent.contract.is_current = '0';
                                $scope.$parent.$parent.contractPast.push($scope.$parent.contract);
                                $scope.$parent.$parent.contractCurrent.splice($scope.$parent.$parent.contractCurrent.indexOf($scope.$parent.contract),1)
                            }
                        }
                    }

                    if (results.revisionCreated) {
                        var dateEffectiveRevisionCreated = new Date(results.revisionCreated.effective_date).setHours(0, 0, 0, 0),
                            dateEffectiveRevisionCurrent = new Date($scope.revisionCurrent.effective_date).setHours(0, 0, 0, 0),
                            dateToday = new Date().setHours(0, 0, 0, 0);

                        if ((dateEffectiveRevisionCreated <= dateToday &&
                            dateEffectiveRevisionCreated >= dateEffectiveRevisionCurrent) ||
                            (dateEffectiveRevisionCurrent > dateToday &&
                            dateEffectiveRevisionCreated <= dateEffectiveRevisionCurrent)) {
                            updateContractView(results);
                        }

                        $scope.revisionList.unshift(results.revisionCreated);
                        $scope.revisionDataList.unshift({
                            revisionEntityIdObj: results.revisionCreated,
                            details: results.details,
                            hour: results.hour,
                            pay: results.pay
                        });
                    } else {
                        var revisionListEntitiesView = ['details','hour','pay'], i, objExt;

                        updateContractView(results);

                        if ($scope.contract.is_primary != results.contract.is_primary) {
                            $scope.$parent.$parent.toggleIsPrimary($scope.contract.id);
                        }

                        angular.forEach($scope.revisionDataList, function(revisionData){
                            i = 0;
                            objExt = {};
                            while (revisionListEntitiesView[i]){
                                if (revisionData.revisionEntityIdObj[revisionListEntitiesView[i]+'_revision_id'] ==
                                    $scope.revisionCurrent[revisionListEntitiesView[i]+'_revision_id']) {
                                    objExt[revisionListEntitiesView[i]] = results[revisionListEntitiesView[i]];
                                    angular.extend(revisionData, objExt);
                                }
                                i++;
                            }

                        })
                    }

                    if (results.files) {
                        promiseFiles = $q.all({
                            details: ContractFilesService.get($scope.details.jobcontract_revision_id,'civicrm_hrjobcontract_details'),
                            pension: ContractFilesService.get($scope.pension.jobcontract_revision_id,'civicrm_hrjobcontract_pension')
                        });
                    }

                });
            };

            $scope.modalRevision = function(entity){
                $scope.$broadcast('hrjc-loader-show');
                if (!entity) {
                    return null;
                }

                var promiseEntityRevisionDataList = [],
                    apiMethod = entity != 'leave' ? 'getOne' : 'get',
                    i = 0, len = $scope.revisionList.length;

                for (i; i < len; i++){
                    promiseEntityRevisionDataList.push(API[apiMethod]('HRJob'+$filter('capitalize')(entity),{
                        jobcontract_revision_id: $scope.revisionList[i][entity+'_revision_id']
                    }));
                }

                var options = {
                    targetDomEl: $rootElement.find('div').eq(0),
                    size: 'lg',
                    controller: 'ModalRevisionCtrl',
                    templateUrl: settings.pathApp+'views/modalRevision.html?v=ergerg',
                    windowClass: 'modal-revision',
                    resolve: {
                        entity: function(){
                            return entity;
                        },
                        fields: function(){
                            return $scope.$parent.$parent.fields[entity];
                        },
                        model: function(){
                            return $scope.model[entity];
                        },
                        utils: function(){
                            return $scope.utils
                        },
                        revisionDataList: function(){
                            return $q.all(promiseEntityRevisionDataList);
                        },
                        revisionList: function(){
                            return $scope.revisionList
                        },
                        modalContract: function(){
                            return $scope.modalContract;
                        }
                    }
                };
                return $modal.open(options);
            }

            $scope.$on('updateContractView',function(){
                $scope.$broadcast('hrjc-loader-show');
                $q.all({
                    details: ContractDetailsService.getOne({ jobcontract_revision_id: $scope.revisionCurrent.details_revision_id }),
                    hour: ContractHourService.getOne({ jobcontract_revision_id: $scope.revisionCurrent.hour_revision_id }),
                    pay: ContractPayService.getOne({ jobcontract_revision_id: $scope.revisionCurrent.pay_revision_id }),
                    leave: ContractLeaveService.get({ jobcontract_revision_id: $scope.revisionCurrent.leave_revision_id }),
                    health: ContractHealthService.getOne({ jobcontract_revision_id: $scope.revisionCurrent.health_revision_id }),
                    pension: ContractPensionService.getOne({ jobcontract_revision_id: $scope.revisionCurrent.pension_revision_id })
                }).then(function(results){
                    updateContractView(results)
                    $scope.$broadcast('hrjc-loader-hide');
                }).then(function(){
                    promiseFiles = $q.all({
                        details: ContractFilesService.get($scope.details.jobcontract_revision_id,'civicrm_hrjobcontract_details'),
                        pension: ContractFilesService.get($scope.pension.jobcontract_revision_id,'civicrm_hrjobcontract_pension')
                    });
                });
            });

        }]);
});