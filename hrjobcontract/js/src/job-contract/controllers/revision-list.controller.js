/* eslint-env amd */

define([
  'common/angular',
  'common/lodash'
], function (angular, _) {
  'use strict';

  RevisionListController.__name = 'RevisionListController';
  RevisionListController.$inject = [
    '$filter', '$log', '$q', '$rootElement', '$rootScope', '$scope', '$uibModal',
    'settings', 'ContractService', 'ContractDetailsService', 'ContractHourService',
    'ContractPayService', 'ContractFilesService', 'ContractRevisionService',
    'ContractRevisionList'
  ];

  function RevisionListController ($filter, $log, $q, $rootElement, $rootScope,
    $scope, $modal, settings, ContractService, ContractDetailsService,
    ContractHourService, ContractPayService, ContractFilesService, ContractRevisionService,
    ContractRevisionList) {
    $log.debug('Controller: RevisionListController');

    var contractId = $scope.contract.id;
    var revisionDataListLocal = $scope.revisionDataList;

    $scope.changeReasons = $rootScope.options.contract.change_reason;
    $scope.currentPage = 1;
    $scope.itemsPerPage = 5;
    $scope.maxSize = 5;
    $scope.sortCol = 'revisionEntityIdObj.effective_date';
    $scope.sortReverse = true;
    $scope.urlCSV = urlCSVBuild();
    $scope.display = {
      effectiveDate: true,
      position: true,
      payScale: true,
      totalSalary: true,
      hours: true,
      placeOfWork: true,
      recordedBy: true,
      changeReason: true
    };

    $scope.createPage = createPage;
    $scope.deleteRevision = deleteRevision;
    $scope.modalRevisionEdit = modalRevisionEdit;
    $scope.sortBy = sortBy;

    (function init () {
      initWatchers();

      if (!$scope.revisionDataList) {
        $scope.$broadcast('hrjc-loader-show');

        // Fetching revision list form ContractRevisionList service
        ContractRevisionList.fetchRevisions(contractId).then(function (result) {
          $scope.revisionList = result.revisionList;
          $scope.revisionDataList = result.revisionDataList;
          $scope.$broadcast('hrjc-loader-hide');
        });
      }
    }());

    function createPage () {
      var start = (($scope.currentPage - 1) * $scope.itemsPerPage);
      var end = start + $scope.itemsPerPage;

      $scope.revisionDataListPage = revisionDataListLocal.slice(start, end);
    }

    function deleteRevision (revisionId, e) {
      if ($scope.revisionList.length === 1) {
        e.stopPropagation();
        return;
      }

      if (!revisionId || typeof +revisionId !== 'number') {
        return;
      }

      var modalInstance = $modal.open({
        appendTo: $rootElement.find('div').eq(0),
        templateUrl: settings.pathApp + 'views/modalDialog.html',
        size: 'sm',
        controller: 'ModalDialogController',
        resolve: {
          content: function () {
            return {
              msg: 'Are you sure you want to delete this job contract revision?'
            };
          }
        }
      });

      modalInstance.result.then(function (confirm) {
        if (confirm) {
          $scope.$broadcast('hrjc-loader-show');
          ContractService.deleteRevision(revisionId).then(function (results) {
            var i = 0;
            var len = $scope.revisionList.length;

            if (!results.is_error) {
              for (i; i < len; i++) {
                if ($scope.revisionList[i].id === revisionId) {
                  $scope.revisionList.splice(i, 1);
                  $scope.revisionDataList.splice(i, 1);
                  break;
                }
              }

              $scope.sortBy();
              $scope.createPage();

              if ($scope.revisionCurrent.id !== setCurrentRevision()) {
                $scope.$emit('updateContractView');
                return;
              }

              $scope.$broadcast('hrjc-loader-hide');
            }
          });
        }
      });
    }

    function initWatchers () {
      $scope.$watch('currentPage', function () {
        $scope.createPage();
      });

      $scope.$watch('revisionDataList.length', function (lengthNow, lengthPrev) {
        revisionDataListLocal = $scope.revisionDataList;
        if (lengthNow > lengthPrev) {
          setCurrentRevision();
        }
        $scope.sortBy();
        $scope.createPage();
      });
    }

    function modalRevisionEdit (revisionEntityIdObj) {
      var date = revisionEntityIdObj.effective_date;
      var reasonId = revisionEntityIdObj.change_reason;
      var modalChangeReason = $modal.open({
        appendTo: $rootElement.find('div').eq(0),
        templateUrl: settings.pathApp + 'views/modalChangeReason.html?v=' + (new Date()).getTime(),
        controller: 'ModalChangeReasonController',
        resolve: {
          content: function () {
            return {
              copy: {
                title: 'Edit revision data'
              }
            };
          },
          date: function () {
            return date;
          },
          reasonId: function () {
            return reasonId;
          }
        }
      });

      modalChangeReason.result.then(function (results) {
        if (results.date !== date || results.reasonId !== reasonId) {
          ContractService.saveRevision({
            id: revisionEntityIdObj.id,
            change_reason: results.reasonId,
            effective_date: results.date
          }).then(function () {
            revisionEntityIdObj.effective_date = results.date;
            revisionEntityIdObj.change_reason = results.reasonId;

            $scope.sortBy();
            $scope.createPage();

            if ($scope.revisionCurrent.id !== setCurrentRevision()) {
              $scope.$emit('updateContractView');
            }
          });
        }
      });
    }

    function setCurrentRevision () {
      var revisionCurrent;
      var i = 0;

      if ($scope.revisionList.length) {
        var revisionList = $filter('orderBy')($scope.revisionList, ['effective_date', 'id']);

        angular.forEach(revisionList, function (revision) {
          if (new Date(revision.effective_date).setHours(0, 0, 0, 0) <= new Date().setHours(0, 0, 0, 0)) {
            revisionCurrent = revision;
          }
        });

        if (!revisionCurrent) {
          do {
            revisionCurrent = revisionList[i];
            i++;
          } while (revisionList[i] && revisionList[i - 1].effective_date === revisionList[i].effective_date);
        }

        angular.extend($scope.revisionCurrent, revisionCurrent);
        return revisionCurrent.id;
      }
      return null;
    }

    function sortBy (sortCol, sortReverse) {
      if (typeof sortCol !== 'undefined') {
        if ($scope.sortCol === sortCol) {
          $scope.sortReverse = !$scope.sortReverse;
        } else {
          $scope.sortCol = sortCol;
        }
      }

      if (typeof sortReverse !== 'undefined') {
        $scope.sortReverse = sortReverse;
      }

      revisionDataListLocal = $filter('orderBy')($scope.revisionDataList, $scope.sortCol, $scope.sortReverse);
    }

    function urlCSVBuild () {
      var url = settings.pathReport + (settings.pathReport.indexOf('?') > -1 ? '&' : '?');
      var fields = $scope.fields;

      angular.forEach(fields, function (entityFields, entityName) {
        url += 'fields[' + entityName + '_revision_id]=1&';
        angular.forEach(entityFields, function (field) {
          url += 'fields[' + entityName + '_' + field.name + ']=1&';
        });
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
        '&order_bys[2][column]=civicrm_hrjobcontract_revision_revision_id&order_bys[2][order]=ASC' +
        '&order_bys[3][column]=-&order_bys[3][order]=ASC' +
        '&order_bys[4][column]=-&order_bys[4][order]=ASC' +
        '&order_bys[5][column]=-&order_bys[5][order]=ASC' +
        '&contract_id_op=eq&permission=access+CiviReport' +
        '&row_count=' +
        '&_qf_Summary_submit_csv=Preview+CSV' +
        '&groups=' +
        '&contract_id_value=' + contractId +
        '&group_bys[civicrm_hrjobcontract_revision_revision_id]=1';

      return url;
    }
  }

  return RevisionListController;
});
