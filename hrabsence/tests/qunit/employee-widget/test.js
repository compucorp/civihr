module('CRM.HRAbsenceApp.List.ListView', {
  setUp: function() {
    CRM.HRAbsenceApp.contentRegion.destroy();
  }
});

test("With vacation and TOIL records", function() {
  CRM.HRAbsenceApp.contentRegion.show(new CRM.HRAbsenceApp.List.ListView({
    collection: new CRM.HRAbsenceApp.Models.AbsenceCollection(CRM.mockData.get(['vacation2013', 'toil2013']))
  }));
  var $el = CRM.HRAbsenceApp.contentRegion.$el;

  assertContainsText("List view for 4 item(s)", $el, 'check count');
});


test("With TOIL records", function() {
  CRM.HRAbsenceApp.contentRegion.show(new CRM.HRAbsenceApp.List.ListView({
    collection: new CRM.HRAbsenceApp.Models.AbsenceCollection(CRM.mockData.get(['toil2013']))
  }));
  var $el = CRM.HRAbsenceApp.contentRegion.$el;

  assertContainsText("List view for 3 item(s)", $el, 'check count');
});
