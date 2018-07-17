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
     * @param {String} title - the title of the feed.
     * @param {String} hash - the unique feed hash.
     */
    function open (title, hash) {
      var modalContainerElement = getModalContainerElement();
      var url = HOST_URL + 'civicrm/calendar-feed?hash=' + hash;

      $uibModal.open({
        controllerAs: 'modal',
        size: 'md',
        template: '<calendar-feeds-link-modal dismiss="modal.dismiss" url="modal.url" title="modal.title"></calendar-feeds-link-modal>',
        controller: ['$uibModalInstance', 'title', 'url', function ($uibModalInstance, title, url) {
          this.dismiss = $uibModalInstance.dismiss;
          this.title = title;
          this.url = url;
        }],
        appendTo: modalContainerElement,
        resolve: {
          title: function () {
            return title;
          },
          url: function () {
            return url;
          }
        }
      });
    }

    /**
     * Returns the element that the modal should be appended to, which is either
     * #bootstrap-theme or the body if the former is not available. This is
     * done to correctly display the modal where Bootstrap CSS rules
     * are accessible.
     *
     * @return {Object} an HTML Element reference.
     */
    function getModalContainerElement () {
      var modalContainerElement = $document.find('#bootstrap-theme');

      if (modalContainerElement.length === 0) {
        modalContainerElement = $document.find('body');
      }

      return modalContainerElement.eq(0);
    }
  }
});
