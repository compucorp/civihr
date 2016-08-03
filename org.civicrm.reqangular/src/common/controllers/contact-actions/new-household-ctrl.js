define([
  'common/modules/controllers',
  'common/services/api/contact-actions'
], function(controllers) {
  'use strict';

  controllers.controller('NewHouseholdModalCtrl', ['$rootScope', '$uibModalInstance',
    'api.contactActions', function($rootScope, $modalInstance, contactActions) {
      var vm = this;

      vm.errorMsg = '';
      vm.householdName = '';
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
        contactActions.saveNewHousehold(vm.householdName, vm.email)
          .then(function(data) {
            $rootScope.$broadcast('newHouseholdCreated', data);
            $modalInstance.dismiss('cancel');
          })
          .catch(function() {
            vm.errorMsg = 'Error while saving data';
          });
      };
    }
  ]);
});
