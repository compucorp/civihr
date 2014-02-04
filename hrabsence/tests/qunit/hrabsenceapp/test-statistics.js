module('CRM.HRAbsenceApp.Statistics.StatisticsView', {
  setUp: function() {
    CRM.HRAbsenceApp.contentRegion.destroy();
  }
});

test("With vacation and TOIL records in FY2012", function() {
  CRM.HRAbsenceApp.contentRegion.show(new CRM.HRAbsenceApp.Statistics.StatisticsView({
    collection: new CRM.HRAbsenceApp.Models.AbsenceCollection(CRM.fixtures.get(['vacationFeb2013', 'toilFeb2013'])),
    entitlementCollection: new CRM.HRAbsenceApp.Models.EntitlementCollection(CRM.fixtures.vacationEnt2013),
    absenceTypeCollection: new CRM.HRAbsenceApp.Models.AbsenceTypeCollection(CRM.fixtures.absenceTypes)
  }));
  var $el = CRM.HRAbsenceApp.contentRegion.$el;

  equal($el.find('tr:first > th').length, 6);
  equal($el.find('tbody tr:first td:first').length, 1);
  equal($el.find('tbody tr:first td:nth-child(2)').text(), 'Vacation');
  assertLike($el.find('tbody tr:first td:nth-child(3)').text(), '+6.00');
  assertLike($el.find('tbody tr:first td:nth-child(4)').text(), '0.00');
  assertLike($el.find('tbody tr:first td:nth-child(5)').text(), '+1.50');
  assertLike($el.find('tbody tr:first td:nth-child(6)').text(), '+4.50');

  equal($el.find('tbody tr:nth-child(2) td:first').length, 1);
  equal($el.find('tbody tr:nth-child(2) td:nth-child(2)').text(), 'TOIL');
  equal($el.find('tbody tr:nth-child(2) td:nth-child(3)').text(), '');
  assertLike($el.find('tbody tr:nth-child(2) td:nth-child(4)').text(), '0.00');
  assertLike($el.find('tbody tr:nth-child(2) td:nth-child(5)').text(), '+1.50');
  assertLike($el.find('tbody tr:nth-child(2) td:nth-child(6)').text(), '-1.50');

  equal($el.find('tbody tr:nth-child(3) td:first').length, 1);
  equal($el.find('tbody tr:nth-child(3) td:nth-child(2)').text(), 'TOIL (Credit)');
  equal($el.find('tbody tr:nth-child(3) td:nth-child(3)').text(), '');
  assertLike($el.find('tbody tr:nth-child(3) td:nth-child(4)').text(), '0.00');
  assertLike($el.find('tbody tr:nth-child(3) td:nth-child(5)').text(), '+0.50');
  assertLike($el.find('tbody tr:nth-child(3) td:nth-child(6)').text(), '-0.50');
});

test("With TOIL records in FY2012", function() {
  CRM.HRAbsenceApp.contentRegion.show(new CRM.HRAbsenceApp.Statistics.StatisticsView({
    collection: new CRM.HRAbsenceApp.Models.AbsenceCollection(CRM.fixtures.get(['toilFeb2013'])),
    entitlementCollection: new CRM.HRAbsenceApp.Models.EntitlementCollection(CRM.fixtures.vacationEnt2013),
    absenceTypeCollection: new CRM.HRAbsenceApp.Models.AbsenceTypeCollection(CRM.fixtures.absenceTypes)
  }));
  var $el = CRM.HRAbsenceApp.contentRegion.$el;

  equal($el.find('tr:first > th').length, 6);

  equal($el.find('tbody tr:first td:first').length, 1);
  equal($el.find('tbody tr:first td:nth-child(2)').text(), 'TOIL');
  equal($el.find('tbody tr:first td:nth-child(3)').text(), '');
  assertLike($el.find('tbody tr:first td:nth-child(4)').text(), '0.00');
  assertLike($el.find('tbody tr:first td:nth-child(5)').text(), '+1.50');
  assertLike($el.find('tbody tr:first td:nth-child(6)').text(), '-1.50');

  equal($el.find('tbody tr:nth-child(2) td:first').length, 1);
  equal($el.find('tbody tr:nth-child(2) td:nth-child(2)').text(), 'TOIL (Credit)');
  equal($el.find('tbody tr:nth-child(2) td:nth-child(3)').text(), '');
  assertLike($el.find('tbody tr:nth-child(2) td:nth-child(4)').text(), '0.00');
  assertLike($el.find('tbody tr:nth-child(2) td:nth-child(5)').text(), '+0.50');
  assertLike($el.find('tbody tr:nth-child(2) td:nth-child(6)').text(), '-0.50');
});

test("With vacation records in FY2012 + FY2013", function() {
  CRM.HRAbsenceApp.contentRegion.show(new CRM.HRAbsenceApp.Statistics.StatisticsView({
    collection: new CRM.HRAbsenceApp.Models.AbsenceCollection(CRM.fixtures.get(['vacationFeb2013', 'vacationApr2013'])),
    entitlementCollection: new CRM.HRAbsenceApp.Models.EntitlementCollection(CRM.fixtures.vacationEnt2013),
    absenceTypeCollection: new CRM.HRAbsenceApp.Models.AbsenceTypeCollection(CRM.fixtures.absenceTypes)
  }));
  var $el = CRM.HRAbsenceApp.contentRegion.$el;

  equal($el.find('tr:first > th').length, 6);

  // Data from in Feb 2013 (FY 2012) and Apr 2013 (FY 2013)
  equal($el.find('.hrabsence-statistics-header').length, 1);
  equal($el.find('.hrabsence-list-item').length, 2);
  equal($el.find('.hrabsence-list-item[data-statistics-id=2-10]').length, 1);
  equal($el.find('.hrabsence-list-item[data-statistics-id=3-10]').length, 1);

  assertLike(cj($el.find('.hrabsence-list-item[data-statistics-id=2-10]').find('.hrabsence-statistics-period-desc')), 'FY 2012');
  assertLike(cj($el.find('.hrabsence-list-item[data-statistics-id=2-10]').find('.hrabsence-statistics-leave-type')), 'Vacation');
  assertLike(cj($el.find('.hrabsence-list-item[data-statistics-id=2-10]').find('.hrabsence-statistics-bal')), '+4.50');

  assertLike(cj($el.find('.hrabsence-list-item[data-statistics-id=3-10]').find('.hrabsence-statistics-period-desc')), 'FY 2013');
  assertLike(cj($el.find('.hrabsence-list-item[data-statistics-id=2-10]').find('.hrabsence-statistics-leave-type')), 'Vacation');
  assertLike(cj($el.find('.hrabsence-list-item[data-statistics-id=3-10]').find('.hrabsence-statistics-bal')), '-1.00');
});
