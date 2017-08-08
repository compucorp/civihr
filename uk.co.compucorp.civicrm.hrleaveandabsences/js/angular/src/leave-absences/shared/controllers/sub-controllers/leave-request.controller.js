/* eslint-env amd */

define([
  'leave-absences/shared/modules/controllers',
  'leave-absences/shared/controllers/request.controller',
  'leave-absences/shared/instances/leave-request.instance'
], function (controllers) {
  controllers.controller('LeaveRequestCtrl', LeaveRequestCtrl);

  LeaveRequestCtrl.$inject = ['$controller', '$log', '$uibModalInstance',
    'directiveOptions', 'LeaveRequestInstance'];

  function LeaveRequestCtrl ($controller, $log, $modalInstance,
    directiveOptions, LeaveRequestInstance) {
    $log.debug('LeaveRequestCtrl');

    var parentRequestCtrl = $controller('RequestCtrl');
    var vm = Object.create(parentRequestCtrl);

    vm.directiveOptions = directiveOptions;
    vm.$modalInstance = $modalInstance;
    vm.initParams = {
      absenceType: {
        is_sick: false
      }
    };

    vm._initRequest = _initRequest;

    (function init () {
      vm.loading.absenceTypes = true;

      vm._init()
        .finally(function () {
          vm.loading.absenceTypes = false;
        });
    })();

    /**
     * Initialize leaverequest based on attributes that come from directive
     */
    function _initRequest () {
      var attributes = vm._initRequestAttributes();

      vm.request = LeaveRequestInstance.init(attributes);
    }

    return vm;
  }
});
