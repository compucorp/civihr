/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/absence-tab/modules/components',
  'common/models/contract'
], function (_, moment, components) {
  components.component('annualEntitlementChangeLog', {
    bindings: {
      periodId: '<',
      contactId: '<',
      dismissModal: '&'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/annual-entitlement-change-log.html';
    }],
    controllerAs: 'changeLog',
    controller: AnualEntitlementChangeLog
  });

  AnualEntitlementChangeLog.$inject = ['AbsencePeriod', 'shared-settings'];

  function AnualEntitlementChangeLog (AbsencePeriod, sharedSettings) {
    var vm = this;

    vm.absencePeriod = null;
    vm.loading = { component: true };

    (function init () {
      loadAbsencePeriod()
      .finally(function () {
        vm.loading.component = false;
      });
    })();

    /**
     * Loads the absence period that corresponds to the period id given to
     * the component.
     *
     * @return {Promise}
     */
    function loadAbsencePeriod () {
      return AbsencePeriod.all({
        id: vm.periodId
      })
      .then(function (absencePeriods) {
        vm.absencePeriod = absencePeriods[0];
      });
    }
  }
});
