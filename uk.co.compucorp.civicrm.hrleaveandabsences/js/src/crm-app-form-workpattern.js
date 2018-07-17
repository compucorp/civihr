(function (require) {
  require(['leave-absences/crm/app.form.workpattern'], function (app) {
    CRM.$(document).trigger('ready.formworkpattern', [app]);
  });
})(require);
