/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  AccessRightsController.$inject = ['$rootElement', '$uibModal'];

  function AccessRightsController ($rootElement, $modal) {
    var vm = this;

    vm.openModal = openModal;

    /**
     * Opens the permissions modal
     */
    function openModal () {
      $modal.open({
        appendTo: angular.element('[data-contact-actions-modals-container]'),
        controller: 'AccessRightsModalController',
        controllerAs: 'modalCtrl',
        bindToController: true,
        templateUrl: CRM.vars.contactAccessRights.baseURL + '/js/src/access-rights/controllers/access-rights-modal.html'
      });
    }
  }

  return { AccessRightsController: AccessRightsController };
});
