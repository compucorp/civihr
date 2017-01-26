define([
    'common/lodash',
    'job-contract/controllers/controllers',
    'job-contract/services/contract'
], function (_, controllers) {
    'use strict';

    controllers.controller('RevisionListCtrl',['$scope', '$filter', '$q', '$uibModal', '$rootElement', 'settings', 'ContractService',
        'ContractDetailsService', 'ContractHourService', 'ContractPayService', 'ContractFilesService', '$log', 'ContractRevisionService',
        function ($scope, $filter, $q, $modal, $rootElement, settings, ContractService,
                 ContractDetailsService, ContractHourService, ContractPayService, ContractFilesService, $log, ContractRevisionService) {
            $log.debug('Controller: RevisionListCtrl');

            var contractId = $scope.contract.id,
                revisionDataListLocal = $scope.revisionDataList;

            $scope.currentPage = 1;
            $scope.itemsPerPage = 5;
            $scope.maxSize = 5;
            $scope.sortCol = 'revisionEntityIdObj.effective_date';
            $scope.sortReverse = true;
            $scope.display = {
                effectiveDate: true,
                position: true,
                payScale: true,
                totalSalary: true,
                hours: true,
                placeOfWork: true,
                recordedBy: true
            };


            $scope.createPage = function(){
                var start = (($scope.currentPage - 1) * $scope.itemsPerPage),
                    end = start + $scope.itemsPerPage;

                $scope.revisionDataListPage = revisionDataListLocal.slice(start, end);
            }

            $scope.sortBy = function(sortCol, sortReverse){

                if (typeof sortCol !== 'undefined') {

                    if ($scope.sortCol == sortCol) {
                        $scope.sortReverse = !$scope.sortReverse;
                    } else {
                        $scope.sortCol = sortCol;
                    }

                }

                if (typeof sortReverse !== 'undefined') {
                    $scope.sortReverse = sortReverse;
                }

                revisionDataListLocal = $filter('orderBy')($scope.revisionDataList, $scope.sortCol, $scope.sortReverse);
            };

            function setCurrentRevision(){
                var revisionCurrent, i = 0;

                if ($scope.revisionList.length) {
                    var revisionList = $filter('orderBy')($scope.revisionList, ['effective_date','id']);

                    angular.forEach(revisionList, function(revision){
                        if (new Date(revision.effective_date).setHours(0, 0, 0, 0) <= new Date().setHours(0, 0, 0, 0)) {
                            revisionCurrent = revision;
                        }
                    });

                    if (!revisionCurrent) {
                        do {
                            revisionCurrent = revisionList[i];
                            i++;
                        } while (revisionList[i] && revisionList[i-1].effective_date == revisionList[i].effective_date);
                    }

                    angular.extend($scope.revisionCurrent,revisionCurrent);
                    return revisionCurrent.id;
                }
                return null;
            }

            /**
             * Takes in a bulk of data, the result of a joined api call, and
             * returns an object containing such data divided in different properties
             *
             * @param  {Object} aggregated
             * @return {Object}
             */
            function expandAggregatedRevisionDetails(aggregated) {
              return {
                details: {
                  position: aggregated['details_revision_id.position'],
                  location: aggregated['details_revision_id.location'],
                },
                hour: {
                  hours_type: aggregated['hour_revision_id.hours_type']
                },
                pay: {
                  pay_annualized_est: aggregated['pay_revision_id.pay_annualized_est'],
                  pay_currency: aggregated['pay_revision_id.pay_currency'],
                  pay_scale: aggregated['pay_revision_id.pay_scale']
                }
              };
            }

            /**
             * Fetches the details of the given revision, like files, pay, etc
             *
             * The bulk of the data is fetched via a joined api call, whose aggregated
             * result is then expanded before being returned
             *
             * @param  {Object} revision
             * @return {Promise} resolves to an object containing the details
             */
            function fetchRevisionDetails(revision) {
              revision.effective_date = revision.effective_date || '';

              return $q.all({
                files: $q.all({
                  details: ContractFilesService.get(revision.details_revision_id,'civicrm_hrjobcontract_details')
                }),
                aggregated: ContractRevisionService.get({
                  action: 'getsingle',
                  json: {
                    sequential: 1,
                    id: revision.id,
                    return: [
                      'details_revision_id.position',
                      'details_revision_id.location',
                      'hour_revision_id.hours_type',
                      'pay_revision_id.pay_scale',
                      'pay_revision_id.pay_annualized_est',
                      'pay_revision_id.pay_currency'
                    ]
                  }
                })
                .$promise.then(function (aggregated) {
                  return aggregated;
                })
              })
              .then(function (results) {
                return _.assign({
                  revisionEntityIdObj: revision,
                  files: results.files
                }, expandAggregatedRevisionDetails(results.aggregated));
              });
            }

            function fetchRevisions(contractId){
                $scope.revisionList.length = 0;
                $scope.revisionDataList.length = 0;

                ContractService.getRevision(contractId)
                  .then(function (revisionList) {
                    revisionList = $filter('orderBy')(revisionList, ['-effective_date', '-id']);
                    Array.prototype.push.apply($scope.revisionList, revisionList);

                    return $q.all(revisionList.map(fetchRevisionDetails));
                  })
                  .then(function(results){
                    Array.prototype.push.apply($scope.revisionDataList,results);

                    $scope.$broadcast('hrjc-loader-hide');
                  });
            };

            fetchRevisions(contractId);

            function urlCSVBuild(){
                var url = settings.pathReport + (settings.pathReport.indexOf('?') > -1 ? '&' : '?' ),
                    fields = $scope.fields;

                angular.forEach(fields, function(entityFields, entityName){
                    url += 'fields['+entityName+'_revision_id]=1&';
                    angular.forEach(entityFields, function(field){
                        url += 'fields['+entityName+'_'+field.name+']=1&';
                    })
                });

                url += 'fields[sort_name]=1' +
                        '&fields[first_name]=1' +
                        '&fields[last_name]=1' +
                        '&fields[external_identifier]=1' +
                        '&fields[email]=1' +
                        '&fields[street_address]=1' +
                        '&fields[city]=1' +
                        '&fields[name]=1' +
                        '&fields[contract_contact_id]=1' +
                        '&fields[contract_contract_id]=1' +
                        '&fields[jobcontract_revision_id]=1' +
                        '&fields[change_reason]=1' +
                        '&fields[created_date]=1' +
                        '&fields[effective_date]=1' +
                        '&fields[modified_date]=1' +
                        '&order_bys[1][column]=id&order_bys[1][order]=ASC' +
                        '&order_bys[2][column]=civicrm_hrjobcontract_revision_revision_id&order_bys[2][order]=DESC' +
                        '&order_bys[3][column]=-&order_bys[3][order]=ASC' +
                        '&order_bys[4][column]=-&order_bys[4][order]=ASC' +
                        '&order_bys[5][column]=-&order_bys[5][order]=ASC' +
                        '&contract_id_op=eq&permission=access+CiviReport' +
                        '&row_count=' +
                        '&_qf_Summary_submit_csv=Preview+CSV' +
                        '&groups=' +
                        '&contract_id_value='+contractId +
                        '&group_bys[civicrm_hrjobcontract_revision_revision_id]=1&task=report_instance.csv';

                return url;
            };
            $scope.urlCSV = urlCSVBuild();

            $scope.deleteRevision = function(revisionId, e) {

                if ($scope.revisionList.length == 1) {
                    e.stopPropagation();
                    return;
                }

                if (!revisionId || typeof +revisionId !== 'number') {
                    return;
                }

                var modalInstance = $modal.open({
                    appendTo: $rootElement.find('div').eq(0),
                    templateUrl: settings.pathApp+'views/modalDialog.html',
                    size: 'sm',
                    controller: 'ModalDialogCtrl',
                    resolve: {
                        content: function(){
                            return {
                                msg: 'Are you sure you want to delete this job contract revision?'
                            };
                        }
                    }
                });

                modalInstance.result.then(function(confirm){
                    if (confirm) {
                        $scope.$broadcast('hrjc-loader-show');
                        ContractService.deleteRevision(revisionId).then(function(results){
                            var i = 0, len = $scope.revisionList.length;
                            if (!results.is_error) {

                                for (i; i < len; i++) {
                                    if ($scope.revisionList[i].id == revisionId) {
                                        $scope.revisionList.splice(i,1);
                                        $scope.revisionDataList.splice(i,1);
                                        break;
                                    }
                                }

                                $scope.sortBy();
                                $scope.createPage();

                                if ($scope.revisionCurrent.id != setCurrentRevision()) {
                                    $scope.$emit('updateContractView');
                                    return
                                }

                                $scope.$broadcast('hrjc-loader-hide');
                            }
                        });
                    }
                })

            }

            $scope.modalRevisionEdit = function(revisionEntityIdObj){
                var date = revisionEntityIdObj.effective_date,
                    reasonId = revisionEntityIdObj.change_reason;

                var modalChangeReason = $modal.open({
                    appendTo: $rootElement.find('div').eq(0),
                    templateUrl: settings.pathApp+'views/modalChangeReason.html?v='+(new Date()).getTime(),
                    controller: 'ModalChangeReasonCtrl',
                    resolve: {
                        content: function() {
                            return {
                                copy: {
                                    title: 'Edit revision data'
                                }
                            }
                        },
                        date: function(){
                            return date;
                        },
                        reasonId: function(){
                            return reasonId;
                        }
                    }
                });

                modalChangeReason.result.then(function(results){
                    if (results.date != date || results.reasonId != reasonId) {
                        ContractService.saveRevision({
                            id: revisionEntityIdObj.id,
                            change_reason: results.reasonId,
                            effective_date: results.date
                        }).then(function(){
                            revisionEntityIdObj.effective_date = results.date;
                            revisionEntityIdObj.change_reason = results.reasonId;

                            $scope.sortBy();
                            $scope.createPage();

                            if ($scope.revisionCurrent.id != setCurrentRevision()) {
                                $scope.$emit('updateContractView');
                            }
                        });
                    }
                });
            };

            $scope.$watch('currentPage', function() {
                $scope.createPage();
            });

            $scope.$watch('revisionDataList.length', function(lengthNow, lengthPrev) {
                revisionDataListLocal = $scope.revisionDataList;
                if (lengthNow > lengthPrev) {
                    setCurrentRevision();
                }
                $scope.sortBy();
                $scope.createPage();
            });

        }]);
});
