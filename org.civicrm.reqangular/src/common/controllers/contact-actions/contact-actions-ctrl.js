define([
  'common/modules/controllers',
  'common/controllers/contact-actions/new-individual-ctrl',
  'common/controllers/contact-actions/new-household-ctrl',
  'common/controllers/contact-actions/new-organization-ctrl',
  'common/services/api/contact-actions'
], function(controllers) {
  'use strict';
  controllers.controller('ContactActionsCtrl', ['$scope', '$rootElement', '$uibModal', 'api.contactActions',
    function($scope, $rootElement, $modal, contactActions) {
      var vm = this;

      $scope.$watch(function() {
        return vm.refineSearch.selected;
      }, function(newValue, oldValue) {
        if (newValue !== oldValue) {
          $scope.$emit('contactRefineSearch', {
            field: newValue.field ? newValue.field.label : null,
            option: newValue.option ? newValue.option.value : null
          });
        }
      }, true);

      vm.refineSearch = {
        selected: {
          field: null,
          option: null
        },
        availableFields: [{
          label: 'Contact Type',
          getOptions: contactActions.getContactTypeOptions.bind(contactActions)
        }, {
          label: 'Group',
          getOptions: contactActions.getGroupOptions.bind(contactActions)
        }, {
          label: 'Tag',
          getOptions: contactActions.getTagOptions.bind(contactActions)
        }, {
          label: 'State / Province',
          getOptions: contactActions.getStateProvinceOptions.bind(contactActions)
        }, {
          label: 'Country',
          getOptions: contactActions.getCountryOptions.bind(contactActions)
        }, {
          label: 'Gender',
          getOptions: contactActions.getGenderOptions.bind(contactActions)
        }, {
          label: 'Deceased',
          getOptions: contactActions.getDeceasedOptions.bind(contactActions)
        }],
        availableOptions: {
          refresh: function() {
            if(!vm.refineSearch.selected.field) {
              this.options = [];
            } else {
              this.options = null;
              vm.refineSearch.selected.field.getOptions().then(function(options) {
                this.options = options;
              }.bind(this));
            }
          },
          options: []
        }
      };

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
