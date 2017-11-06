/* eslint-env amd */

define([
  'common/angular',
  'common/lodash',
  'common/directives/loading',
  'common/directives/help-text.directive',
  'common/filters/time-unit-applier.filter',
  'common/models/contract',
  'common/services/pub-sub',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/shared/modules/components',
  'leave-absences/shared/models/absence-period.model',
  'leave-absences/shared/models/absence-type.model',
  'leave-absences/shared/components/leave-widget/leave-widget-absence-types-amount-taken.component',
  'leave-absences/shared/components/leave-widget/leave-widget-absence-types-available-balance.component',
  'leave-absences/shared/components/leave-widget/leave-widget-next-leave.component'
], function (angular, _) {
  angular.module('leave-absences.components.leave-widget', [
    'common.components',
    'common.directives',
    'common.filters',
    'common.models',
    'common.services',
    'leave-absences.components',
    'leave-absences.models',
    'leave-absences.settings'
  ])
  .component('leaveWidget', {
    bindings: {
      contactId: '<'
    },
    controller: leaveWidgetController,
    controllerAs: 'leaveWidget',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-widget/leave-widget.html';
    }]
  });

  leaveWidgetController.$inject = ['$log', '$q', '$scope', 'AbsencePeriod',
    'AbsenceType', 'Contract', 'OptionGroup', 'pubSub'];

  function leaveWidgetController ($log, $q, $scope, AbsencePeriod,
    AbsenceType, Contract, OptionGroup, pubSub) {
    var allowedLeaveStatuses = ['approved', 'admin_approved',
      'awaiting_approval', 'more_information_required'];
    var childComponents = 0;
    var vm = this;

    vm.absenceTypes = [];
    vm.currentAbsencePeriod = null;
    vm.jobContract = null;
    vm.loading = { childComponents: false, component: true };
    vm.leaveRequestStatuses = [];
    vm.sicknessAbsenceTypes = [];

    /**
     * Initializes the component by watching for events, and loading
     * dependencies.
     */
    (function init () {
      $log.debug('Controller: leaveWidgetController');
      initWatchers();
      loadDependencies();
    })();

    /**
     * Increases the child component counter and sets loading child components
     * to true.
     */
    function childComponentIsLoading () {
      childComponents++;
      vm.loading.childComponents = true;
    }

    /**
     * Decreases the child component counter. If there are no more child
     * components in the queue, it sets loading child components to false.
     */
    function childComponentIsReady () {
      childComponents--;

      if (childComponents <= 0) {
        childComponents = 0;
        vm.loading.childComponents = false;
      }
    }

    /**
     * Initializes watchers for child components and events that make the leave
     * widget refresh.
     */
    function initWatchers () {
      $scope.$on('LeaveWidget::childIsLoading', childComponentIsLoading);
      $scope.$on('LeaveWidget::childIsReady', childComponentIsReady);
      initWatchersForWidgetRefresh();
    }

    /**
     * Watches for events that make the leave widget refresh.
     */
    function initWatchersForWidgetRefresh () {
      var listOfEvents = ['LeaveRequest::new', 'LeaveRequest::edit',
        'LeaveRequest::deleted', 'LeaveRequest::updatedByManager',
        'contract:created', 'contract:deleted', 'contract-refresh'];

      listOfEvents.forEach(function (eventName) {
        pubSub.subscribe(eventName, loadDependencies);
      });
    }

    /**
     * Loads the contact's job contract and if the contact has a job contract
     * it then loads absence types, the current absence period, and leave request
     * statuses. When all dependencies are ready it sets loading component to
     * false.
     *
     * @return {Promise} - Returns an empty promise when all dependencies have
     * been loaded.
     */
    function loadDependencies () {
      loadCurrentJobContract().then(function () {
        return $q.all([
          loadAbsenceTypes(),
          loadCurrentAbsencePeriod(),
          loadLeaveRequestTypes()
        ]);
      })
      .finally(function () {
        vm.loading.component = false;
      });
    }

    /**
     * Loads all the absence types.
     *
     * @return {Promise}
     */
    function loadAbsenceTypes () {
      return AbsenceType.all({ is_active: true }).then(function (types) {
        vm.absenceTypes = types;
        vm.sicknessAbsenceTypes = types.filter(function (type) {
          return +type.is_sick;
        });
      });
    }

    /**
     * Loads the current job contract for the contact. If there are no job
     * contracts, it will reject the promise.
     *
     * @return {Promise}
     */
    function loadCurrentJobContract () {
      return Contract.all({
        contact_id: vm.contactId,
        deleted: false
      })
      .then(function (contracts) {
        vm.jobContract = _.find(contracts, function (contract) {
          return +contract.is_current;
        });
      })
      .then(function () {
        if (!vm.jobContract) {
          return $q.reject();
        }
      });
    }

    /**
     * Loads the status ID for absence types and stores only the allowed ones.
     *
     * @return {Promise}
     */
    function loadLeaveRequestTypes () {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
        .then(function (statuses) {
          vm.leaveRequestStatuses = statuses.filter(function (status) {
            return _.includes(allowedLeaveStatuses, status.name);
          });
        });
    }

    /**
     * Loads all absence periods and stores the current one.
     *
     * @return {Promise}
     */
    function loadCurrentAbsencePeriod () {
      return AbsencePeriod.all().then(function (periods) {
        vm.currentAbsencePeriod = _.find(periods, function (period) {
          return period.current;
        });
      });
    }
  }
});
