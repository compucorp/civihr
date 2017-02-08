define([
  'common/angular',
  'common/modules/apis',
  'leave-absences/shared/modules/shared-settings',
], function (angular) {
  'use strict';

  return angular.module('leave-absences.apis', [
    'common.apis',
    'leave-absences.settings'
  ]);
});
