(function (require) {
  require([
    'common/angular',
    'leave-absences/leave-type-wizard/leave-type-wizard.module'
  ],
  function (angular) {
    angular.bootstrap(
      document.querySelector('[data-leave-absences-leave-type-wizard]'),
      ['leave-type-wizard']
    );
  });
})(require);
