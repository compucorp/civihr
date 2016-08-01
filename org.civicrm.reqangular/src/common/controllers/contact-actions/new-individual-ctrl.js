define([
  'common/modules/controllers',
  'common/services/api/contact'
], function(controllers) {
  'use strict';

  controllers.controller('NewIndividualModalCtrl', ['$rootScope', '$uibModalInstance',
    'api.contact', function($rootScope, $modalInstance, contact) {
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
        contact.saveNewIndividual(vm.firstName, vm.lastName, vm.email)
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
