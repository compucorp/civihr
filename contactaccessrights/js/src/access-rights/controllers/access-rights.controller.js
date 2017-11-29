/* eslint-env amd */

define(function () {
  'use strict';

  AccessRightsCtrl.__name = 'AccessRightsCtrl';
  AccessRightsCtrl.$inject = ['$rootElement', '$uibModal'];

  function AccessRightsCtrl ($rootElement, $modal) {
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

  return AccessRightsCtrl;
});
