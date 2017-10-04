/* eslint-env amd */

define([
  'common/angular',
  'common/decorators/uib-datepicker-calendar-icon.decorator',
  'common/decorators/uib-datepicker-mobile-version.decorator',
  'common/decorators/xeditable-disable-calendar-icon.decorator',
  'common/decorators/ui-bootstrap/uib-modal.decorator',
  'common/angularBootstrap',
  'common/angularXeditable',
  'common/ui-select',
  'common/modules/templates',
  'common/modules/controllers',
  'common/modules/services',
  'common/modules/apis'
], function (angular, uibCalendarIconDecorator, uibCalendarMobileVersion, xeditableDisableCalendarIcon, uibModalDecorator) {
  'use strict';
  return angular.module('common.directives', ['common.templates', 'common.controllers',
    'common.apis', 'ui.select', 'ngSanitize', 'xeditable'])
    .config(['$provide', function ($provide) {
      $provide.decorator('uibDatepickerPopupDirective', uibCalendarMobileVersion);
      $provide.decorator('uibDatepickerPopupDirective', uibCalendarIconDecorator);
      $provide.decorator('$uibModal', uibModalDecorator);
      $provide.decorator('editableBsdateDirective', xeditableDisableCalendarIcon);
    }])
});
