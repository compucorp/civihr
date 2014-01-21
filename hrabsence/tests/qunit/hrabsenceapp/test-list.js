module('CRM.HRAbsenceApp.List.ListView', {
  setUp: function() {
    CRM.HRAbsenceApp.contentRegion.destroy();
  }
});

test("With vacation and TOIL records in FY2012", function() {
  CRM.HRAbsenceApp.contentRegion.show(new CRM.HRAbsenceApp.List.ListView({
    collection: new CRM.HRAbsenceApp.Models.AbsenceCollection(CRM.fixtures.get(['vacationFeb2013', 'toilFeb2013']))
  }));
  var $el = CRM.HRAbsenceApp.contentRegion.$el;

  equal($el.find('tr:first > th').length, 5);
  equal($el.find('tr:first > :nth-child(3)').text(), 'Vacation');
  equal($el.find('tr:first > :nth-child(4)').text(), 'TOIL');
  equal($el.find('tr:first > :nth-child(5)').text(), 'TOIL (Credit)');

  // Both data sets are in Feb 2013 (which is FY 2012)
  equal($el.find('.hrabsence-list-period-header').length, 1);
  equal($el.find('.hrabsence-list-period-header[data-period-id=2]').length, 1);
  equal($el.find('.hrabsence-list-item').length, 4);
  equal($el.find('.hrabsence-list-item[data-period-id=2]').length, 4);
});

test("With TOIL records in FY2012", function() {
  CRM.HRAbsenceApp.contentRegion.show(new CRM.HRAbsenceApp.List.ListView({
    collection: new CRM.HRAbsenceApp.Models.AbsenceCollection(CRM.fixtures.get(['toilFeb2013']))
  }));
  var $el = CRM.HRAbsenceApp.contentRegion.$el;

  equal($el.find('tr:first > th').length, 4);
  equal($el.find('tr:first > :nth-child(3)').text(), 'TOIL');
  equal($el.find('tr:first > :nth-child(4)').text(), 'TOIL (Credit)');

  // Data from in Feb 2013 (which is FY 2012)
  equal($el.find('.hrabsence-list-period-header').length, 1);
  equal($el.find('.hrabsence-list-period-header[data-period-id=2]').length, 1);
  equal($el.find('.hrabsence-list-item').length, 3);
  equal($el.find('.hrabsence-list-item[data-period-id=2]').length, 3);
});

test("With vacation records in FY2012 + FY2013", function() {
  CRM.HRAbsenceApp.contentRegion.show(new CRM.HRAbsenceApp.List.ListView({
    collection: new CRM.HRAbsenceApp.Models.AbsenceCollection(CRM.fixtures.get(['vacationFeb2013', 'vacationApr2013']))
  }));
  var $el = CRM.HRAbsenceApp.contentRegion.$el;

  equal($el.find('tr:first > th').length, 3);
  equal($el.find('tr:first > :nth-child(3)').text(), 'Vacation');

  // Data from in Feb 2013 (FY 2012) and Apr 2013 (FY 2013)
  equal($el.find('.hrabsence-list-period-header').length, 2);
  equal($el.find('.hrabsence-list-period-header[data-period-id=2]').length, 1);
  equal($el.find('.hrabsence-list-period-header[data-period-id=3]').length, 1);
  equal($el.find('.hrabsence-list-item').length, 3);
  equal($el.find('.hrabsence-list-item[data-period-id=2]').length, 1);
  assertLike(cj($el.find('.hrabsence-list-item[data-period-id=2]')[0]).find('.hrabsence-list-desc'), 'Vacation (Feb 15, 2013 - Feb 16, 2013)');
  assertLike(cj($el.find('.hrabsence-list-item[data-period-id=2]')[0]).find('[data-duration-actid=10]'), '+/- 1.50'); // FIXME signage
  equal($el.find('.hrabsence-list-item[data-period-id=3]').length, 2);
  assertLike(cj($el.find('.hrabsence-list-item[data-period-id=3]')[0]).find('.hrabsence-list-desc'), 'Vacation (Apr 4, 2013)');
  assertLike(cj($el.find('.hrabsence-list-item[data-period-id=3]')[0]).find('[data-duration-actid=10]'), '+/- 1.00'); // FIXME signage
  assertLike(cj($el.find('.hrabsence-list-item[data-period-id=3]')[1]).find('.hrabsence-list-desc'), 'Vacation');
  assertLike(cj($el.find('.hrabsence-list-item[data-period-id=3]')[1]).find('[data-duration-actid=10]'), '+/- 0.00'); // FIXME signage
});

