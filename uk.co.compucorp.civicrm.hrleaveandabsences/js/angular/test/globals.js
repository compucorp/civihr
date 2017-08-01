(function (CRM) {
  CRM.vars = {
    leaveAndAbsences: {
      baseURL: '/base/tools/extensions/civihr/uk.co.compucorp.civicrm.hrleaveandabsences',
      contactId: '202',
      attachmentToken: 'sample123'
    }
  };

  CRM.url({
    back: '/index.php?q=*path*&*query*',
    front: '/index.php?q=*path*&*query*'
  });

  window.Drupal = {
    settings: {
      currentCiviCRMUserId: 123
    }
  };
})(CRM);
