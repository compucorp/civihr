define([
  'common/modules/controllers',
  'common/controllers/contact-actions/modal-ctrl',
  'common/services/api/contact-actions'
], function(controllers, ModalCtrl) {
  'use strict';

  function NewOrganizationModalCtrl($rootScope, $modalInstance, contactActions) {
    var vm = this;
    ModalCtrl.call(vm, $rootScope, $modalInstance);
    vm.title = 'New Organization';

    vm.init(contactActions.getFormFields.forNewOrganization);

    vm.submit = function() {
      vm.save(contactActions.save.newOrganization, 'newOrganizationCreated');
    };
  }
  NewOrganizationModalCtrl.prototype = Object.create(ModalCtrl.prototype);
  NewOrganizationModalCtrl.prototype.constructor = NewOrganizationModalCtrl;

  controllers.controller('NewOrganizationModalCtrl', ['$rootScope', '$uibModalInstance',
    'api.contactActions', NewOrganizationModalCtrl]);
});
