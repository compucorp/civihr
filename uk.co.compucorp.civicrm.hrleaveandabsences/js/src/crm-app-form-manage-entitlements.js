(function (require) {
  require(['leave-absences/crm/app.form.manage-entitlements'], function () {
    CRM.$(document).trigger('ready.formmanageentitlements');
  });
})(require);
