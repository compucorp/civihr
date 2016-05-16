(function (CRM) {
    CRM.vars = {
        appraisals: {
            baseURL: '/base/tools/extensions/civihr/uk.co.compucorp.civicrm.appraisals'
        }
    };

    CRM.url({
        back: '/index.php?q=*path*&*query*',
        front: '/index.php?q=*path*&*query*'
    });
})(CRM);
