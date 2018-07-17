(function (require) {
  require(['leave-absences/crm/app.list.absenceperiod'], function (app) {
    CRM.$(document).trigger('hrappready.listabsenceperiod', [app]);
  });
})(require);
