define([
  'common/angular',
  'common/services/pub-sub',
  'common/angularUiRouter'
], function (angular) {
  'use strict';

  angular.module('contactsummary.core', [
    'ngRoute',
    'ngResource',
    'ui.bootstrap',
    'ui.router',
    'common.services'
  ]);
});
