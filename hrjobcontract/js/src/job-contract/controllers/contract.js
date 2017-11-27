/* eslint-env amd */

define([
  'common/lodash',
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
], function (_, moment, controllers) {
  'use strict';

  controllers.controller('ContractCtrl', ['$scope', '$route', '$filter', '$uibModal', '$rootElement', '$q', '$window', 'settings',
    'API', 'ContractService', 'ContractDetailsService', 'ContractHourService', 'ContractPayService', 'ContractLeaveService',
    'ContractHealthService', 'ContractPensionService', 'ContractFilesService', 'ContactService', 'ContractRevisionList', '$log',
    'UtilsService',
    function ($scope, $route, $filter, $modal, $rootElement, $q, $window, settings, API, ContractService, ContractDetailsService,
      ContractHourService, ContractPayService, ContractLeaveService, ContractHealthService,
      ContractPensionService, ContractFilesService, ContactService, ContractRevisionList, $log, UtilsService) {
      $log.debug('Controller: ContractCtrl');

      var promiseFiles;
      var contractId = $scope.contract.id;
      var vm = this;

      $scope.contractLoaded = false;
      $scope.revisionsShown = false;
      $scope.isCollapsed = true;
      $scope.files = {};
      $scope.revisionCurrent = {};
      $scope.revisionList = [];
      $scope.revisionDataList = [];

      _.extend($scope, _.cloneDeep($scope.model));

      function updateContractView (newScope) {
        var contractRevisionIdObj = {
          id: null,
          jobcontract_id: contractId,
          jobcontract_revision_id: newScope.details.jobcontract_revision_id
        };

        _.extend($scope.details, newScope.details);
        _.extend($scope.hour, newScope.hour || contractRevisionIdObj);
        _.extend($scope.pay, newScope.pay || contractRevisionIdObj);

        if (newScope.health &&
          newScope.health.provider &&
          newScope.health.provider !== $scope.health.provider) {
          ContactService.getOne(newScope.health.provider).then(function (contact) {
            $scope.health.provider_contact = contact;
          });
        }

        if (newScope.health &&
          newScope.health.provider_life_insurance &&
          newScope.health.provider_life_insurance !== $scope.health.provider_life_insurance) {
          ContactService.getOne(newScope.health.provider_life_insurance).then(function (contact) {
            $scope.health.provider_life_insurance_contact = contact;
          });
        }

        _.extend($scope.health, newScope.health || contractRevisionIdObj);
        _.extend($scope.pension, newScope.pension || contractRevisionIdObj);

        _.each($scope.leave, function (leaveType, leaveTypeId) {
          _.extend(leaveType, newScope.leave ? newScope.leave[leaveTypeId] || contractRevisionIdObj : contractRevisionIdObj);
        });
      }

      /**
       * Updates the contract list view,
       * by sorting the contract into current or past
       * depending on the period end date of the contract.
       *
       * @param {string || date} newEndDate the date specified by the user
       */
      function updateContractList (newEndDate) {
        var isCurrentContract = !newEndDate ? true : (moment().diff(newEndDate, 'day') <= 0);
        var contract = $scope.$parent.contract;
        var currentContracts = $scope.$parent.contractCurrent;
        var pastContracts = $scope.$parent.contractPast;
        var currentContractIndex = currentContracts.indexOf(contract);
        var pastContractIndex = pastContracts.indexOf(contract);

        if (isCurrentContract) {
          contract.is_current = '1';
          if (currentContractIndex + 1) {
            _.extend(currentContracts[currentContractIndex], contract);
          } else {
            pastContracts.splice(pastContractIndex);
            currentContracts.push(contract);
          }
        } else {
          contract.is_current = '0';
          if (pastContractIndex + 1) {
            _.extend(pastContracts[pastContractIndex], contract);
          } else {
            pastContracts.push(contract);
            currentContracts.splice(currentContractIndex);
          }
        }
      }

      function updateContractFiles () {
        promiseFiles = $q.all({
          details: ContractFilesService.get($scope.details.jobcontract_revision_id, 'civicrm_hrjobcontract_details'),
          pension: ContractFilesService.get($scope.pension.jobcontract_revision_id, 'civicrm_hrjobcontract_pension')
        });

        promiseFiles.then(function (files) {
          $scope.files = files;
        });

        return promiseFiles;
      }

      ContractService
        .fullDetails(contractId)
        .then(function (results) {
          updateContractView(results);

          $scope.contractLoaded = true;

          $scope.$watch('contract.is_primary', function () {
            $scope.isCollapsed = !+$scope.contract.is_primary;
          });

          $scope.$broadcast('hrjc-loader-show');
          // Fetching revision list form ContractRevisionList service
          ContractRevisionList.fetchRevisions(contractId).then(function (result) {
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
      vm.fetchRevisionDetails = function (revision) {
        var entity, revisionDetails;

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
        .then(function (results) {
          revisionDetails = {
            'details': results[0],
            'hour': results[1],
            'health': results[2],
            'pay': results[3],
            'pension': results[4],
            'leave': results[5]
          };
          entity = { contract: $scope.contract };
          _.extend(entity, _.cloneDeep($scope.model));
          _.extend(entity.details, revisionDetails.details);
          _.extend(entity.hour, revisionDetails.hour);
          _.extend(entity.health, revisionDetails.health);
          _.extend(entity.pay, revisionDetails.pay);
          _.extend(entity.pension, revisionDetails.pension);
          _.each(entity.leave, function (leaveType, leaveTypeId) {
            _.extend(leaveType, revisionDetails.leave ? revisionDetails.leave[leaveTypeId] : '');
          });

          return entity;
        });
      };

      $scope.modalContract = function (action, revisionEntityIdObj) {
        var modalInstance, dateEffectiveRevisionCreated, dateEffectiveRevisionCurrent,
          dateToday, revisionData, isCurrentRevision, i, objExt;
        var revisionListEntitiesView = ['details', 'hour', 'pay'];
        var options = {
          controller: 'ModalContractCtrl',
          appendTo: $rootElement.find('div').eq(0),
          templateUrl: settings.pathApp + 'views/modalForm.html?v=4448',
          windowClass: 'modal-contract',
          size: 'lg',
          resolve: {
            action: function () {
              return action || 'view';
            },
            content: function () {
              return null;
            },
            entity: function () {
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
            files: function () {
              if (!revisionEntityIdObj) {
                return promiseFiles;
              }

              return $q.all({
                details: ContractFilesService.get(revisionEntityIdObj.details_revision_id, 'civicrm_hrjobcontract_details'),
                pension: ContractFilesService.get(revisionEntityIdObj.pension_revision_id, 'civicrm_hrjobcontract_pension')
              });
            },
            utils: function () {
              return $scope.utils;
            }
          }
        };

        $scope.$broadcast('hrjc-loader-show');

        switch (action) {
          case 'edit':
            options.resolve.content = function () {
              return {
                allowSave: true,
                isDisabled: false,
                copy: {
                  close: 'Cancel',
                  save: 'Save without making a new revision',
                  title: 'Edit contract'
                }
              };
            };
            break;
          case 'change':
            options.resolve.content = function () {
              return {
                allowSave: true,
                isDisabled: false,
                copy: {
                  close: 'Cancel',
                  save: 'Save and make a new revision',
                  title: 'Change contract terms'
                }
              };
            };
            break;
        }

        modalInstance = $modal.open(options);

        modalInstance.result.then(function (results) {
          if (!results) {
            return;
          }

          ContractService.updateHeaderInfo();
          updateContractView(results);
          updateContractList(results.details.period_end_date);

          if (results.revisionCreated) {
            dateEffectiveRevisionCreated = moment(new Date(results.revisionCreated.effective_date));
            dateEffectiveRevisionCurrent = moment(new Date($scope.revisionCurrent.effective_date));
            dateToday = moment();
            revisionData = {
              revisionEntityIdObj: results.revisionCreated,
              details: results.details,
              hour: results.hour,
              pay: results.pay
            };
            isCurrentRevision = dateEffectiveRevisionCurrent.diff(dateToday, 'day') <= 0 || dateEffectiveRevisionCurrent.diff(dateEffectiveRevisionCreated, 'day') <= 0;

            if (results.files) {
              if (isCurrentRevision) {
                updateContractFiles().then(function (files) {
                  revisionData.files = files;
                });
              } else {
                $q.all({
                  details: ContractFilesService.get(results.revisionCreated.details_revision_id, 'civicrm_hrjobcontract_details')
                }).then(function (files) {
                  revisionData.files = files;
                });
              }
            }

            $scope.revisionList.unshift(results.revisionCreated);
            $scope.revisionDataList.unshift(revisionData);
          } else {
            if ($scope.contract.is_primary !== results.contract.is_primary) {
              $scope.$parent.$parent.toggleIsPrimary($scope.contract.id);
            }

            _.each($scope.revisionDataList, function (revisionData) {
              i = 0;
              objExt = {};
              while (revisionListEntitiesView[i]) {
                if (revisionData.revisionEntityIdObj[revisionListEntitiesView[i] + '_revision_id'] ===
                  $scope.revisionCurrent[revisionListEntitiesView[i] + '_revision_id']) {
                  objExt[revisionListEntitiesView[i]] = results[revisionListEntitiesView[i]];

                  if (revisionListEntitiesView[i] === 'details' && results.files) {
                    updateContractFiles().then(function (files) {
                      objExt.files = files;
                      _.extend(revisionData, objExt);
                    });
                  }

                  _.extend(revisionData, objExt);
                }
                i++;
              }
            });
          }

          CRM.refreshParent('#hrjobroles');
          $window.location.assign(UtilsService.getManageEntitlementsPageURL($scope.contract.contact_id));
        });
      };

      /**
       * Marks that the revisions (in a different tab) have been shown
       */
      $scope.showRevisions = function () {
        $scope.revisionsShown = true;
      };

      $scope.modalRevision = function (entity) {
        var options;
        var promiseEntityRevisionDataList = [];
        var apiMethod = entity !== 'leave' ? 'getOne' : 'get';
        var i = 0;
        var len = $scope.revisionList.length;

        $scope.$broadcast('hrjc-loader-show');
        if (!entity) {
          return null;
        }

        for (i; i < len; i++) {
          promiseEntityRevisionDataList.push(API[apiMethod]('HRJob' + $filter('capitalize')(entity), {
            jobcontract_revision_id: $scope.revisionList[i][entity + '_revision_id']
          }));
        }

        options = {
          appendTo: $rootElement.find('div').eq(0),
          size: 'lg',
          controller: 'ModalRevisionCtrl',
          templateUrl: settings.pathApp + 'views/modalRevision.html?v=1234',
          windowClass: 'modal-revision',
          resolve: {
            entity: function () {
              return entity;
            },
            fields: function () {
              return $scope.$parent.$parent.fields[entity];
            },
            model: function () {
              return $scope.model[entity];
            },
            utils: function () {
              return $scope.utils;
            },
            revisionDataList: function () {
              return $q.all(promiseEntityRevisionDataList);
            },
            revisionList: function () {
              return $scope.revisionList;
            },
            modalContract: function () {
              return $scope.modalContract;
            }
          }
        };
        return $modal.open(options);
      };

      $scope.$on('updateContractView', function () {
        $scope.$broadcast('hrjc-loader-show');

        ContractService
          .fullDetails($scope.revisionCurrent.jobcontract_id)
          .then(function (results) {
            updateContractView(results);
            $scope.$broadcast('hrjc-loader-hide');
          })
          .then(updateContractFiles);
      });
    }
  ]);
});
