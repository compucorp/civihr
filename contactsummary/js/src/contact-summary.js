require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
        'contact-summary': CRM.vars.contactsummary.baseURL + '/js/src/contact-summary'
    }
});

require(['contact-summary/app'], function () {
  // In order to avoid conflicts with other components being loaded,
  // we're delaying the bootstrapping a little
  setTimeout(function () {
    angular.bootstrap(document.getElementById('contactsummary'), ['contactsummary']);
  }, 10);
});
