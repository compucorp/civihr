module('CRM.HRAbsenceApp.Calendar.CalendarView', {
  setUp: function() {
    CRM.HRAbsenceApp.contentRegion.destroy();
  }
});

test("Check Legend", function() {
  CRM.HRAbsenceApp.contentRegion.show(new CRM.HRAbsenceApp.Calendar.CalendarView({
    criteria: new CRM.HRAbsenceApp.Models.AbsenceCriteria({
      period_id: [3]
    }),
    collection: new CRM.HRAbsenceApp.Models.AbsenceCollection(CRM.fixtures.get(['vacationFeb2013', 'toilFeb2013']))
  }));
  var $el = CRM.HRAbsenceApp.contentRegion.$el;

  assertLike(cj($el.find('.hrabsence-legend .hrabsence-bg-0-debit')), 'Vacation');
  assertLike(cj($el.find('.hrabsence-legend .hrabsence-bg-1-debit')), 'TOIL');
  assertLike(cj($el.find('.hrabsence-legend .hrabsence-bg-1-credit')), 'TOIL (Credit)');
});

test("Check Rows - Single Period", function() {
  CRM.HRAbsenceApp.contentRegion.show(new CRM.HRAbsenceApp.Calendar.CalendarView({
    criteria: new CRM.HRAbsenceApp.Models.AbsenceCriteria({
      period_id: [3]
    }),
    collection: new CRM.HRAbsenceApp.Models.AbsenceCollection(CRM.fixtures.get(['vacationFeb2013', 'toilFeb2013']))
  }));
  var $el = CRM.HRAbsenceApp.contentRegion.$el;

  equal(cj('table.hrabsence-calendar tr').length, 1+12); // header + FY2013
  assertLike(cj('table.hrabsence-calendar tr:nth-child(1) td:first').text(), 'Apr 2013');
  assertLike(cj('table.hrabsence-calendar tr:nth-child(2) td:first').text(), 'May 2013');
  assertLike(cj('table.hrabsence-calendar tr:nth-child(12) td:first').text(), 'Mar 2014');
});

test("Check Rows - Two Periods", function() {
  CRM.HRAbsenceApp.contentRegion.show(new CRM.HRAbsenceApp.Calendar.CalendarView({
    criteria: new CRM.HRAbsenceApp.Models.AbsenceCriteria({
      period_id: [2,3]
    }),
    collection: new CRM.HRAbsenceApp.Models.AbsenceCollection(CRM.fixtures.get(['vacationFeb2013', 'toilFeb2013']))
  }));
  var $el = CRM.HRAbsenceApp.contentRegion.$el;

  equal(cj('table.hrabsence-calendar tr').length, 1+12+12); // header + FY2012 + FY2013
  assertLike(cj('table.hrabsence-calendar tr:nth-child(1) td:first').text(), 'Apr 2012');
  assertLike(cj('table.hrabsence-calendar tr:nth-child(2) td:first').text(), 'May 2012');
  assertLike(cj('table.hrabsence-calendar tr:nth-child(12) td:first').text(), 'Mar 2013');
  assertLike(cj('table.hrabsence-calendar tr:nth-child(13) td:first').text(), 'Apr 2013');
  assertLike(cj('table.hrabsence-calendar tr:nth-child(14) td:first').text(), 'May 2013');
  assertLike(cj('table.hrabsence-calendar tr:nth-child(24) td:first').text(), 'Mar 2014');
});
