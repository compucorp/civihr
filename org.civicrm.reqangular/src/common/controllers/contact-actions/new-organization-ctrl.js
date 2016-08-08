define([
  'common/modules/controllers',
  'common/services/api/contact-actions'
], function(controllers) {
  'use strict';

  controllers.controller('NewOrganizationModalCtrl', ['$rootScope', '$uibModalInstance',
    'api.contactActions', function($rootScope, $modalInstance, contactActions) {
      var vm = this;

      vm.errorMsg = '';
      vm.loading = false;
      vm.organizationName = '';
      vm.email = '';

      /**
       * Closes the modal
       */
      vm.cancel = function() {
        $modalInstance.dismiss('cancel');
      };

      /**
       * Saves data and closes the modal
       */
      vm.submit = function() {
        vm.loading = true;
        contactActions.saveNewOrganization(vm.organizationName, vm.email)
          .then(function(data) {
            vm.loading = false;
            $rootScope.$broadcast('newOrganizationCreated', data);
            $modalInstance.dismiss('cancel');
          })
          .catch(function() {
            vm.loading = false;
            vm.errorMsg = 'Error while saving data';
          });
      };
    }
  ]);
});
