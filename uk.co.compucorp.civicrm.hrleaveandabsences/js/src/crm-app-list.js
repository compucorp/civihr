(function (require) {
  require(['leave-absences/crm/app.list'], function (app) {
    CRM.$(document).trigger('hrappready.list', [app]);
  });
})(require);
