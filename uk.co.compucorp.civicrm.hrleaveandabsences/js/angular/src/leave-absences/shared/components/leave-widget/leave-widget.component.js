/* eslint-env amd */

define([
  'leave-absences/shared/modules/components',
  'common/models/session.model',
  'leave-absences/shared/models/absence-period.model',
  'leave-absences/shared/models/absence-type.model',
  './leave-widget-balance.component'
], function (components) {
  components.component('leaveWidget', {
    controller: leaveWidgetController,
    controllerAs: 'leaveWidget',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-widget/leave-widget.html';
    }]
  });

  leaveWidgetController.$inject = ['$log', '$scope', 'AbsencePeriod',
    'AbsenceType', 'Session'];

  function leaveWidgetController ($log, $scope, AbsencePeriod, AbsenceType,
  Session) {
    var childComponents = 0;
    var vm = this;

    vm.absenceTypes = [];
    vm.currentAbsencePeriod = null;
    vm.loading = { childComponents: false, component: true };
    vm.loggedInContactId = null;

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
     * Watches for child components loading and ready events.
     */
    function initWatchers () {
      $scope.$on('LeaveWidget::childIsLoading', childComponentIsLoading);
      $scope.$on('LeaveWidget::childIsReady', childComponentIsReady);
    }

    /**
     * Loads the session, absence types, and the current absence period. When
     * all dependencies are ready it sets loading component to false.
     */
    function loadDependencies () {
      loadSession()
        .then(loadAbsenceTypes)
        .then(loadCurrentAbsencePeriod)
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
      return AbsenceType.all().then(function (types) {
        vm.absenceTypes = types;
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

    /**
     * Loads the session and stores the current user id.
     */
    function loadSession () {
      return Session.get().then(function (value) {
        vm.loggedInContactId = value.contactId;
      });
    }
  }
});
