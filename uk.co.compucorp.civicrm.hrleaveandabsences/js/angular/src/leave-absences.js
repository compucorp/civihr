(function () {
  var extPath = CRM.vars.leaveAbsences.baseURL + '/js/src/leave-absences';

  require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
      'leave-absences': extPath,
      'leave-absences/vendor/ui-router': extPath + '/vendor/angular-ui-router.min'
    }
  });

  require(['leave-absences/app'], function (angular) {
    angular.bootstrap(document.querySelector('[data-leave-absences-app]'), ['leave-absences']);
  });
})(require);
