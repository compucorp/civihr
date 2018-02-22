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
     * @param  {Object}   options
     * @param  {String}   options.title custom text used in the title
     * @param  {String}   options.msg custom text used in the body message
     * @param  {String}   options.copyConfirm custom text used in the confirmation button
     * @param  {String}   options.copyCancel custom text used in the cancel button
     * @param  {String}   options.classConfirm class name for the confirmation button, eg. "btn-success"
     * @param  {Boolean}  options.loading toggles a loading icon and hides action buttons and body message
     * @param  {Function} options.onConfirm is executed on the confirmation button click
     * @param  {Promise}  options.optionsPromise once resolves, sets resolved options
     * @return {Promise}
     */
    function open (options) {
      var $children;

      if (!_.isObject(options)) {
        throw (new Error('Dialog Service: Options passed should be an object'));
      }

      $children = $rootElement.children();

      return $modal.open({
        appendTo: $children.length ? $children.eq(0) : $rootElement,
        size: 'sm',
        controller: 'DialogController',
        template: $templateCache.get('dialog.html'),
        resolve: {
          options: function () {
            return options;
          }
        }
      }).result;
    }
  }
});
