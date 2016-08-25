define([
  'common/modules/controllers',
  'common/controllers/contact-actions/modal-ctrl',
  'common/services/api/contact-actions'
], function(controllers, ModalCtrl) {
  'use strict';

  function NewHouseholdModalCtrl($rootScope, $modalInstance, contactActions) {
    var vm = this;
    ModalCtrl.call(vm, $rootScope, $modalInstance);
    vm.title = 'New Household';

    vm.init(contactActions.getFormFields.forNewHousehold);

    vm.submit = function() {
      vm.save(contactActions.save.newHousehold, 'newHouseholdCreated');
    };
  }
  NewHouseholdModalCtrl.prototype = Object.create(ModalCtrl.prototype);
  NewHouseholdModalCtrl.prototype.constructor = NewHouseholdModalCtrl;

  controllers.controller('NewHouseholdModalCtrl', ['$rootScope', '$uibModalInstance',
    'api.contactActions', NewHouseholdModalCtrl]);
});
