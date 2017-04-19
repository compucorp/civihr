(function (CRM) {
  define([
    'common/angular'
  ], function (angular) {
    return angular.module('absence-tab.settings', []).constant('settings', {
      contactId: decodeURIComponent((new RegExp('[?|&]cid=([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null,
      debug: CRM.debug,
      pathTpl: CRM.vars.leaveAndAbsences.baseURL + '/views/absence-tab/'
    });
  });
})(CRM);
