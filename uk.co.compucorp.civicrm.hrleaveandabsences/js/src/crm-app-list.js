(function (require) {
  require(['leave-absences/crm/app.list'], function (app) {
    CRM.$(document).trigger('ready.list', [app]);
  });
})(require);
