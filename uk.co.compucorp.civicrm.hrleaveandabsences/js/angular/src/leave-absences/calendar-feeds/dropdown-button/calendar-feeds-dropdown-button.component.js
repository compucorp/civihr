/* eslint-env amd */

(function (CRM) {
  define([
    'common/lodash'
  ], function (_) {
    CalendarFeedsDropdownButtonController.$inject = ['CalendarFeed', 'CalendarFeedsLinkModal'];

    return {
      __name: 'calendarFeedsDropdownButton',
      bindings: {
        dropdownPosition: '@'
      },
      controller: CalendarFeedsDropdownButtonController,
      controllerAs: 'dropdownButton',
      templateUrl: ['shared-settings', function (sharedSettings) {
        return sharedSettings.sourcePath + 'calendar-feeds/dropdown-button/calendar-feeds-dropdown-button.html';
      }]
    };

    function CalendarFeedsDropdownButtonController (CalendarFeed, CalendarFeedsLinkModal) {
      var vm = this;

      vm.feeds = [];
      vm.loading = {
        feeds: false
      };

      vm.openLinkModal = openLinkModal;

      (function init () {
        loadFeeds();
      }());

      /**
       * Loads Calendar Feeds
       *
       * @return {Promise}
       */
      function loadFeeds () {
        vm.loading.feeds = true;

        return CalendarFeed.all()
          .then(function (feeds) {
            vm.feeds = feeds;
          })
          .finally(function () {
            vm.loading.feeds = false;
          });
      }

      /**
       * Opens Link Modal for a particular feed
       *
       * @param {String} feedHash - the hash for the feed to open
       */
      function openLinkModal (feedHash) {
        CalendarFeedsLinkModal.open(feedHash);
      }
    }
  });
}(CRM));
