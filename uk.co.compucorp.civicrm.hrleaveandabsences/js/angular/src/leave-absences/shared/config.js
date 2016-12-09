(function (require) {
  define(function () {
    var srcPath = CRM.vars.leaveAndAbsences.baseURL + '/js/angular/src/leave-absences/shared';

    // This require.config will picked up by the r.js optimizer
    require.config({
      paths: {
        'leave-absences/shared/ui-router': 'leave-absences/shared/vendor/angular-ui-router.min',
      },
      shim: {
        'leave-absences/shared/ui-router': {}
      }
    });

    // This require.config will be used by the "live" RequireJS (with debug ON)
    require.config({
      paths: {
        'leave-absences/shared/ui-router': srcPath + '/vendor/angular-ui-router.min',
      }
    });
  });
})(require);