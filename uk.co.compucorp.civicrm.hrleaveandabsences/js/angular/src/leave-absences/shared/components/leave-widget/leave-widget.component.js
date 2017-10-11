/* eslint-env amd */

define([
  'leave-absences/shared/modules/components',
  'leave-absences/shared/modules/shared-settings'
], function (components) {
  components.component('leaveWidget', {
    controller: leaveWidgetController,
    controllerAs: 'leaveWidget',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-widget/leave-widget.html';
    }]
  });

  leaveWidgetController.$inject = ['$log', 'AbsencePeriod', 'AbsenceType',
    'Session'];

  function leaveWidgetController ($log, AbsencePeriod, AbsenceType, Session) {
    var vm = this;

    vm.absenceTypes = [];
    vm.currentAbsencePeriod = null;
    vm.loading = { component: true };
    vm.loggedInContactId = null;

    (function init () {
      $log.debug('Controller: leaveWidgetController');
      loadSession()
      .then(loadAbsenceTypes)
      .then(loadCurrentAbsencePeriod)
      .finally(function () {
        vm.loading.component = false;
      });
    })();

    function loadAbsenceTypes () {
      return AbsenceType.all().then(function (types) {
        vm.absenceTypes = types;
      });
    }

    function loadCurrentAbsencePeriod () {
      return AbsencePeriod.all().then(function (periods) {
        vm.currentAbsencePeriod = _.find(periods, function (period) {
          return period.current;
        });
      });
    }

    function loadSession () {
      return Session.get().then(function (value) {
        vm.loggedInContactId = value.contactId;
      });
    }
  }
});
