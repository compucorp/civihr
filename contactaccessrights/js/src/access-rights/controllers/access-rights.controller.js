/* eslint-env amd */

define([
  'access-rights/modules/access-rights.controllers'
], function (controllers) {
  'use strict';

  controllers.controller('AccessRightsCtrl', ['$rootElement', '$uibModal',
    function ($rootElement, $modal) {
      return {

        /**
         * Opens the permissions modal
         */
        openModal: function () {
          $modal.open({
            appendTo: $rootElement.children().eq(0),
            controller: 'AccessRightsModalCtrl',
            controllerAs: 'modalCtrl',
            bindToController: true,
            templateUrl: CRM.vars.contactAccessRights.baseURL + '/views/access-rights-modal.html'
          });
        }
      };
    }
  ]);
});
