(function (require) {
  require(['leave-absences/crm/app.form.workpattern'], function () {
    CRM.$(document).trigger('CRMWorkPatternFormScriptIsReady');
  });
})(require);
