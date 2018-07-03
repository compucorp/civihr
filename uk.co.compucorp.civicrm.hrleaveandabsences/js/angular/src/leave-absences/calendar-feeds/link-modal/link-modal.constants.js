/* eslint-env amd */
/* global Drupal */

(function (Drupal) {
  define([
    'common/angular'
  ], function (angular) {
    'use strict';

    return angular.module('calendar-feeds.link-modal.constants', [])
      .constant('SITE_HOST', Drupal.absoluteUrl('/'));
  });
}(Drupal));
