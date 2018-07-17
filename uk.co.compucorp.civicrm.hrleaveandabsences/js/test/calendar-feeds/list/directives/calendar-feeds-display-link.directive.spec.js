/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/angularMocks',
  'leave-absences/calendar-feeds/list/list.module'
], function (angular, _) {
  'use strict';

  describe('displayLink', function () {
    var $rootScope, displayLink, linkModal, scope;
    var titleValue = 'Feed Title';
    var hashValue = _.uniqueId();

    beforeEach(angular.mock.module('calendar-feeds.list', 'leave-absences.templates'));

    beforeEach(inject(function ($compile, _$rootScope_, CalendarFeedsLinkModal) {
      var template = '<div data-title="' + titleValue + '" data-hash="' + hashValue + '">' +
        '  <a class="calendar-feeds-display-link">View Link</a>' +
        '</div>';
      $rootScope = _$rootScope_;
      scope = $rootScope.$new();
      linkModal = CalendarFeedsLinkModal;
      displayLink = $compile(template)(scope);

      $rootScope.$digest();
    }));

    describe('opening the link modal', function () {
      beforeEach(function () {
        spyOn(linkModal, 'open');
        displayLink.find('a').click();
        $rootScope.$digest();
      });

      it('opens the link modal and passes the title and hash values stored in the closest parent element', function () {
        expect(linkModal.open).toHaveBeenCalledWith(titleValue, hashValue);
      });
    });
  });
});
