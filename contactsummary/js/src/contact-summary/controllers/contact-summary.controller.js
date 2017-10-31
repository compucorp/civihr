/* eslint-env amd */

define(function () {
  'use strict';

  ContactSummaryCtrl.__name = 'ContactSummaryCtrl';
  ContactSummaryCtrl.$inject = ['$log', 'settings'];

  function ContactSummaryCtrl ($log, settings) {
    $log.debug('Controller: ContactSummaryCtrl');

    var templateDir = settings.pathBaseUrl + settings.pathTpl;

    this.partials = {
      keyDetails: templateDir + '/include/keyDetails.html',
      keyDates: templateDir + '/include/keyDates.html'
    };

    this.ready = false;
  }

  return ContactSummaryCtrl;
});
