define([
  'common/angular',
  'common/angularBootstrap',
  'common/modules/templates'
], function (angular) {
  'use strict';

  return angular.module("common.uibTabset", [])
    .run(['$templateCache', function ($templateCache) {
      // Update tabset HTML with header class
      var tplPath = 'uib/template/tabs/tabset.html';
      var tpl = jQuery($templateCache.get(tplPath));

      tpl.find('ul').addClass('{{tabset.customHeaderClass}}');

      $templateCache.put(tplPath, tpl.wrap('<div/>').parent().html());
    }]);
});
