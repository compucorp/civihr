(function (CRM, Drupal, require) {
  CRM.vars.leaveAndAbsences = CRM.vars.leaveAndAbsences || Drupal.settings.civihr_leave_absences;
  var srcPath = CRM.vars.leaveAndAbsences.baseURL + '/js/angular/src/leave-absences';

  require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
      'leave-absences/shared': srcPath + '/shared',
      'leave-absences/waiting-approval-notification': srcPath + '/waiting-approval-notification'
    }
  });

  require(['leave-absences/shared/config'], function () {
    require([
      'leave-absences/waiting-approval-notification/app'
    ],
    function (angular) {
      angular.bootstrap(
        document.querySelector('[data-leave-absences-waiting-approval-notification]'), ['waiting-approval-notification']
      );
    });
  });
})(CRM, window.Drupal, require);
