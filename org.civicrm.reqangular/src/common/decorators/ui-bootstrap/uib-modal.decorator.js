/* eslint-env amd */

define(function () {
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
      var lockScrollStyle = ';overflow: hidden; height: 100%; width: 100%';
      var elements = [
        $document[0].body,
        $document[0].getElementsByTagName('html')[0]
      ];

      elements.forEach(function (element) {
        element.setAttribute('style',
          (element.getAttribute('style') || '') + lockScrollStyle);
      });

      modalInstance = originalOpenFunction.apply(this, arguments);

      modalInstance.closed.then(function () {
        elements.forEach(function (element) {
          element.setAttribute('style',
            (element.getAttribute('style') || '').replace(lockScrollStyle, ''));
        });
      });

      return modalInstance;
    }

    return $delegate;
  }];
});
