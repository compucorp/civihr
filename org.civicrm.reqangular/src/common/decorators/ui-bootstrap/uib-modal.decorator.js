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
      var lockScrollStyle = 'overflow: hidden; height: 100%; width: 100%;';
      var elements = [
        {
          node: $document[0].body,
          originalStyle: $document[0].body.getAttribute('style')
        },
        {
          node: $document[0].getElementsByTagName('html')[0],
          originalStyle: $document[0].getElementsByTagName('html')[0].getAttribute('style')
        }
      ];

      elements.forEach(function (element) {
        element.style = element.node.getAttribute('style');
        element.node.setAttribute('style', element.style + ';' + lockScrollStyle);
      });

      modalInstance = originalOpenFunction.apply(this, arguments);

      modalInstance.closed.then(function () {
        elements.forEach(function (element) {
          element.node.setAttribute('style', element.originalStyle);
        });
      });

      return modalInstance;
    }

    return $delegate;
  }];
});
