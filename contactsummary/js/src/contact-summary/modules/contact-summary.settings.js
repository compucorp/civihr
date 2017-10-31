define([
    'common/angular'
], function (angular) {
    'use strict';

    return angular.module('contactsummary.settings', []).constant('settings', {
        classNamePrefix: 'contactSummary-',
        contactId: decodeURIComponent((new RegExp('[?|&]cid=([^&;]+?)(&|#|;|$)').exec(location.search) || [, ""])[1].replace(/\+/g, '%20')) || null,
        debug: true,
        pathApp: '',
        pathRest: CRM.url('civicrm/ajax/rest'),
        pathBaseUrl: CRM.vars.contactsummary.baseURL + '/',
        // pathRest: '/index.php?q=civicrm/ajax/rest',
        // pathBaseUrl: 'http://localhost:8900/sites/all/modules/civicrm/tools/extensions/civihr/contactsummary/',
        pathTpl: 'views/'
    });
})
