define([
  'common/moment',
  'job-contract/controllers/controllers',
  'job-contract/services/contract-details',
  'job-contract/services/contract-hour',
  'job-contract/services/contract-pay',
  'job-contract/services/contract-leave',
  'job-contract/services/contract-pension',
  'job-contract/services/contract-health',
  'job-contract/services/contact',
  'job-contract/services/utils',
  'common/filters/angular-date/format-date'
], function(moment, controllers) {
  'use strict';

  controllers.controller('ContractCtrl', ['$scope', '$route', '$filter', '$uibModal', '$rootElement', '$q', 'settings',
    'API', 'ContractService', 'ContractDetailsService', 'ContractHourService', 'ContractPayService', 'ContractLeaveService',
    'ContractHealthService', 'ContractPensionService', 'ContractFilesService', 'ContactService', 'ContractRevisionList', '$log',
    function($scope, $route, $filter, $modal, $rootElement, $q, settings, API, ContractService, ContractDetailsService,
      ContractHourService, ContractPayService, ContractLeaveService, ContractHealthService,
      ContractPensionService, ContractFilesService, ContactService, ContractRevisionList, $log) {
      $log.debug('Controller: ContractCtrl');

      var vm = this;
      var contractId = $scope.contract.id,
        promiseFiles;

      $scope.contractLoaded = false;
      $scope.revisionsShown = false;
      $scope.isCollapsed = true;
      $scope.files = {};
      $scope.revisionCurrent = {};
      $scope.revisionList = [];
      $scope.revisionDataList = [];

      angular.extend($scope, angular.copy($scope.model));

      function updateContractView(newScope) {
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
          ContactService.getOne(newScope.health.provider).then(function(contact) {
            $scope.health.provider_contact = contact;
          });
        }

        if (newScope.health &&
          newScope.health.provider_life_insurance &&
          newScope.health.provider_life_insurance != $scope.health.provider_life_insurance) {
          ContactService.getOne(newScope.health.provider_life_insurance).then(function(contact) {
            $scope.health.provider_life_insurance_contact = contact;
          });
        }

        angular.extend($scope.health, newScope.health || contractRevisionIdObj);
        angular.extend($scope.pension, newScope.pension || contractRevisionIdObj);

        angular.forEach($scope.leave, function(leaveType, leaveTypeId) {
          angular.extend(leaveType, newScope.leave ? newScope.leave[leaveTypeId] || contractRevisionIdObj : contractRevisionIdObj);
        });
      };

      /**
       * Updates the contract list view,
       * by sorting the contract into current or past
       * depending on the period end date of the contract.
       *
       * @param {string || date} newEndDate the date specified by the user
       */
      function updateContractList(newEndDate) {
        var isCurrentContract = !newEndDate ? true : (moment().diff(newEndDate, "day") <= 0);

        if (isCurrentContract) {
          $scope.$parent.contract.is_current = '1';
          $scope.$parent.contractCurrent.push($scope.$parent.contract);
          $scope.$parent.contractPast.splice($scope.$parent.contractPast.indexOf($scope.$parent.contract), 1);
        } else {
          $scope.$parent.contract.is_current = '0';
          $scope.$parent.contractPast.push($scope.$parent.contract);
          $scope.$parent.contractCurrent.splice($scope.$parent.contractCurrent.indexOf($scope.$parent.contract), 1)
        }
      }

      function updateContractFiles() {

        promiseFiles = $q.all({
          details: ContractFilesService.get($scope.details.jobcontract_revision_id, 'civicrm_hrjobcontract_details'),
          pension: ContractFilesService.get($scope.pension.jobcontract_revision_id, 'civicrm_hrjobcontract_pension')
        });

        promiseFiles.then(function(files) {
          $scope.files = files;
        });

        return promiseFiles;
      }

      ContractService
        .fullDetails(contractId)
        .then(function(results) {
          updateContractView(results);

          $scope.contractLoaded = true;

          $scope.$watch('contract.is_primary', function() {
            $scope.isCollapsed = !+$scope.contract.is_primary;
          });

          $scope.$broadcast('hrjc-loader-show');
          // Fetching revision list form ContractRevisionList service
          ContractRevisionList.fetchRevisions(contractId).then(function(result){
            $scope.revisionList = result.revisionList;
            $scope.revisionDataList = result.revisionDataList;
            $scope.$broadcast('hrjc-loader-hide');
          });
        })
        .then(updateContractFiles);

      /**
       * Fetches the Revision Details for given revision
       * @param  {object} revision
       * @return {object}
       */
      vm.fetchRevisionDetails = function(revision) {
        return $q.all([
          ContractDetailsService.getOne({
            jobcontract_revision_id: revision.details_revision_id
          }),
          ContractHourService.getOne({
            jobcontract_revision_id: revision.hour_revision_id
          }),
          ContractHealthService.getOne({
            jobcontract_revision_id: revision.health_revision_id
          }),
          ContractPayService.getOne({
            jobcontract_revision_id: revision.pay_revision_id
          }),
          ContractPensionService.getOne({
            jobcontract_revision_id: revision.pension_revision_id
          }),
          ContractLeaveService.getOne({
            jobcontract_revision_id: revision.leave_revision_id
          })
        ])
        .then(function(results) {
          var revisionDetails = {
            "details": results[0],
            "hour": results[1],
            "health": results[2],
            "pay": results[3],
            "pension": results[4],
            "leave": results[5]
          };
          var entity = {
            contract: $scope.contract
          }
          angular.extend(entity, angular.copy($scope.model));
          angular.extend(entity.details, revisionDetails.details);
          angular.extend(entity.hour, revisionDetails.hour);
          angular.extend(entity.health, revisionDetails.health);
          angular.extend(entity.pay, revisionDetails.pay);
          angular.extend(entity.pension, revisionDetails.pension);
          angular.forEach(entity.leave, function(leaveType, leaveTypeId) {
            angular.extend(leaveType, revisionDetails.leave ? revisionDetails.leave[leaveTypeId] : '');
          });

          return entity;
        });
      }

      $scope.modalContract = function(action, revisionEntityIdObj) {
        $scope.$broadcast('hrjc-loader-show');

        var modalInstance,
          options = {
            controller: 'ModalContractCtrl',
            appendTo: $rootElement.find('div').eq(0),
            templateUrl: settings.pathApp + 'views/modalForm.html?v=4448',
            windowClass: 'modal-contract',
            size: 'lg',
            resolve: {
              action: function() {
                return action || 'view'
              },
              content: function() {
                return null;
              },
              entity: function() {
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

                return vm.fetchRevisionDetails(revisionEntityIdObj);
              },
              files: function() {

                if (!revisionEntityIdObj) {
                  return promiseFiles;
                }

                return $q.all({
                  details: ContractFilesService.get(revisionEntityIdObj.details_revision_id, 'civicrm_hrjobcontract_details'),
                  pension: ContractFilesService.get(revisionEntityIdObj.pension_revision_id, 'civicrm_hrjobcontract_pension')
                })

              },
              utils: function() {
                return $scope.utils
              }
            }
          };

        switch (action) {
          case 'edit':
            options.resolve.content = function() {
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
            options.resolve.content = function() {
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

        modalInstance.result.then(function(results) {

          if (!results) {
            return;
          }

          ContractService.updateHeaderInfo();
          updateContractView(results);
          updateContractList(results.details.period_end_date);

          if (results.revisionCreated) {

            var dateEffectiveRevisionCreated = moment(new Date(results.revisionCreated.effective_date)),
              dateEffectiveRevisionCurrent = moment(new Date($scope.revisionCurrent.effective_date)),
              dateToday = moment(),
              revisionData = {
                revisionEntityIdObj: results.revisionCreated,
                details: results.details,
                hour: results.hour,
                pay: results.pay
              },
              isCurrentRevision = dateEffectiveRevisionCurrent.diff(dateToday, "day") <= 0 || dateEffectiveRevisionCurrent.diff(dateEffectiveRevisionCreated, "day") <= 0;


            if (results.files) {
              if (isCurrentRevision) {
                updateContractFiles().then(function(files) {
                  revisionData.files = files;
                });
              } else {
                $q.all({
                  details: ContractFilesService.get(results.revisionCreated.details_revision_id, 'civicrm_hrjobcontract_details')
                }).then(function(files) {
                  revisionData.files = files;
                });
              }
            }

            $scope.revisionList.unshift(results.revisionCreated);
            $scope.revisionDataList.unshift(revisionData);

          } else {
            var revisionListEntitiesView = ['details', 'hour', 'pay'],
              i, objExt;

            if ($scope.contract.is_primary != results.contract.is_primary) {
              $scope.$parent.$parent.toggleIsPrimary($scope.contract.id);
            }

            angular.forEach($scope.revisionDataList, function(revisionData) {
              i = 0;
              objExt = {};
              while (revisionListEntitiesView[i]) {
                if (revisionData.revisionEntityIdObj[revisionListEntitiesView[i] + '_revision_id'] ==
                  $scope.revisionCurrent[revisionListEntitiesView[i] + '_revision_id']) {
                  objExt[revisionListEntitiesView[i]] = results[revisionListEntitiesView[i]];

                  if (revisionListEntitiesView[i] == 'details' && results.files) {
                    updateContractFiles().then(function(files) {
                      objExt.files = files;
                      angular.extend(revisionData, objExt);
                    });
                  }

                  angular.extend(revisionData, objExt);
                }
                i++;
              }

            })
          }

          CRM.refreshParent('#hrjobroles');

        });
      };

      /**
       * Marks that the revisions (in a different tab) have been shown
       */
      $scope.showRevisions = function() {
        $scope.revisionsShown = true;
      }

      $scope.modalRevision = function(entity) {
        $scope.$broadcast('hrjc-loader-show');
        if (!entity) {
          return null;
        }

        var promiseEntityRevisionDataList = [],
          apiMethod = entity != 'leave' ? 'getOne' : 'get',
          i = 0,
          len = $scope.revisionList.length;

        for (i; i < len; i++) {
          promiseEntityRevisionDataList.push(API[apiMethod]('HRJob' + $filter('capitalize')(entity), {
            jobcontract_revision_id: $scope.revisionList[i][entity + '_revision_id']
          }));
        }

        var options = {
          appendTo: $rootElement.find('div').eq(0),
          size: 'lg',
          controller: 'ModalRevisionCtrl',
          templateUrl: settings.pathApp + 'views/modalRevision.html?v=1234',
          windowClass: 'modal-revision',
          resolve: {
            entity: function() {
              return entity;
            },
            fields: function() {
              return $scope.$parent.$parent.fields[entity];
            },
            model: function() {
              return $scope.model[entity];
            },
            utils: function() {
              return $scope.utils
            },
            revisionDataList: function() {
              return $q.all(promiseEntityRevisionDataList);
            },
            revisionList: function() {
              return $scope.revisionList
            },
            modalContract: function() {
              return $scope.modalContract;
            }
          }
        };
        return $modal.open(options);
      }

      $scope.$on('updateContractView', function() {
        $scope.$broadcast('hrjc-loader-show');

        ContractService
          .fullDetails($scope.revisionCurrent.jobcontract_id)
          .then(function(results) {
            updateContractView(results);
            $scope.$broadcast('hrjc-loader-hide');
          })
          .then(updateContractFiles);
      });
    }
  ]);
});
