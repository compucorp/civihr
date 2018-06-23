/* eslint-env amd */

define(function () {
  CalendarFeedsLinkModal.__name = 'CalendarFeedsLinkModal';
  CalendarFeedsLinkModal.$inject = ['$uibModal'];

  return CalendarFeedsLinkModal;

  function CalendarFeedsLinkModal ($uibModal) {
    return {
      open: open
    };

    /**
     * Opens a modal with the calendar feed link.
     *
     * @param {String} url - the url to display on the modal.
     */
    function open (url) {
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
