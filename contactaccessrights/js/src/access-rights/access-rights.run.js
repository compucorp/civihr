/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  angular.module('access-rights.run', []).run(run);

  run.$inject = ['$log', 'editableOptions', 'editableThemes'];

  function run ($log, editableOptions, editableThemes) {
    $log.debug('app.run');
    editableOptions.theme = 'bs3';
  }
});
