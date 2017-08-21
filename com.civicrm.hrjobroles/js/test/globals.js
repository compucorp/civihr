(function (CRM) {
  CRM.vars = {
    hrjobroles: {
      baseURL: '/base/tools/extensions/civihr/com.civicrm.hrjobroles'
    }
  };

  CRM.url({
    back: '/index.php?q=*path*&*query*',
    front: '/index.php?q=*path*&*query*'
  });
})(CRM);
