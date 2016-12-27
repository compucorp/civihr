(function (CRM, require) {
  var srcPath = CRM.vars.leaveAndAbsences.baseURL + '/js/angular/src/leave-absences';

  require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
      'leave-absences/shared': srcPath + '/shared',
      'leave-absences/manager-leave': srcPath + '/manager-leave',
    }
  });

  require([
      'leave-absences/shared/config',
      'leave-absences/manager-leave/app',
    ],
    function (__, angular) {
      angular.bootstrap(document.querySelector('[data-leave-absences-manager-leave]'), ['manager-leave']);
    });
})(CRM, require);
