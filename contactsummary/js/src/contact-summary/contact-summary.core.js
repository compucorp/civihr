/* eslint-env amd */

define([
  'common/angular',
  'common/services/pub-sub',
  'leave-absences/shared/components/leave-widget/leave-widget.component'
], function (angular) {
  'use strict';

  angular.module('contactsummary.core', [
    'ngRoute',
    'ngResource',
    'ui.bootstrap',
    'common.services',
    'leave-absences.components.leave-widget'
  ]);
});
