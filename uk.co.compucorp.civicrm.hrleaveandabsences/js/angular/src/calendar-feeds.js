(function (CRM, require) {
  var srcPath = CRM.vars.leaveAndAbsences.baseURL + '/js/angular/src/leave-absences';

  require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
      'leave-absences/shared': srcPath + '/shared',
      'leave-absences/calendar-feeds': srcPath + '/calendar-feeds'
    }
  });

  require(['leave-absences/shared/config'], function () {
    require([
      'leave-absences/calendar-feeds/app'
    ],
    function (angular) {
      angular.bootstrap(document.querySelector('[data-leave-absences-calendar-feeds]'), ['calendar-feeds']);
    });
  });
})(CRM, require);
