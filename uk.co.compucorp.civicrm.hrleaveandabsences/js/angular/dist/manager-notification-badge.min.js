(function (CRM, require) {
  var srcPath = CRM.vars.leaveAndAbsences.baseURL + '/js/angular/src/leave-absences';

  require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
      'leave-absences/shared': srcPath + '/shared',
      'leave-absences/manager-notification-badge': srcPath + '/manager-notification-badge'
    }
  });

  require(['leave-absences/shared/config'], function () {
    require([
      'leave-absences/manager-notification-badge/app'
    ],
    function (angular) {
      angular.bootstrap(
        document.querySelector('[data-leave-absences-manager-notification-badge]'), ['manager-notification-badge']
      );
    });
  });
})(CRM, require);
