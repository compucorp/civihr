(function (CRM) {
  CRM.vars = {
    leaveAbsences: {
      baseURL: '/base/tools/extensions/civihr/uk.co.compucorp.civicrm.hrleaveandabsences'
    }
  };

  CRM.url({
    back: '/index.php?q=*path*&*query*',
    front: '/index.php?q=*path*&*query*'
  });
})(CRM);
