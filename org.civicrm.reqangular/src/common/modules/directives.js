/* eslint-env amd */
define([
  'common/angular',
  'common/decorators/ui-select-focus.decorator',
  'common/decorators/uib-datepicker-calendar-icon.decorator',
  'common/decorators/uib-datepicker-mobile-version.decorator',
  'common/decorators/xeditable-disable-calendar-icon.decorator',
  'common/decorators/ui-bootstrap/uib-tabset',
  'common/decorators/ui-bootstrap/uib-modal.decorator',
  'common/angularBootstrap',
  'common/angularXeditable',
  'common/ui-select',
  'common/modules/templates',
  'common/modules/controllers',
  'common/modules/services',
  'common/modules/apis'
], function (angular, uiSelectFocusDecorator, uibCalendarIconDecorator, uibCalendarMobileVersion, xeditableDisableCalendarIcon, uibTabset, uibModalDecorator) {
  'use strict';
  return angular.module('common.directives', ['common.templates', 'common.controllers',
    'ui.select', 'ui.bootstrap', 'xeditable'])
    .config(['$provide', function ($provide) {
      $provide.decorator('uibDatepickerPopupDirective', uibCalendarMobileVersion);
      $provide.decorator('uibDatepickerPopupDirective', uibCalendarIconDecorator);
      $provide.decorator('uibTabsetDirective', uibTabset);
      $provide.decorator('$uibModal', uibModalDecorator);
      $provide.decorator('editableBsdateDirective', xeditableDisableCalendarIcon);
      $provide.decorator('uiSelectDirective', uiSelectFocusDecorator);
    }])
    .run(['$templateCache', function ($templateCache) {
      // Update uib-tabset HTML with header class
      var tplPath = 'uib/template/tabs/tabset.html';
      var tpl = jQuery($templateCache.get(tplPath));
      tpl.find('ul').addClass('{{tabset.customHeaderClass}}');
      $templateCache.put(tplPath, tpl.wrap('<div/>').parent().html());
      //end of uib-tabset override
    }]);
});
