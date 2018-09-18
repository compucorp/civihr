/* eslint-env amd */

define(function () {
  'use strict';

  ContactSummaryController.$inject = ['$log', 'settings'];

  function ContactSummaryController ($log, settings) {
    $log.debug('Controller: ContactSummaryController');

    var vm = this;

    vm.contactId = settings.contactId;
    vm.ready = false;
    vm.partials = {
      keyDetails: settings.baseUrl + 'controllers/key-details.html',
      keyDates: settings.baseUrl + 'controllers/key-dates.html'
    };
  }

  return { ContactSummaryController: ContactSummaryController };
});
