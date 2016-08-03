define([
  'common/modules/controllers',
  'common/controllers/contact-actions/new-individual-ctrl',
  'common/controllers/contact-actions/new-household-ctrl',
  'common/controllers/contact-actions/new-organization-ctrl'
], function(controllers) {
  'use strict';
  controllers.controller('ContactActionsCtrl', ['$scope', '$rootElement', '$uibModal',
    function($scope, $rootElement, $modal) {
      var vm = this;

      vm.showNewIndividualModal = function() {
        $modal.open({
          appendTo: $rootElement.children().eq(0),
          controller: 'NewIndividualModalCtrl',
          controllerAs: '$ctrl',
          bindToController: true,
          templateUrl: 'contact-actions/modals/new-individual.html'
        });
      };

      vm.showNewHouseholdModal = function() {
        $modal.open({
          appendTo: $rootElement.children().eq(0),
          controller: 'NewHouseholdModalCtrl',
          controllerAs: '$ctrl',
          bindToController: true,
          templateUrl: 'contact-actions/modals/new-household.html'
        });
      };

      vm.showNewOrganizationModal = function() {
        $modal.open({
          appendTo: $rootElement.children().eq(0),
          controller: 'NewOrganizationModalCtrl',
          controllerAs: '$ctrl',
          bindToController: true,
          templateUrl: 'contact-actions/modals/new-organization.html'
        });
      };
    }
  ]);
});
