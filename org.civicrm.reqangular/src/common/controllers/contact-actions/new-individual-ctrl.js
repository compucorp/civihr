define([
  'common/modules/controllers',
  'common/services/api/contact-actions'
], function(controllers) {
  'use strict';

  controllers.controller('NewIndividualModalCtrl', ['$rootScope', '$uibModalInstance',
    'api.contactActions', function($rootScope, $modalInstance, contactActions) {
      var vm = this;

      vm.errorMsg = '';
      vm.firstName = '';
      vm.lastName = '';
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
        contactActions.saveNewIndividual(vm.firstName, vm.lastName, vm.email)
          .then(function(data) {
            $rootScope.$broadcast('newIndividualCreated', data);
            $modalInstance.dismiss('cancel');
          })
          .catch(function() {
            vm.errorMsg = 'Error while saving data';
          });
      };
    }
  ]);
});
