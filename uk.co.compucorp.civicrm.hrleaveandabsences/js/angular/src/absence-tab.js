(function (CRM, require) {
  var srcPath = CRM.vars.leaveAndAbsences.baseURL + '/js/angular/src/leave-absences';

  require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
      'leave-absences/shared': srcPath + '/shared',
      'leave-absences/absence-tab': srcPath + '/absence-tab'
    }
  });

  require([
    'leave-absences/shared/config',
    'leave-absences/absence-tab/app'
  ], function () {
    document.dispatchEvent(typeof window.CustomEvent == "function" ? new CustomEvent('absenceTabReady') : (function () {
      var e = document.createEvent('Event');
      e.initEvent('absenceTabReady', true, true);
      return e;
    })());
  });
})(CRM, require);
