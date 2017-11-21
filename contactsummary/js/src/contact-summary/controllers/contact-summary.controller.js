/* eslint-env amd */

define(function () {
  'use strict';

  ContactSummaryController.__name = 'ContactSummaryController';
  ContactSummaryController.$inject = ['$log', 'settings'];

  function ContactSummaryController ($log, settings) {
    $log.debug('Controller: ContactSummaryController');

    var templateDir = settings.pathBaseUrl + settings.pathTpl;
    var vm = this;

    vm.contactId = settings.contactId;
    vm.ready = false;
    vm.partials = {
      keyDetails: templateDir + '/include/keyDetails.html',
      keyDates: templateDir + '/include/keyDates.html'
    };
  }

  return ContactSummaryController;
});
