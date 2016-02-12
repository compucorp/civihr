define([
    'common/modules/dialog',
    'common/controllers/dialog-ctrl'
], function (dialog) {
    'use strict';

    dialog.factory('dialog', ['$modal', '$rootElement', '$templateCache',
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

                    if (options && typeof options !== 'object') {
                        return;
                    }

                    return $modal.open({
                        targetDomEl: $rootElement.children().eq(0),
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
                            }
                        }
                    }).result;
                }
            };
        }
    ]);
});
