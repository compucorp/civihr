/* global CustomEvent */

(function (CRM, require) {
  var srcPath = CRM.vars.leaveAndAbsences.baseURL + '/js/angular/src/leave-absences';

  require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
      'leave-absences/shared': srcPath + '/shared',
      'leave-absences/admin-dashboard': srcPath + '/admin-dashboard'
    }
  });

  require(['leave-absences/shared/config'], function () {
    require([
      'leave-absences/admin-dashboard/app'
    ],
    function () {
      document.dispatchEvent(typeof window.CustomEvent === 'function' ? new CustomEvent('adminDashboardReady') : (function () {
        var e = document.createEvent('Event');
        e.initEvent('adminDashboardReady', true, true);
        return e;
      })());
    });
  });
})(CRM, require);
