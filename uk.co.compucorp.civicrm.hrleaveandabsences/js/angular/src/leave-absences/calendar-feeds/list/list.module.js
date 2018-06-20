/* eslint-env amd */

define([
  'common/angular',
  './list.component',
  '../calendar-feeds.module'
], function (angular, listComponent) {
  return angular.module('calendar-feeds.list', [
    'calendar-feeds'
  ])
    .component(listComponent.__name, listComponent);
});
