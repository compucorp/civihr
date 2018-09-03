/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  AccessRightsController.$inject = ['$rootElement', '$uibModal', 'settings'];

  function AccessRightsController ($rootElement, $modal, settings) {
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
        templateUrl: settings.baseUrl + 'controllers/access-rights-modal.html'
      });
    }
  }

  return { AccessRightsController: AccessRightsController };
});
