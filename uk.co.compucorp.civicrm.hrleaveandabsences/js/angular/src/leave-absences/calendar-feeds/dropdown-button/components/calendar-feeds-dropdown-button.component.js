/* eslint-env amd */

define([], function () {
  CalendarFeedsDropdownButtonController.$inject = [
    'CalendarFeed', 'CalendarFeedsLinkModal', 'checkPermissions', 'shared-settings'];

  return {
    __name: 'calendarFeedsDropdownButton',
    bindings: {
      dropdownPosition: '@'
    },
    controller: CalendarFeedsDropdownButtonController,
    controllerAs: 'dropdownButton',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sourcePath + 'calendar-feeds/dropdown-button/components/calendar-feeds-dropdown-button.html';
    }]
  };

  function CalendarFeedsDropdownButtonController (
    CalendarFeed, CalendarFeedsLinkModal, checkPermissions, sharedSettings) {
    var vm = this;

    vm.canCreateNewFeed = false;
    vm.feeds = [];
    vm.loading = {
      feeds: false
    };

    vm.openLinkModal = openLinkModal;

    (function init () {
      loadFeeds();
      defineIfCanCreateFeeds();
    }());

    /**
     * Defines if user can create feeds based on permissions
     *
     * @return {Promise}
     */
    function defineIfCanCreateFeeds () {
      return checkPermissions('can administer calendar feeds')
        .then(function (canAdministerCalendarFeeds) {
          vm.canCreateNewFeed = !!canAdministerCalendarFeeds;
        });
    }

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
