(function (require) {
  require(['leave-absences/crm/app.list.absenceperiod'], function () {
    CRM.$(document).trigger('CRMListAbsencePeriodScriptIsReady');
  });
})(require);
