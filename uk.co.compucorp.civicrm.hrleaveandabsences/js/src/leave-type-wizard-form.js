(function (require) {
  require([
    'common/angular',
    'leave-absences/leave-type-wizard/form/form.module'
  ],
  function (angular) {
    angular.bootstrap(
      document.querySelector('[data-leave-absences-leave-type-wizard-form]'),
      ['leave-type-wizard.form']
    );
  });
})(require);
