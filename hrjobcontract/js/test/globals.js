(function (CRM) {
  CRM.jobContractTabApp = {
    path: '/base/tools/extensions/civihr/hrjobcontract/'
  };

  CRM.url({
    back: '/index.php?q=*path*&*query*',
    front: '/index.php?q=*path*&*query*'
  });
})(CRM);
