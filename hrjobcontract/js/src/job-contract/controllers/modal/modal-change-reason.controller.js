/* eslint-env amd */

define([
  'common/moment'
], function (moment) {
  'use strict';

  ModalChangeReasonController.$inject = [
    '$log', '$scope', '$uibModalInstance', 'crmAngService', 'content', 'date', 'reasonId',
    'settings', 'contractRevisionService', 'contractService'
  ];

  function ModalChangeReasonController ($log, $scope, $modalInstance, crmAngService, content, date,
    reasonId, settings, contractRevisionService, contractService) {
    var copy;

    $log.debug('Controller: ModalChangeReasonController');

    content = content || {};
    copy = content.copy || {};
    copy.title = copy.title || 'Revision data';

    $scope.change_reason = reasonId || '';
    $scope.copy = copy;
    $scope.effective_date = date || '';
    $scope.isPast = false;

    $scope.cancel = cancel;
    $scope.dpOpen = dpOpen;
    $scope.openRevisionChangeReasonEditor = openRevisionChangeReasonEditor;
    $scope.save = save;

    (function init () {
      initWatchers();
    }());

    function cancel () {
      $modalInstance.dismiss('cancel');
    }

    function dpOpen ($event, opened) {
      $event.preventDefault();
      $event.stopPropagation();

      $scope[opened] = true;
    }

    function initWatchers () {
      $scope.$watch('effective_date', function (dateSelected) {
        $scope.isPast = (new Date(dateSelected).setHours(0, 0, 0, 0) < new Date().setHours(0, 0, 0, 0));
      });
    }

    function save () {
      contractRevisionService.validateEffectiveDate({
        contact_id: settings.contactId,
        effective_date: $scope.effective_date
      })
        .then(function (result) {
          if (result.success) {
            $modalInstance.close({
              reasonId: $scope.change_reason,
              date: $scope.effective_date ? moment($scope.effective_date).format('YYYY-MM-DD') : ''
            });
          } else {
            CRM.alert(result.message, 'Error', 'error');
            $scope.$broadcast('hrjc-loader-hide');
          }
        });
    }

    /**
     * Opens the revision change reasons for editing
     */
    function openRevisionChangeReasonEditor () {
      crmAngService.loadForm('/civicrm/admin/options/hrjc_revision_change_reason?reset=1')
        .on('crmUnload', function () {
          contractService.getRevisionOptions('change_reason', true)
            .then(function (result) {
              $scope.options.contract.change_reason = result.obj;
            });
        });
    }
  }

  return { ModalChangeReasonController: ModalChangeReasonController };
});
