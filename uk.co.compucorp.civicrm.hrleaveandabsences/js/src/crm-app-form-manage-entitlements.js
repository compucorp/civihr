(function (require) {
  require(['leave-absences/crm/app.form.manage-entitlements'], function (app) {
    CRM.$(document).trigger('ready.formmanageentitlements', [app]);
  });
})(require);
