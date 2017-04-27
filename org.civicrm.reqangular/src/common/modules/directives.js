define([
  'common/angular',
  'common/decorators/angular-date/datepicker-addon',
  'common/decorators/ui-bootstrap/uib-tabset',
  'common/angularBootstrap',
  'common/ui-select',
  'common/modules/templates',
  'common/modules/controllers',
  'common/modules/services',
  'common/modules/apis'
], function (angular, datepickerAddon, uibTabset) {
  'use strict';

  return angular.module('common.directives', ['common.templates', 'common.controllers',
    'ui.select', 'ui.bootstrap'])
    .config(['$provide', function ($provide) {
      $provide.decorator('uibDatepickerPopupDirective', datepickerAddon);
      $provide.decorator('uibTabsetDirective', uibTabset);
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
