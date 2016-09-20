define([
  'common/modules/controllers',
  'common/services/api/contact-actions',
  'common/controllers/contact-actions/new-individual-ctrl',
  'common/controllers/contact-actions/new-household-ctrl',
  'common/controllers/contact-actions/new-organization-ctrl'
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
          getOptions: contactActions.getOptions.forContactType
        }, {
          label: 'Group',
          getOptions: contactActions.getOptions.forGroup
        }, {
          label: 'Tag',
          getOptions: contactActions.getOptions.forTag
        }, {
          label: 'State / Province',
          getOptions: contactActions.getOptions.forStateProvince
        }, {
          label: 'Country',
          getOptions: contactActions.getOptions.forCountry
        }, {
          label: 'Gender',
          getOptions: contactActions.getOptions.forGender
        }, {
          label: 'Deceased',
          getOptions: contactActions.getOptions.forDeceased
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
          templateUrl: 'contact-actions/modals/form.html'
        });
      };

      vm.showNewHouseholdModal = function() {
        $modal.open({
          appendTo: $rootElement.children().eq(0),
          controller: 'NewHouseholdModalCtrl',
          controllerAs: '$ctrl',
          bindToController: true,
          templateUrl: 'contact-actions/modals/form.html'
        });
      };

      vm.showNewOrganizationModal = function() {
        $modal.open({
          appendTo: $rootElement.children().eq(0),
          controller: 'NewOrganizationModalCtrl',
          controllerAs: '$ctrl',
          bindToController: true,
          templateUrl: 'contact-actions/modals/form.html'
        });
      };
    }
  ]);
});
