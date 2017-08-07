/* eslint-env amd */

define([
  'common/angular',
  'common/decorators/q/q-sequence',
  'common/modules/apis'
], function (angular, $qSequence) {
  'use strict';

  return angular.module('common.models.instances', [
    'common.apis'
  ]).config(['$provide', function ($provide) {
    $provide.decorator('$q', $qSequence);
  }]);
});
