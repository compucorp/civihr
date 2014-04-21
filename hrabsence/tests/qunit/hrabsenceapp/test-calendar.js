(function ($, _) {
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

    assertLike($($el.find('.hrabsence-legend .hrabsence-bg-0-debit')), 'Vacation');
    assertLike($($el.find('.hrabsence-legend .hrabsence-bg-1-debit')), 'TOIL');
    assertLike($($el.find('.hrabsence-legend .hrabsence-bg-1-credit')), 'TOIL (Credit)');
  });

  test("Check Rows - Single Period", function() {
    CRM.HRAbsenceApp.contentRegion.show(new CRM.HRAbsenceApp.Calendar.CalendarView({
      criteria: new CRM.HRAbsenceApp.Models.AbsenceCriteria({
        period_id: [2]
      }),
      collection: new CRM.HRAbsenceApp.Models.AbsenceCollection(CRM.fixtures.get(['vacationFeb2013', 'toilFeb2013']))
    }));
    var $el = CRM.HRAbsenceApp.contentRegion.$el;

    equal($('table.hrabsence-calendar tr').length, 1 + 12); // header + FY2013
    assertLike($('table.hrabsence-calendar tr:nth-child(1) td:first').text(), 'Apr 2012');
    assertLike($('table.hrabsence-calendar tr:nth-child(2) td:first').text(), 'May 2012');
    assertLike($('table.hrabsence-calendar tr:nth-child(12) td:first').text(), 'Mar 2013');

    var feb2013 = $('table.hrabsence-calendar tr:nth-child(11)');
    assertLike(feb2013.find('[data-caldate=2013-02-09]').text(), ''); // irrelevant day
    assertLike(feb2013.find('[data-caldate=2013-02-10]').text(), ''); // date a request was filed - don't care
    assertLike(feb2013.find('[data-caldate=2013-02-15]').text(), 'Fr'); // date of actual absence
    assertLike(feb2013.find('[data-caldate=2013-02-16]').text(), 'Sa'); // date of actual absence
    assertLike(feb2013.find('[data-caldate=2013-02-17]').text(), ''); // irrelevant day
    assertLike(feb2013.find('.hrabsence-cal-total').text(), '+0.50 -3.00');

    ok(feb2013.find('[data-caldate=2013-02-09]').hasClass('hrabsence-bg-empty')); // irrelevant day
    ok(feb2013.find('[data-caldate=2013-02-10]').hasClass('hrabsence-bg-empty')); // date a request was filed - don't care
    ok(feb2013.find('[data-caldate=2013-02-13]').hasClass('hrabsence-bg-1-debit')); // toil
    ok(feb2013.find('[data-caldate=2013-02-14]').hasClass('hrabsence-bg-1-debit')); // toil
    ok(feb2013.find('[data-caldate=2013-02-15]').hasClass('hrabsence-bg-mixed')); // toil + vacation - weird!
    ok(feb2013.find('[data-caldate=2013-02-16]').hasClass('hrabsence-bg-0-debit')); // vacation
    ok(feb2013.find('[data-caldate=2013-02-17]').hasClass('hrabsence-bg-empty')); // irrelevant day
  });

  test("Check Rows - Two Periods", function() {
    CRM.HRAbsenceApp.contentRegion.show(new CRM.HRAbsenceApp.Calendar.CalendarView({
      criteria: new CRM.HRAbsenceApp.Models.AbsenceCriteria({
        period_id: [2, 3]
      }),
      collection: new CRM.HRAbsenceApp.Models.AbsenceCollection(CRM.fixtures.get(['vacationFeb2013', 'toilFeb2013']))
    }));
    var $el = CRM.HRAbsenceApp.contentRegion.$el;

    equal($('table.hrabsence-calendar tr').length, 1 + 12 + 12); // header + FY2012 + FY2013
    assertLike($('table.hrabsence-calendar tr:nth-child(1) td:first').text(), 'Apr 2012');
    assertLike($('table.hrabsence-calendar tr:nth-child(2) td:first').text(), 'May 2012');
    assertLike($('table.hrabsence-calendar tr:nth-child(12) td:first').text(), 'Mar 2013');
    assertLike($('table.hrabsence-calendar tr:nth-child(13) td:first').text(), 'Apr 2013');
    assertLike($('table.hrabsence-calendar tr:nth-child(14) td:first').text(), 'May 2013');
    assertLike($('table.hrabsence-calendar tr:nth-child(24) td:first').text(), 'Mar 2014');

    var feb2013 = $('table.hrabsence-calendar tr:nth-child(11)');
    assertLike(feb2013.find('[data-caldate=2013-02-09]').text(), ''); // irrelevant day
    assertLike(feb2013.find('[data-caldate=2013-02-10]').text(), ''); // date a request was filed - don't care
    assertLike(feb2013.find('[data-caldate=2013-02-15]').text(), 'Fr'); // date of actual absence
    assertLike(feb2013.find('[data-caldate=2013-02-16]').text(), 'Sa'); // date of actual absence
    assertLike(feb2013.find('[data-caldate=2013-02-17]').text(), ''); // irrelevant day

    ok(feb2013.find('[data-caldate=2013-02-09]').hasClass('hrabsence-bg-empty')); // irrelevant day
    ok(feb2013.find('[data-caldate=2013-02-10]').hasClass('hrabsence-bg-empty')); // date a request was filed - don't care
    ok(feb2013.find('[data-caldate=2013-02-13]').hasClass('hrabsence-bg-1-debit')); // toil
    ok(feb2013.find('[data-caldate=2013-02-14]').hasClass('hrabsence-bg-1-debit')); // toil
    ok(feb2013.find('[data-caldate=2013-02-15]').hasClass('hrabsence-bg-mixed')); // toil + vacation - weird!
    ok(feb2013.find('[data-caldate=2013-02-16]').hasClass('hrabsence-bg-0-debit')); // vacation
    ok(feb2013.find('[data-caldate=2013-02-17]').hasClass('hrabsence-bg-empty')); // irrelevant day
  });
}(CRM.$, CRM._));
