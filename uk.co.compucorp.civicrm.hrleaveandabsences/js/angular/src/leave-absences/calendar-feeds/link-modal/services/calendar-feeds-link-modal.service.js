/* eslint-env amd */

define(function () {
  CalendarFeedsLinkModal.__name = 'CalendarFeedsLinkModal';
  CalendarFeedsLinkModal.$inject = ['$uibModal', 'SITE_HOST'];

  return CalendarFeedsLinkModal;

  function CalendarFeedsLinkModal ($uibModal, SITE_HOST) {
    return {
      open: open
    };

    /**
     * Opens a modal with the calendar feed link.
     *
     * @param {String} hash - the unique feed hash.
     */
    function open (hash) {
      var url = SITE_HOST + 'civicrm/calendar-feed?hash=' + hash;

      $uibModal.open({
        controllerAs: 'modal',
        size: 'md',
        template: '<calendar-feeds-link-modal dismiss="modal.dismiss" url="modal.url"></calendar-feeds-link-modal>',
        controller: ['$uibModalInstance', 'url', function ($uibModalInstance, url) {
          this.url = url;
          this.dismiss = $uibModalInstance.dismiss;
        }],
        resolve: {
          url: function () {
            return url;
          }
        }
      });
    }
  }
});
