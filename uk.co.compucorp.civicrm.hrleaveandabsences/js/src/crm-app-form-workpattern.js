(function (require) {
  require(['leave-absences/crm/app.form.workpattern'], function (app) {
    CRM.$(document).trigger('hrappready.formworkpattern', [app]);
  });
})(require);
