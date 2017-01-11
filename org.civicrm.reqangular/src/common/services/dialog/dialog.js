define([
  'common/lodash'
], function (_) {
  'use strict';

  return ['$uibModal', '$rootElement', '$templateCache',
    function ($modal, $rootElement, $templateCache) {

      return {

        /**
         * Opens the small dialog modal
         *
         * @param {object} options
         *   Contains the labels for OK and Cancel button, the body
         *   of the modal, and the title of the modal
         * @return {Promise}
         */
        open: function (options) {
          var $children;

          if (options && typeof options !== 'object') {
            return;
          }

          $children = $rootElement.children();

          return $modal.open({
            appendTo: $children.length ? $children.eq(0) : $rootElement,
            size: 'sm',
            controller: 'DialogCtrl',
            template: $templateCache.get('dialog.html'),
            resolve: {
              content: function (){
                return {
                  copyCancel: options.copyCancel || '',
                  copyConfirm: options.copyConfirm || '',
                  classConfirm: options.classConfirm || '',
                  title: options.title || '',
                  msg: options.msg || ''
                };
              },
              onConfirm: function () {
                return _.isFunction(options.onConfirm) ? options.onConfirm : null;
              }
            }
          }).result;
        }
      };
    }
  ];
});
