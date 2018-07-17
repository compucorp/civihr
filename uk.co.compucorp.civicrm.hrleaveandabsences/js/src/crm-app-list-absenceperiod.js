(function (require) {
  require(['leave-absences/crm/app.list.absenceperiod'], function () {
    CRM.$(document).trigger('ready.listabsenceperiod');
  });
})(require);
