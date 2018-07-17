(function (require) {
  require(['leave-absences/crm/app.list'], function () {
    CRM.$(document).trigger('CRMHrleaveandabsencesScriptIsReady');
  });
})(require);
