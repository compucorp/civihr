/* eslint-env amd */

define([
  'common/lodash',
  'common/modules/services'
], function (_, services) {
  'use strict';

  services.factory('dialog', DialogService);

  DialogService.$inject = ['$uibModal', '$rootElement', '$templateCache'];

  function DialogService ($modal, $rootElement, $templateCache) {
    return {
      open: open
    };

    /**
     * Opens a small dialog modal
     *
     * @param {object} options
     *   Contains labels for OK and Cancel button, a body
     *   of the modal, and a title of the modal
     * @return {Promise}
     */
    function open (options) {
      var $children;

      if (options && typeof options !== 'object') {
        return;
      }

      $children = $rootElement.children();

      return $modal.open({
        appendTo: $children.length ? $children.eq(0) : $rootElement,
        size: 'sm',
        controller: 'DialogController',
        template: $templateCache.get('dialog.html'),
        resolve: {
          props: function () {
            return options;
          }
        }
      }).result;
    }
  }
});
