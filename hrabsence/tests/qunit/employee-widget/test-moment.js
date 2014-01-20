module('CRM.HRAbsenceApp.moment');

test("Overflow the cache", function() {
  if (CRM.HRAbsenceApp.moment.cache) delete CRM.HRAbsenceApp.moment.cache;

  for (var i = 0; i < 400; i++) {
    var year = 1900 + i;
    CRM.HRAbsenceApp.moment("" + year + "-01-02 03:04:05");
    equal(CRM.HRAbsenceApp.moment.cache.size, i + 1);
  }

  // accessing the same 199 values causes no change in size
  for (var i = 0; i < 400; i++) {
    var year = 1900 + i;
    CRM.HRAbsenceApp.moment("" + year + "-01-02 03:04:05");
    equal(CRM.HRAbsenceApp.moment.cache.size, 400);
  }

  CRM.HRAbsenceApp.moment("2500-01-02 03:04:05");
  equal(CRM.HRAbsenceApp.moment.cache.size, 381);
});