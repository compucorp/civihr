define([
  'common/modules/directives',
  'common/controllers/contact-actions/contact-actions-ctrl'
], function (directives) {
  'use strict';
  directives.component('contactActions', {
    templateUrl: 'contact-actions/contact-actions.html',
    controller: 'ContactActionsCtrl'
  });
});
