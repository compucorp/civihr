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
        {
          item: angular.element($document[0].body),
          styles: [
            { name: 'overflow', old: null, new: 'hidden' },
            { name: 'height', old: null, new: '100%' },
            { name: 'width', old: null, new: '100%' }
          ]
        },
        {
          item: angular.element($document[0].getElementsByTagName('html')[0]),
          styles: [
            { name: 'overflow', old: null, new: 'hidden' },
            { name: 'height', old: null, new: '100%' },
            { name: 'width', old: null, new: '100%' }
          ]
        }
      ];

      elements.forEach(function (element) {
        element.styles.forEach(function (style) {
          style.old = element.item.css(style.name);
          element.item.css(style.name, style.new);
        });
      });

      modalInstance = originalOpenFunction.apply(this, arguments);

      modalInstance.closed.then(function () {
        elements.forEach(function (element) {
          element.styles.forEach(function (style) {
            element.item.css(style.name, style.old);
          });
        });
      });

      return modalInstance;
    }

    return $delegate;
  }];
});
