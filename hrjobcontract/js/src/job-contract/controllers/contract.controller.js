/* eslint-env amd */

define([
  'common/lodash',
  'common/moment'
], function (_, moment) {
  'use strict';

  ContractController.__name = 'ContractController';
  ContractController.$inject = [
    '$filter', '$log', '$q', '$rootElement', '$route', '$scope', '$window', '$uibModal',
    'settings', 'apiService', 'contractService', 'contractDetailsService', 'contractHourService',
    'contractPayService', 'contractLeaveService', 'contractHealthService',
    'contractPensionService', 'contractFilesService', 'contactService',
    'contractRevisionListService', 'notificationService', 'utilsService'
  ];

  function ContractController ($filter, $log, $q, $rootElement, $route, $scope, $window, $modal,
    settings, API, contractService, contractDetailsService, contractHourService,
    contractPayService, contractLeaveService, contractHealthService,
    contractPensionService, contractFilesService, contactService,
    contractRevisionListService, notificationService, utilsService) {
    $log.debug('Controller: ContractController');

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

    $scope.modalContract = modalContract;
    $scope.modalRevision = modalRevision;
    $scope.showRevisions = showRevisions;
    vm.fetchRevisionDetails = fetchRevisionDetails;

    (function init () {
      initListeners();

      _.extend($scope, _.cloneDeep($scope.model));

      contractService
        .fullDetails(contractId)
        .then(function (results) {
          updateContractView(results);

          $scope.$watch('contract.is_primary', function () {
            $scope.isCollapsed = !+$scope.contract.is_primary;
          });

          $scope.$broadcast('hrjc-loader-show');
          // Fetching revision list form contractRevisionListService service
          contractRevisionListService.fetchRevisions(contractId).then(function (result) {
            $scope.revisionList = result.revisionList;
            $scope.revisionDataList = result.revisionDataList;
            $scope.$broadcast('hrjc-loader-hide');
            $scope.contractLoaded = true;
          });
        })
        .then(updateContractFiles);
    }());

    /**
     * Fetches the Revision Details for given revision
     * @param  {object} revision
     * @return {object}
     */
    function fetchRevisionDetails (revision) {
      var entity, revisionDetails;

      return $q.all([
        contractDetailsService.getOne({
          jobcontract_revision_id: revision.details_revision_id
        }),
        contractHourService.getOne({
          jobcontract_revision_id: revision.hour_revision_id
        }),
        contractHealthService.getOne({
          jobcontract_revision_id: revision.health_revision_id
        }),
        contractPayService.getOne({
          jobcontract_revision_id: revision.pay_revision_id
        }),
        contractPensionService.getOne({
          jobcontract_revision_id: revision.pension_revision_id
        }),
        contractLeaveService.getOne({
          jobcontract_revision_id: revision.leave_revision_id
        })
      ]).then(function (results) {
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
        mapAbsenceTypesWithContractLeaveData(entity.leave, revisionDetails.leave);

        return entity;
      });
    }

    function initListeners () {
      $scope.$on('updateContractView', function () {
        $scope.$broadcast('hrjc-loader-show');

        contractService
          .fullDetails($scope.revisionCurrent.jobcontract_id)
          .then(function (results) {
            updateContractView(results);
            $scope.$broadcast('hrjc-loader-hide');
          })
          .then(updateContractFiles);
      });
    }

    /**
     * Maps Absence Types with the leave data from the Contract
     * or sets default revision data if Contract leave data does not exists yet, if presented
     *
     * @param {Array} leaveEntity
     * @param {Array} leaveData
     * @param {Object} contractRevisionIdObj optional
     */
    function mapAbsenceTypesWithContractLeaveData (leaveEntity, leaveData, contractRevisionIdObj) {
      contractRevisionIdObj = contractRevisionIdObj || '';

      _.each(leaveEntity, function (entity) {
        _.extend(entity, leaveData
          ? _.find(leaveData, { leave_type: entity.leave_type }) ||
          contractRevisionIdObj : contractRevisionIdObj);
      });
    }

    function modalContract (action, revisionEntityIdObj) {
      var modalInstance, dateEffectiveRevisionCreated, dateEffectiveRevisionCurrent,
        dateToday, revisionData, isCurrentRevision, i, objExt;
      var revisionListEntitiesView = ['details', 'hour', 'pay'];
      var options = {
        controller: 'ModalContractController',
        appendTo: $rootElement.find('div').eq(0),
        templateUrl: settings.pathApp + 'views/modalForm.html?v=4448',
        windowClass: 'modal-contract',
        size: 'lg',
        resolve: {
          action: function () {
            return action || 'view';
          },
          content: function () {
            return {};
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
              details: contractFilesService.get(revisionEntityIdObj.details_revision_id, 'civicrm_hrjobcontract_details'),
              pension: contractFilesService.get(revisionEntityIdObj.pension_revision_id, 'civicrm_hrjobcontract_pension')
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

        contractService.updateHeaderInfo();
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
                details: contractFilesService.get(results.revisionCreated.details_revision_id, 'civicrm_hrjobcontract_details')
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

        if (results.haveEntitlementFieldsChanged) {
          $window.location.assign(utilsService.getManageEntitlementsPageURL($scope.contract.contact_id));
        } else {
          notificationService.success('CiviHR', 'Contract updated');
        }
      });
    }

    function modalRevision (entity) {
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
        controller: 'ModalRevisionController',
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
    }

    /**
     * Marks that the revisions (in a different tab) have been shown
     */
    function showRevisions () {
      $scope.revisionsShown = true;
    }

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
        contactService.getOne(newScope.health.provider).then(function (contact) {
          $scope.health.provider_contact = contact;
        });
      }

      if (newScope.health &&
        newScope.health.provider_life_insurance &&
        newScope.health.provider_life_insurance !== $scope.health.provider_life_insurance) {
        contactService.getOne(newScope.health.provider_life_insurance).then(function (contact) {
          $scope.health.provider_life_insurance_contact = contact;
        });
      }

      _.extend($scope.health, newScope.health || contractRevisionIdObj);
      _.extend($scope.pension, newScope.pension || contractRevisionIdObj);
      mapAbsenceTypesWithContractLeaveData($scope.leave, newScope.leave, contractRevisionIdObj);
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
        details: contractFilesService.get($scope.details.jobcontract_revision_id, 'civicrm_hrjobcontract_details'),
        pension: contractFilesService.get($scope.pension.jobcontract_revision_id, 'civicrm_hrjobcontract_pension')
      });

      promiseFiles.then(function (files) {
        $scope.files = files;
      });

      return promiseFiles;
    }
  }

  return ContractController;
});
