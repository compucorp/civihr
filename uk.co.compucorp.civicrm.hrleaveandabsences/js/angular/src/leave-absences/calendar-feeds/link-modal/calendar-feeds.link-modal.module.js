/* eslint-env amd */
/* global Drupal */

(function (Drupal) {
  define([
    'common/angular',
    'leave-absences/calendar-feeds/link-modal/calendar-feeds-link-modal.component',
    'leave-absences/calendar-feeds/link-modal/calendar-feeds-link-modal.service',
    'leave-absences/calendar-feeds/link-modal/input-with-copy-button.directive',
    'leave-absences/calendar-feeds/calendar-feeds.core',
    'leave-absences/calendar-feeds/link-modal/calendar-feeds.link-modal.core'
  ], function (angular, CalendarFeedsLinkModalComponent, CalendarFeedsLinkModalService,
    inputWithCopyButton) {
    return angular.module('calendar-feeds.link-modal', [
      'calendar-feeds.core',
      'calendar-feeds.link-modal.core'
    ])
      .constant('SITE_HOST', Drupal.absoluteUrl('/'))
      .component(CalendarFeedsLinkModalComponent.__name, CalendarFeedsLinkModalComponent)
      .directive(inputWithCopyButton.__name, inputWithCopyButton)
      .factory(CalendarFeedsLinkModalService.__name, CalendarFeedsLinkModalService);
  });
}(Drupal));
