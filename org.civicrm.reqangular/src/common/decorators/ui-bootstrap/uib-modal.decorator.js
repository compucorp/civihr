/* eslint-env amd */

define([], function () {
  'use strict';

  return ['$delegate', '$document', function ($delegate, $document) {
    var openFunction = $delegate.open;

    $delegate.open = open;

    /**
     * Decorates uibModal to set prevent body from scrolling while a modal is open
     * and revert to the original styles values when it is closed
     *
     * @param  {any}
     * @return {Object} Modal Instance
     */
    function open () {
      var lockScrollStyling = ';overflow: hidden;height: 100%;width: 100%;';
      var modalInstance;
      var elements = [
        { node: $document[0].body },
        { node: $document[0].getElementsByTagName('html')[0] }
      ];

      elements.forEach(function (element) {
        element.style = element.node.getAttribute('style');
        element.node.setAttribute('style', element.style + lockScrollStyling);
      });

      modalInstance = openFunction.apply(this, arguments);

      modalInstance.closed.then(function () {
        elements.forEach(function (element) {
          element.node.setAttribute('style', element.style);
        });
      });

      return modalInstance;
    }

    return $delegate;
  }];
});
