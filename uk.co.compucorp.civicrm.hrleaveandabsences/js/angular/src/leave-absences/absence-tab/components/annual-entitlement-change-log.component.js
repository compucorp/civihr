/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/absence-tab/modules/components',
  'common/models/contract'
], function (_, moment, components) {
  components.component('annualEntitlementChangeLog', {
    bindings: {
      contactId: '<',
      dismissModal: '&'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/anual-entitlement-change-log.html';
    }],
    controllerAs: 'changeLog',
    controller: AnualEntitlementChangeLog
  });

  function AnualEntitlementChangeLog () {}
});
