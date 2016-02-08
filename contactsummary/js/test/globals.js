(function (CRM) {
    CRM.vars = {
        contactsummary: {
            baseURL: '/base/tools/extensions/civihr/contactsummary'
        }
    };

    CRM.url({
        back: '/index.php?q=*path*&*query*',
        front: '/index.php?q=*path*&*query*'
    });
})(CRM);
