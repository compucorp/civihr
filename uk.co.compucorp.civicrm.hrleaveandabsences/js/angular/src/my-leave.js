(function (CRM, require) {
  var srcPath = CRM.vars.leaveAndAbsences.baseURL + '/js/angular/src/leave-absences';

  require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
      'leave-absences/shared': srcPath + '/shared',
      'leave-absences/my-leave': srcPath + '/my-leave',
    }
  });

  require([
      'leave-absences/shared/config',
      'leave-absences/my-leave/app',
    ],
    function (__, angular) {
      angular.bootstrap(document.querySelector('[data-leave-absences-my-leave]'), ['my-leave']);
    });
})(CRM, require);
