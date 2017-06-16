define([
  'leave-absences/absence-tab/modules/components',
], function (components) {

  components.component('absenceTabWorkPatterns', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/absence-tab-work-patterns.html';
    }],
    controllerAs: 'workpatterns',
    controller: ['$log', '$rootElement', '$uibModal', 'settings', controller]
  });

  function controller($log, $rootElement, $uibModal, settings) {
    $log.debug('Component: absence-tab-work-patterns');

    var vm = {};

    // TODO -This is temporary to open the modal, test cases are pending
    vm.openModal = function () {
      $uibModal.open({
        appendTo: $rootElement.children().eq(0),
        templateUrl: settings.pathTpl + 'components/absence-tab-custom-work-pattern-modal.html',
      });
    };

    return vm;
  }
});
