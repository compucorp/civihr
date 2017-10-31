(function (CRM, require) {
  require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
      'contact-summary': CRM.vars.contactsummary.baseURL + '/js/src/contact-summary'
    }
  });

  require(['contact-summary/modules/contact-summary.module'], function () {
    document.dispatchEvent(typeof window.CustomEvent == "function" ? new CustomEvent('contactsummaryReady') : (function () {
      var e = document.createEvent('Event');
      e.initEvent('contactsummaryReady', true, true);
      return e;
    })());
  });
})(CRM, require);
