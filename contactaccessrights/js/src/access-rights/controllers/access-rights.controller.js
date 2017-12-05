/* eslint-env amd */

define(function () {
  'use strict';

  AccessRightsController.__name = 'AccessRightsController';
  AccessRightsController.$inject = ['$rootElement', '$uibModal'];

  function AccessRightsController ($rootElement, $modal) {
    var vm = this;

    vm.openModal = openModal;

    /**
     * Opens the permissions modal
     */
    function openModal () {
      $modal.open({
        appendTo: $rootElement.children().eq(0),
        controller: 'AccessRightsModalController',
        controllerAs: 'modalCtrl',
        bindToController: true,
        templateUrl: CRM.vars.contactAccessRights.baseURL + '/views/access-rights-modal.html'
      });
    }
  }

  return AccessRightsController;
});
