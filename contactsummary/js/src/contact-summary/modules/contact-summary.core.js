/* eslint-env amd */

define([
  'common/angular',
  'common/services/pub-sub'
], function (angular) {
  'use strict';

  angular.module('contactsummary.core', [
    'ngRoute',
    'ngResource',
    'ui.bootstrap',
    'common.services'
  ]);
});
