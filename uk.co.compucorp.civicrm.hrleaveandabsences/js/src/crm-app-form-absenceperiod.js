(function (require) {
  require(['leave-absences/crm/app.form.absenceperiod'], function (app) {
    CRM.$(document).trigger('hrappready.formabsenceperiod', [app]);
  });
})(require);
