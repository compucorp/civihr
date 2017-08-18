/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  angular.module('hrjobroles.run', ['hrjobroles.constants']).run(hrJobRolesRun);

  hrJobRolesRun.$inject = ['$q', '$rootScope', 'editableOptions', 'settings'];

  function hrJobRolesRun ($q, $rootScope, editableOptions, settings) {
    // Set bootstrap 3 as default theme
    editableOptions.theme = 'bs3';

    // Pass the values from our settings
    $rootScope.contactId = settings.contactId;
    $rootScope.pathBaseUrl = settings.pathBaseUrl;
    $rootScope.pathTpl = settings.pathTpl;
    $rootScope.pathIncludeTpl = settings.pathIncludeTpl;
    $rootScope.prefix = settings.classNamePrefix;
  }
});
