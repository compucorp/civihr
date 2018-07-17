(function (require) {
  require(['leave-absences/crm/app.form.absenceperiod'], function () {
    CRM.$(document).trigger('CRMAbsencePeriodFormScriptIsReady');
  });
})(require);
