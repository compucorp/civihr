require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
        'contact-summary': CRM.vars.contactsummary.baseURL + '/js/src/contact-summary'
    }
});

require(['contact-summary/app'], function () {
  function bootstrap() {
    angular.bootstrap(document.getElementById('contactsummary'), ['contactsummary']);
  }
  angular.element(document).ready(function () {
    if(window.contactsummaryLoad) {
      bootstrap();
    } else {
      document.addEventListener('contactsummaryLoad', function() {
        bootstrap();
      });
    }
  });
});
