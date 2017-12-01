/* eslint-env amd */

define([
  'common/moment'
], function (moment) {
  'use strict';

  ModalChangeReasonController.__name = 'ModalChangeReasonController';
  ModalChangeReasonController.$inject = [
    '$log', '$scope', '$uibModalInstance', 'content', 'date', 'reasonId',
    'settings', 'contractRevisionService'
  ];

  function ModalChangeReasonController ($log, $scope, $modalInstance, content, date,
    reasonId, settings, contractRevisionService) {
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
  }

  return ModalChangeReasonController;
});
