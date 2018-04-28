/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  return ['$delegate', '$document', function ($delegate, $document) {
    var originalOpenFunction = $delegate.open;

    $delegate.open = open;

    /**
     * Decorates uibModal to set prevent body from scrolling while a modal is open
     * and revert to the original styles values when it is closed
     *
     * @param  {any}
     * @return {Object} Modal Instance
     */
    function open () {
      var modalInstance;
      var elements = [
        $document.find('body'),
        $document.find('html')
      ];

      elements.forEach(function (element) {
        element.addClass('chr_scroll-lock');
      });

      modalInstance = originalOpenFunction.apply(this, arguments);

      modalInstance.closed.then(function () {
        if ($document.find('.modal-dialog').length) {
          return;
        }

        elements.forEach(function (element) {
          element.removeClass('chr_scroll-lock');
        });
      });

      return modalInstance;
    }

    return $delegate;
  }];
});
