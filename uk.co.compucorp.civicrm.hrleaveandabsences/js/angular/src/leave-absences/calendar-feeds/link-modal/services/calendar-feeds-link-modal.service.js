/* eslint-env amd */

define(function () {
  CalendarFeedsLinkModal.__name = 'CalendarFeedsLinkModal';
  CalendarFeedsLinkModal.$inject = ['$document', '$uibModal', 'HOST_URL'];

  return CalendarFeedsLinkModal;

  function CalendarFeedsLinkModal ($document, $uibModal, HOST_URL) {
    return {
      open: open
    };

    /**
     * Opens a modal with the calendar feed link.
     *
     * @param {String} hash - the unique feed hash.
     */
    function open (hash) {
      var appendToElement = $document.find('#bootstrap-theme');
      var url = HOST_URL + 'civicrm/calendar-feed?hash=' + hash;

      if (appendToElement.length === 0) {
        appendToElement = $document.find('body');
      }

      $uibModal.open({
        controllerAs: 'modal',
        size: 'md',
        template: '<calendar-feeds-link-modal dismiss="modal.dismiss" url="modal.url"></calendar-feeds-link-modal>',
        controller: ['$uibModalInstance', 'url', function ($uibModalInstance, url) {
          this.url = url;
          this.dismiss = $uibModalInstance.dismiss;
        }],
        appendTo: appendToElement.eq(0),
        resolve: {
          url: function () {
            return url;
          }
        }
      });
    }
  }
});
