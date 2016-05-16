require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
        'contact-summary': CRM.vars.contactsummary.baseURL + '/js/src/contact-summary'
    }
});

require(['contact-summary/app'], function () {
    angular.bootstrap(document.getElementById('contactsummary'), ['contactsummary']);
});
