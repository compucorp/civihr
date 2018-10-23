/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/leave-type-wizard/components/leave-type-wizard.component',
  'common/directives/help-text.directive',
  'common/directives/loading',
  'common/models/contact',
  'common/services/hr-settings',
  'common/services/notification.service',
  'common/angular-spectrum-colorpicker',
  'leave-absences/shared/models/absence-type.model',
  'leave-absences/leave-type-wizard/leave-type-wizard.core'
], function (angular, LeaveTypeWizardComponent) {
  angular.module('leave-type-wizard', [
    'common.directives',
    /*
     * @TODO Because the app requires Contact, which requires Group,
     * which requires api.group.mock and api.group-contact.mock,
     * we need to include 'common.mocks' in the production app.
     * This needs to be refactored.
     */
    'common.mocks',
    'common.models',
    'common.services',
    'leave-absences.models',
    'leave-type-wizard.core',
    'angularSpectrumColorpicker'
  ])
    .component(LeaveTypeWizardComponent)
    .config(['$httpProvider', function ($httpProvider) {
      $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    }]);
});
