(function (CRM, require) {
  var srcPath = CRM.vars.leaveAndAbsences.baseURL + '/js/angular/src/leave-absences';

  require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
      'leave-absences/shared': srcPath + '/shared',
      'leave-absences/manager-leave': srcPath + '/manager-leave'
    }
  });

  require(['leave-absences/shared/config'], function () {
    require([
      'leave-absences/manager-leave/app',
      'leave-absences/shared/models/leave-request.model'
    ],
    function (angular) {
      angular.bootstrap(document.querySelector('[data-leave-absences-manager-leave]'), ['manager-leave']);
    });
  });
})(CRM, require);
