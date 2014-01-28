module('CRM.HRAbsenceApp.moment');

test("Use default format", function() {
  var m = CRM.HRAbsenceApp.moment('2014-01-02 03:04:05');
  equal(m.year(), 2014);
  equal(m.month(), 0);
  equal(m.date(), 2);
  equal(m.hour(), 3);
  equal(m.minute(), 4);
  equal(m.second(), 5);
})

/*
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
  */