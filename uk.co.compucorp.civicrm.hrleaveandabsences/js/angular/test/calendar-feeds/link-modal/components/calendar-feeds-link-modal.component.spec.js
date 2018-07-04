/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'leave-absences/calendar-feeds/link-modal/link-modal.module'
], function (angular) {
  'use strict';

  describe('CalendarFeedsLinkModalService', function () {
    var CalendarFeedsLinkModal;

    beforeEach(angular.mock.module('calendar-feeds.link-modal'));

    beforeEach(inject(function ($componentController) {
      CalendarFeedsLinkModal = $componentController('calendarFeedsLinkModal');
    }));

    it('is defined', function () {
      expect(CalendarFeedsLinkModal).toBeDefined();
    });
  });
});
