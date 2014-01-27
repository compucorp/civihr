module('CRM.HRAbsenceApp.Calendar.CalendarView', {
  setUp: function() {
    CRM.HRAbsenceApp.contentRegion.destroy();
  }
});

test("Check Legend", function() {
  CRM.HRAbsenceApp.contentRegion.show(new CRM.HRAbsenceApp.Calendar.CalendarView({
    collection: new CRM.HRAbsenceApp.Models.AbsenceCollection(CRM.fixtures.get(['vacationFeb2013', 'toilFeb2013']))
  }));
  var $el = CRM.HRAbsenceApp.contentRegion.$el;

  assertLike(cj($el.find('.hrabsence-bg-0-debit ~ td:first')), 'Vacation');
  assertLike(cj($el.find('.hrabsence-bg-1-debit ~ td:first')), 'TOIL');
  assertLike(cj($el.find('.hrabsence-bg-1-credit ~ td:first')), 'TOIL (Credit)');
});
