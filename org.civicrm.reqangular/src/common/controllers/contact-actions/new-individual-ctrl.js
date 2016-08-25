define([
  'common/modules/controllers',
  'common/controllers/contact-actions/modal-ctrl',
  'common/services/api/contact-actions'
], function(controllers, ModalCtrl) {
  'use strict';

  function NewIndividualModalCtrl($rootScope, $modalInstance, contactActions) {
    var vm = this;
    ModalCtrl.call(vm, $rootScope, $modalInstance);
    vm.title = 'New Individual';

    vm.init(contactActions.getFormFields.forNewIndividual);

    vm.submit = function() {
      vm.save(contactActions.save.newIndividual, 'newIndividualCreated');
    };
  }
  NewIndividualModalCtrl.prototype = Object.create(ModalCtrl.prototype);
  NewIndividualModalCtrl.prototype.constructor = NewIndividualModalCtrl;

  controllers.controller('NewIndividualModalCtrl', ['$rootScope', '$uibModalInstance',
    'api.contactActions', NewIndividualModalCtrl
  ]);
});
