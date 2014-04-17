(function ($, _) {
module('CRM.HRAbsenceApp.List.ListView', {
  setUp: function() {
    CRM.HRAbsenceApp.contentRegion.destroy();
  }
});

test("With vacation and TOIL records in FY2012", function() {
  CRM.HRAbsenceApp.contentRegion.show(new CRM.HRAbsenceApp.List.ListView({
    collection: new CRM.HRAbsenceApp.Models.AbsenceCollection(CRM.fixtures.get(['vacationFeb2013', 'toilFeb2013'])),
    entitlementCollection: new CRM.HRAbsenceApp.Models.EntitlementCollection(CRM.fixtures.get(['vacationEnt2013'])),
    absenceTypeCollection: new CRM.HRAbsenceApp.Models.AbsenceTypeCollection(CRM.fixtures.get(['absenceTypes']))
  }));
  var $el = CRM.HRAbsenceApp.contentRegion.$el;

  equal($el.find('tr:first > th').length, 5);
  equal($el.find('tr:first > :nth-child(3)').text(), 'Vacation');
  equal($el.find('tr:first > :nth-child(4)').text(), 'TOIL');
  equal($el.find('tr:first > :nth-child(5)').text(), 'TOIL (Credit)');

  // Both data sets are in Feb 2013 (which is FY 2012)

  // Check header row
  equal($el.find('.hrabsence-list-period-header').length, 1);
  equal($el.find('.hrabsence-list-period-header[data-period-id=2]').length, 1);

  // Check data rows
  equal($el.find('.hrabsence-list-item').length, 4);
  equal($el.find('.hrabsence-list-item[data-period-id=2]').length, 4);

  // Check entitlement rows
  equal($el.find('.hrabsence-list-entitlement').length, 1);
  equal($el.find('.hrabsence-list-entitlement[data-period-id=2]').length, 1);
  assertLike($($el.find('.hrabsence-list-entitlement > td')[2]).text(), '+6.00'); // Vacation
  assertLike($($el.find('.hrabsence-list-entitlement > td')[3]).text(), '+5.00'); // TOIL, no entitlements
  assertLike($($el.find('.hrabsence-list-entitlement > td')[4]).text(), ''); // TOIL credit, no entitlements

  // Check balance rows
  equal($el.find('.hrabsence-list-balance').length, 1);
  equal($el.find('.hrabsence-list-balance[data-period-id=2]').length, 1);
  assertLike($($el.find('.hrabsence-list-balance[data-period-id=2] > td')[2]).text(), '+4.50'); // Vacation FY2012
  assertLike($($el.find('.hrabsence-list-balance[data-period-id=2] > td')[3]).text(), '+3.50'); // TOIL FY2012
  assertLike($($el.find('.hrabsence-list-balance[data-period-id=2] > td')[4]).text(), '+0.50'); // TOIL credit FY2012
});

test("With TOIL records in FY2012", function() {
  CRM.HRAbsenceApp.contentRegion.show(new CRM.HRAbsenceApp.List.ListView({
    collection: new CRM.HRAbsenceApp.Models.AbsenceCollection(CRM.fixtures.get(['toilFeb2013'])),
    entitlementCollection: new CRM.HRAbsenceApp.Models.EntitlementCollection(CRM.fixtures.get(['vacationEnt2013'])),
    absenceTypeCollection: new CRM.HRAbsenceApp.Models.AbsenceTypeCollection(CRM.fixtures.get(['absenceTypes']))
  }));
  var $el = CRM.HRAbsenceApp.contentRegion.$el;

  equal($el.find('tr:first > th').length, 4);
  equal($el.find('tr:first > :nth-child(3)').text(), 'TOIL');
  equal($el.find('tr:first > :nth-child(4)').text(), 'TOIL (Credit)');

  // Data from in Feb 2013 (which is FY 2012)

  // Check header row
  equal($el.find('.hrabsence-list-period-header').length, 1);
  equal($el.find('.hrabsence-list-period-header[data-period-id=2]').length, 1);

  // Check data rows
  equal($el.find('.hrabsence-list-item').length, 3);
  equal($el.find('.hrabsence-list-item[data-period-id=2]').length, 3);

  // Check entitlement rows
  equal($el.find('.hrabsence-list-entitlement').length, 1);
  equal($el.find('.hrabsence-list-entitlement[data-period-id=2]').length, 1);
  assertLike($($el.find('.hrabsence-list-entitlement > td')[3]).text(), ''); // TOIL, no entitlements
  assertLike($($el.find('.hrabsence-list-entitlement > td')[4]).text(), ''); // TOIL credit, no entitlements

  // Check balance rows
  equal($el.find('.hrabsence-list-balance').length, 1);
  equal($el.find('.hrabsence-list-balance[data-period-id=2]').length, 1);
  assertLike($($el.find('.hrabsence-list-balance[data-period-id=2] > td')[2]).text(), '+3.50'); // TOIL FY2012
  assertLike($($el.find('.hrabsence-list-balance[data-period-id=2] > td')[3]).text(), '+0.50'); // TOIL credit FY2012
});

test("With vacation records in FY2012 + FY2013", function() {
  CRM.HRAbsenceApp.contentRegion.show(new CRM.HRAbsenceApp.List.ListView({
    collection: new CRM.HRAbsenceApp.Models.AbsenceCollection(CRM.fixtures.get(['vacationFeb2013', 'vacationApr2013'])),
    entitlementCollection: new CRM.HRAbsenceApp.Models.EntitlementCollection(CRM.fixtures.get(['vacationEnt2013'])),
    absenceTypeCollection: new CRM.HRAbsenceApp.Models.AbsenceTypeCollection(CRM.fixtures.get(['absenceTypes']))
  }));
  var $el = CRM.HRAbsenceApp.contentRegion.$el;

  equal($el.find('tr:first > th').length, 3);
  equal($el.find('tr:first > :nth-child(3)').text(), 'Vacation');

  // Data from in Feb 2013 (FY 2012) and Apr 2013 (FY 2013)

  // Check header row
  equal($el.find('.hrabsence-list-period-header').length, 2);
  equal($el.find('.hrabsence-list-period-header[data-period-id=2]').length, 1);
  equal($el.find('.hrabsence-list-period-header[data-period-id=3]').length, 1);

  // Check data rows
  equal($el.find('.hrabsence-list-item').length, 3);
  equal($el.find('.hrabsence-list-item[data-period-id=2]').length, 1);
  assertLike($($el.find('.hrabsence-list-item[data-period-id=2]')[0]).find('.hrabsence-list-desc'), 'Vacation (Feb 15, 2013 - Feb 16, 2013)');
  assertLike($($el.find('.hrabsence-list-item[data-period-id=2]')[0]).find('[data-duration-actid=10]'), '-1.50');
  equal($el.find('.hrabsence-list-item[data-period-id=3]').length, 2);
  assertLike($($el.find('.hrabsence-list-item[data-period-id=3]')[0]).find('.hrabsence-list-desc'), 'Vacation (Apr 4, 2013)');
  assertLike($($el.find('.hrabsence-list-item[data-period-id=3]')[0]).find('[data-duration-actid=10]'), '-1.00');
  assertLike($($el.find('.hrabsence-list-item[data-period-id=3]')[1]).find('.hrabsence-list-desc'), 'Vacation');
  assertLike($($el.find('.hrabsence-list-item[data-period-id=3]')[1]).find('[data-duration-actid=10]'), '0.00');

  // Check entitlement rows
  equal($el.find('.hrabsence-list-entitlement').length, 2);
  equal($el.find('.hrabsence-list-entitlement[data-period-id=2]').length, 1);
  assertLike($($el.find('.hrabsence-list-entitlement[data-period-id=2] > td')[2]).text(), '+6.00'); // Vacation FY2012
  assertLike($($el.find('.hrabsence-list-entitlement[data-period-id=2] > td')[3]).text(), ''); // TOIL, no entitlements
  assertLike($($el.find('.hrabsence-list-entitlement[data-period-id=2] > td')[4]).text(), ''); // TOIL credit, no entitlements
  equal($el.find('.hrabsence-list-entitlement[data-period-id=3]').length, 1);
  assertLike($($el.find('.hrabsence-list-entitlement[data-period-id=3] > td')[2]).text(), ''); // Vacation FY2013
  assertLike($($el.find('.hrabsence-list-entitlement[data-period-id=3] > td')[3]).text(), ''); // TOIL, no entitlements
  assertLike($($el.find('.hrabsence-list-entitlement[data-period-id=3] > td')[4]).text(), ''); // TOIL credit, no entitlements

  // Check balance rows
  equal($el.find('.hrabsence-list-balance').length, 2);
  equal($el.find('.hrabsence-list-balance[data-period-id=2]').length, 1);
  assertLike($($el.find('.hrabsence-list-balance[data-period-id=2] > td')[2]).text(), '+4.50'); // Vacation FY2012
  assertLike($($el.find('.hrabsence-list-balance[data-period-id=2] > td')[3]).text(), ''); // TOIL FY2012
  assertLike($($el.find('.hrabsence-list-balance[data-period-id=2] > td')[4]).text(), ''); // TOIL credit FY2012
  equal($el.find('.hrabsence-list-balance[data-period-id=3]').length, 1);
  assertLike($($el.find('.hrabsence-list-balance[data-period-id=3] > td')[2]).text(), '-1.00'); // Vacation FY2013
  assertLike($($el.find('.hrabsence-list-balance[data-period-id=3] > td')[3]).text(), ''); // TOIL FY2013
  assertLike($($el.find('.hrabsence-list-balance[data-period-id=3] > td')[4]).text(), ''); // TOIL credit FY2013
});
}(CRM.$, CRM._));
